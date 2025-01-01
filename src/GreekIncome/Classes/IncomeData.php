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

    // Add additional properties here if needed, depending on the incoming data.

    public function __construct(array $data ,array $input)
    {
        $this->input = $input;
        // Assign the success value
        $allTrue=true;
        $oneTrue=false;


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
            $allTrue=$allTrue && $value;
            $oneTrue=$oneTrue || $value;
        }
        $success=false;
        if ($allTrue && $oneTrue) $success= true;
        elseif ($oneTrue){ $success = null ; }
        $this->success = $success;
    }

    // Convert the object to an array
    public function toArray(): array
    {
        return [
            'success' => $this->success,
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

