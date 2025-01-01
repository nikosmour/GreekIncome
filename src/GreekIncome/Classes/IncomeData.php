<?php

namespace GreekIncome\Classes;

class IncomeData
{
    public $input=[];
    public $output=[
        'ypoxreos'=>[],
        'sizigos'=>[],
    ];
    public $success;

    /**
     * @var int
     */
    public int $successCount;

    public function __construct(array $data ,array $input)
    {
        $this->input = $input;

        /**calculate the success validations*/
        $successCount=0;


        // Loop through the incoming data and populate object properties dynamically
        foreach ($data as $key => $value) {
            $person = str_ends_with($key, 'f') ? 'ypoxreos' : 'sizigos';
            $newKey = match (substr($key, 0, -1)) {
                'aytfor' => 'AYT_FOR_EISOD', // ΑΥΤΟΤ. ΦΟΡΟΛ. ΠΟΣΑ... ΚΤΛ.
                'dhl' => 'DHLWTHEN', // ΔΗΛΩΘΕΝ ΕΙΣΟΔΗΜΑ
                'eis' => 'EISODHMA', // ΣΥΝΟΛΟ ΕΙΣΟΔΗΜΑΤΟΣ
                'oaed' => 'OAED',
                'akath' => 'TZIROS',
                'boyl' => 'BOYL_APOZ',
                'enhmer' => 'ENHMER_F',
                default => 'EISODHMA_',
            };

            if ($newKey === 'EISODHMA_') {
                preg_match_all("/eis(.+)[fs]/", $key, $matches, PREG_SET_ORDER);
                $newKey .= strtoupper($matches[0][1]);
            }

            // Assign the value to the appropriate property
            $this->output[$person][$newKey] = $value;
            if($value) $successCount++;
        }
        $success=false;
        if ($successCount === count($data)) $success= true;
        elseif ($successCount){ $success = null ; }
        $this->successCount = $successCount;
        $this->success = $success;
    }

    // Convert the object to an array
    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'successCount' => $this->successCount,
            'input'=> $this->input,
            'output'=>$this->output,
        ];
    }

    // Convert the object to JSON
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
    public function __toString(): string
    {
        return $this->toJson();
    }
}

