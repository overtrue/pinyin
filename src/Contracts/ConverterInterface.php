<?php

namespace Overtrue\Pinyin\Contracts;

use Overtrue\Pinyin\Collection;

interface ConverterInterface
{
    public function convert(string $string): Collection;
    
    public function heteronym(bool $asList = false): static;
    
    public function surname(): static;
    
    public function noWords(): static;
    
    public function noCleanup(): static;
    
    public function onlyHans(): static;
    
    public function withToneStyle(string $toneStyle): static;
    
    public function getMemoryUsage(): array;
}