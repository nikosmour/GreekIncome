<?php

namespace GreekIncome\Services;

use GreekIncome\Classes\IncomeData;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\StreamInterface;

class VerifyIncomeService extends VerifyIncomeBaseService
{
    protected const TO_CACHE = true;
    protected const CACHE_TIME = 60000;
    private array $cookies;


    /**
     * @throws ConnectionException
     */
    public function __construct()
    {
        if (self::TO_CACHE)
            $this->getCachedCookies();
        else
            $this->refreshCookies();
    }

    /**
     * return the for every input if the verification was correct or not
     * @throws ConnectionException
     */
    public function __invoke(array $data): IncomeData
    {
        return new IncomeData($this->findAnswers($data),$data);
    }

    /**
     * get the body of the response from the gov
     * @param array $postData
     * @param bool $firstTime
     * @return StreamInterface|string
     * @throws ConnectionException
     */
    private function getHtml(array $postData,bool $firstTime=true): StreamInterface|string
    {
        // Extract cookies from the first response
        $cookies = $this->cookies;

        // Simulate the button click with a POST request
        $response = $this->validateIncomeToGov($cookies,$postData);
        $status = $response->getStatusCode();
        if ($status == 200) {
            return $response->body();
        }
        if ($status == 400 && $firstTime && $this->whyFail($response->getBody())==='session') {
            $this->refreshCookies();
            return $this->getHtml($postData,false);
        }
        return 'failed';


    }

    /**
     * Return all the result of the validations that happened on the aade
     * @return array<bool>
     * @throws ConnectionException
     */
    private function findAnswers(array $inputData):array
    {
        $postData=$this->getPostData($inputData);
        $html = $this->getHtml($postData);
        if ($html=== 'failed')
            return [];
        preg_match_all("/document\.images\['(.*?)']\.src\s*=\s*(.*?)_flat.src;/", $html, $matches, PREG_SET_ORDER);
        $checking=[];
        foreach ($matches as $match) {
            $checking[$match[1]]=$match[2]==='ok';//$checking[$imageName]=$srcValue === 'ok';
            // The image name, e.g., 'aytforf'
            // The src value, e.g., ok / notok
        }
        return $checking;
    }

    /**
     * The reason that may fail the request to the gov.
     * @param string $html
     * @return string|null
     * `session` if fail because of the session
     * `wrongYear` if the link is not supported;
     */
    private function whyFail(string $html):?string
    {
//        typeOf($html);
        $check=preg_match_all("/logout/", $html, $matches, PREG_SET_ORDER);
        if ($check) return 'session';
        $check=preg_match_all("/-income-e1-check/", $html, $matches, PREG_SET_ORDER);
        if ($check) return 'wrongYear';
        return null;
    }

    /**
     * Receiving new session cookies for the gov request
     * @throws ConnectionException
     */
    private function refreshCookies():void
    {
        $this->cookies = $this->getNewCookies();
        if ($this::TO_CACHE) Cache::put('govSessionCookies', $this->cookies,$this::CACHE_TIME);
    }
    /**
     * @param array $cookies
     * @return \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
     * @throws ConnectionException
     */
    private function validateIncomeToGov(array $cookies,array $postData): \GuzzleHttp\Promise\PromiseInterface|\Illuminate\Http\Client\Response
    {
//        $year=now()->subMonths(5)->format('Y');
        $year=$postData['FISCAL_YEAR'];
        $url= "https://www1.aade.gr/webtax2/incomefp2/year$year-income-e1-check.do";

        return Http::withOptions(['verify' => true])
            ->withCookies($cookies, 'www1.aade.gr') // Include cookies
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:128.0) Gecko/20100101 Firefox/128.0',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ])
            ->asForm() // Specify form data format
            ->post($url, $postData);
    }

    private function getCachedCookies():void
    {
        $this->cookies = Cache::remember('govSessionCookies', $this::CACHE_TIME, function() {
            return $this->getNewCookies();  // Refresh cookies if not available
        });
    }

    /**
     * @return array array of cookies
     * @throws ConnectionException
     */
    private function getNewCookies(): array
    {
        $url = "https://www1.aade.gr/webtax2/incomefp2/year2024/income/e1check/index.jsp";

        // Make the initial GET request
        $response = Http::withOptions(['verify' => true])
        ->withHeaders([
            'User-Agent' => 'CustomUserAgent/1.0',
        ])
            ->get($url);
        $cookies = $response->cookies()->toArray();
        $cookies = array_column($cookies, 'Value', 'Name');
        $cookies['f5_cspm'] = '1234';
        return $cookies;
    }
}
