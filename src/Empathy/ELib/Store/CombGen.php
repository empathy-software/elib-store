<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

class CombGen
{
    private $sets;
    private $results = [];

    public function __construct($sets)
    {
        $this->sets = $sets;
        $this->generateCombinations('', 0, $this->sets);
    }

    public function generateCombinations($string, $start, $sets)
    {
        $current = $sets[$start];
        for ($i = 0; $i < sizeof($current); $i++) {
            if ($start + 1 < sizeof($sets)) {
                if ($start === 0) {
                    $this->generateCombinations($current[$i], $start + 1, $sets);
                } else {
                    $this->generateCombinations($string.'-'.$current[$i], $start + 1, $sets);
                }
            } else {
                array_push($this->results, $string.'-'.$current[$i]);
            }
        }
    }

    public function getResults()
    {
        return $this->results;
    }

}
