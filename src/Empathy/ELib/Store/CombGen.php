<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

class CombGen
{
    private mixed $sets;
    private mixed $results = [];

    public function __construct(mixed $sets)
    {
        $this->sets = $sets;
        $this->generateCombinations('', 0, $this->sets);
    }

    public function generateCombinations(mixed $string, mixed $start, mixed $sets): void {
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

    public function getResults(): mixed {
        return $this->results;
    }

}
