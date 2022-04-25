<?php

/**
 * @example
 * <pre>
 * // U+4E2D: zhōng,zhòng  # 中
 * </pre>
 * @throws Exception
 */
function parse_chars(string $path, callable $fn = null): Generator
{
    $fn ??= fn ($p) => $p;

    foreach (file($path) as $line) {
        preg_match('/^U\+(?<code>[0-9A-Z]+):\s+(?<pinyin>\S+)\s+#\s*(?<char>\S+)/', $line, $matched);

        if ($matched && !empty($matched['pinyin'])) {
            yield $matched['char'] => $fn(explode(',', $matched['pinyin']));
        } elseif (!str_starts_with($line, '#')) {
            throw new Exception("行解析错误：$line");
        }
    }
}

/**
 * @example
 * <pre>
 * // 㞎㞎: bǎ ba # 注释
 * </pre>
 *
 * @throws Exception
 */
function parse_words(string $path, callable $fn = null): Generator
{
    $fn ??= fn ($p) => $p;

    foreach (file($path) as $line) {
        preg_match('/^(?<word>[^#\s]+):\s+(?<pinyin>[\p{L} ]+)#?/u', $line, $matched);

        if ($matched && !empty($matched['pinyin'])) {
            yield $matched['word'] => $fn(explode(' ', trim($matched['pinyin'])));
        }
    }
}
