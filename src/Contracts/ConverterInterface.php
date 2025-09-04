<?php

namespace Overtrue\Pinyin\Contracts;

use Overtrue\Pinyin\Collection;
use Overtrue\Pinyin\ToneStyle;

interface ConverterInterface
{
    public function convert(string $string): Collection;

    public function heteronym(bool $asList = false): static;

    public function surname(): static;

    public function noWords(): static;

    public function noCleanup(): static;

    public function onlyHans(): static;

    public function noAlpha(): static;

    public function noNumber(): static;

    public function noPunctuation(): static;

    public function withToneStyle(string|ToneStyle $toneStyle): static;

    public function noTone(): static;

    public function useNumberTone(): static;

    public function yuToV(): static;

    public function yuToU(): static;

    public function yuToYu(): static;

    public function when(bool $condition, callable $callback): static;
}
