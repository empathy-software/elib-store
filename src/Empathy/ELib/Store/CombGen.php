<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

class CombGen
{
    private mixed $results = [];

    public function __construct(private readonly mixed $sets)
    {
        $this->generateCombinations('', 0, $this->sets);
    }

    public function generateCombinations(mixed $string, mixed $start, mixed $sets): void
    {
        $current = $sets[$start];
        $counter = count($current);
        for ($i = 0; $i < $counter; $i++) {
            if ($start + 1 < count($sets)) {
                if ($start === 0) {
                    $this->generateCombinations($current[$i], $start + 1, $sets);
                } else {
                    $this->generateCombinations($string.'-'.$current[$i], $start + 1, $sets);
                }
            } else {
                $this->results[] = $string.'-'.$current[$i];
            }
        }
    }

    public function getResults(): mixed
    {
        return $this->results;
    }

}
