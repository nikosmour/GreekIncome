<?php

namespace GreekIncome\Services;

use InvalidArgumentException;
use LengthException;
use UnexpectedValueException;

class VerifyIncomeDataService
{
    /** @var string  url to receive  session cookies */
    public const COOKIE_URL="https://www1.aade.gr/webtax2/incomefp2/year2024/income/e1check/index.jsp";
    /**
     * Default request data expected by the government.
     * Keys ending with `_F` refer to "ypoxreos", `_S` to "sizigos".
     *
     * @var array
     */
    private const  INIT_REQUEST_DATA = [
        'PBCheck' => 'ΕΛΕΓΧΟΣ',
        'AFM_F' => '',
        'AR_KATAXWRHSHS' => '',
        'FISCAL_YEAR' => '', // 2003+
        // Income categories for ypoxreos and sizigos
        'EISODHMA_A_F' => '', 'EISODHMA_A_S' => '',
        'EISODHMA_C_F' => '', 'EISODHMA_C_S' => '',
        'EISODHMA_E_F' => '', 'EISODHMA_E_S' => '',
        'EISODHMA_D_F' => '', 'EISODHMA_D_S' => '',
        'EISODHMA_ST_F' => '', 'EISODHMA_ST_S' => '',
        'EISODHMA_Z_F' => '', 'EISODHMA_Z_S' => '',
        'EISODHMA_AL_F' => '', 'EISODHMA_AL_S' => '',
        'EISODHMA_F' => '', 'EISODHMA_S' => '',
        'EPIDOMA_OAED_F' => '', 'EPIDOMA_OAED_S' => '',
        'DHLWTHEN_F' => '', 'DHLWTHEN_S' => '',
        'AYT_FOR_EISOD_F' => '', 'AYT_FOR_EISOD_S' => '',
        'TZIROS_F' => '', 'TZIROS_S' => '',
        'BOYL_APOZ_F' => '', 'BOYL_APOZ_S' => '',
        'ENHMER_F' => '',
        'ONOMA_F' => '+',
        'ONOMA_S' => '+',
    ];

    /**
     * Allowed keys for validation.
     */
    private const ALLOWED_KEYS = [
        'AYT_FOR_EISOD', 'DHLWTHEN', 'EISODHMA', 'EISODHMA_A',
        'EISODHMA_C', 'EISODHMA_E', 'EISODHMA_D', 'EISODHMA_ST',
        'EISODHMA_Z', 'EISODHMA_AL', 'EPIDOMA_OAED', 'TZIROS', 'BOYL_APOZ',
    ];

    /**
     * Validates a key.
     *
     * @param string $key
     * @throws InvalidArgumentException
     */
    private function verifyKey(string $key): void
    {
        if (!in_array($key, self::ALLOWED_KEYS, true)) {
            throw new InvalidArgumentException("Invalid key: $key");
        }
    }

    /**
     * Validates a value.
     *
     * @param string|int $value
     * @param string $key
     * @param int $min
     * @return string
     * @throws UnexpectedValueException
     */
    private function verifyValue(string|int $value, string $key, int $min = 0): string
    {
        if ((int)$value > $min) {
            return (string)$value; // Ensure value is a string as required by the government.
        }
        throw new UnexpectedValueException("The value for $key must be higher than $min");
    }

    /**
     * Validates and transforms the input data to match the government format.
     * @property $data  the inputData
     * @return array
     * @throws InvalidArgumentException|LengthException|UnexpectedValueException
     */
    private function transformInputData(array $data): array
    {
        $newValues = [];

        // Validate and transform main fields
        $newValues['AFM_F'] = $this->validateAfm($data['AFM']);
        $newValues['AR_KATAXWRHSHS'] = $this->verifyValue($data['AR_DILOSIS'], 'AR_DILOSIS');
        $newValues['FISCAL_YEAR'] = $this->verifyValue($data['YEAR'], 'YEAR', 2003);

        unset($data['AFM'], $data['AR_DILOSIS'], $data['YEAR']);

        // Handle "ypoxreos" and "sizigos"
        $this->processNestedData($data, $newValues, 'ypoxreos', '_F');
        $this->processNestedData($data, $newValues, 'sizigos', '_S');

        // Handle non-nested keys
        foreach ($data as $key => $value) {
            $this->verifyKey($key);
            if (!is_array($value)) {
                $value = [$value];
            }
            if (isset($value[0])) $newValues["{$key}_F"] = $this->verifyValue($value[0], "{$key}_F");
            if (isset($value[1])) $newValues["{$key}_S"] = $this->verifyValue($value[1], "{$key}_S");
        }

        if (count($newValues) > 3) {
            return $newValues;
        }

        throw new InvalidArgumentException("Invalid input data: insufficient values for validation.");
    }

    /**
     * Validates and transforms AFM.
     *
     * @param mixed $afm
     * @return string
     * @throws LengthException|UnexpectedValueException
     */
    private function validateAfm(mixed $afm): string
    {
        $afm = $this->verifyValue($afm, 'AFM');
        if (strlen($afm) !== 9) {
            throw new LengthException("The value for AFM must be 9 characters long.");
        }
        return $afm;
    }

    /**
     * Processes nested data for ypoxreos or sizigos.
     *
     * @param array $data
     * @param array &$newValues
     * @param string $key
     * @param string $suffix
     */
    private function processNestedData(array &$data, array &$newValues, string $key, string $suffix): void
    {
        if (isset($data[$key])) {
            foreach ($data[$key] as $subKey => $value) {
                $this->verifyKey($subKey);
                $newValues["{$subKey}{$suffix}"] = $this->verifyValue($value, "{$key}.{$subKey}");
            }
            unset($data[$key]);
        }
    }

    /**
     * get the data to send as a post on the incomeBaseService
     * @param array $values
     * @return array
     */
    public function getPostData(array $values): array
    {
        return array_merge($this::INIT_REQUEST_DATA, $this->transformInputData($values));
    }

    /**
     * receive the url to verify the data
     * @param int|null $year
     * @return string
     */
    public static function getVerifyUrl(?int $year): string
    {
        $year??=now()->subMonths(5)->format('Y');
        return "https://www1.aade.gr/webtax2/incomefp2/year$year-income-e1-check.do";
    }
}
