<?php

/**
 * @example
 * <pre>
 * // U+4E2D: zhōng,zhòng  # 中
 * </pre>
 *
 * @throws Exception
 */
function parse_chars(string $path, ?callable $fn = null): Generator
{
    $fn ??= fn ($p) => $p;

    foreach (file($path) as $line) {
        preg_match('/^U\+(?<code>[0-9A-Z]+):\s+(?<pinyin>\S+)\s+#\s*(?<char>\S+)/', $line, $matched);

        if ($matched && ! empty($matched['pinyin'])) {
            yield $matched['char'] => $fn(explode(',', $matched['pinyin']));
        } elseif (! str_starts_with($line, '#')) {
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
function parse_words(string $path, ?callable $fn = null): Generator
{
    $fn ??= fn ($p) => $p;

    foreach (file($path) as $line) {
        preg_match('/^(?<word>[^#\s]+):\s+(?<pinyin>[\p{L} ]+)#?/u', $line, $matched);

        if ($matched && ! empty($matched['pinyin'])) {
            yield $matched['word'] => $fn(explode(' ', trim($matched['pinyin'])));
        }
    }
}

function parse_options(array $argv): array
{
    $inputOptions = [];
    $currentOption = null;

    for ($i = 1; $i < count($argv); $i++) {
        $arg = $argv[$i];

        if (str_starts_with($arg, '--')) {
            // 长选项
            $option = substr($arg, 2);
            if (str_contains($option, '=')) {
                [$option, $value] = explode('=', $option, 2);
                $inputOptions[$option] = $value;
            } else {
                $inputOptions[$option] = true;
                $currentOption = $option;
            }
        } elseif (str_starts_with($arg, '-')) {
            // 短选项
            $option = substr($arg, 1);
            if (strlen($option) > 1 && $option[1] !== '=') {
                // 多个短选项，如 -abc
                for ($j = 0; $j < strlen($option); $j++) {
                    $inputOptions[$option[$j]] = true;
                }
            } else {
                // 单个短选项，如 -a 或 -a=value
                if (str_contains($option, '=')) {
                    [$option, $value] = explode('=', $option, 2);
                    $inputOptions[$option] = $value;
                } else {
                    $inputOptions[$option] = true;
                    $currentOption = $option;
                }
            }
        } else {
            // 参数值
            if ($currentOption !== null) {
                $inputOptions[$currentOption] = $arg;
                $currentOption = null;
            } else {
                $inputOptions[] = $arg;
            }
        }
    }

    return $inputOptions;
}
