<?php

namespace Overtrue\Pinyin;

use ArrayAccess;
use JsonSerializable;
use Stringable;

use function array_map;
use function implode;
use function is_array;

class Collection implements ArrayAccess, JsonSerializable, Stringable
{
    public function __construct(protected $items = []) {}

    public function join(string $separator = ' '): string
    {
        return implode($separator, array_map(
            fn ($item) => is_array($item) ? '['.implode(', ', $item).']' : $item,
            $this->items
        ));
    }

    public function map(callable $callback): Collection
    {
        return new static(array_map($callback, $this->all()));
    }

    public function all(): array
    {
        return $this->items;
    }

    public function toArray(): array
    {
        return $this->all();
    }

    public function toJson(int $options = 0): string
    {
        return json_encode($this->all(), $options);
    }

    public function __toString(): string
    {
        return $this->join();
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->items[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->items[$offset]);
    }

    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
