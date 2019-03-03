<?php

namespace Bitty\Router;

class RouteCompiler
{
    /**
     * Regex deliminator.
     *
     * @var string
     */
    public const DELIMINATOR = '`';

    /**
     * Separators that are seen as optional.
     *
     * @var string
     */
    public const SEPARATORS = '/.';

    /**
     * Compiles a regex for the given path.
     *
     * @param string $path
     * @param string[] $constraints
     * @param mixed[] $params
     *
     * @return array
     */
    public static function compile(string $path, array $constraints = [], array $params = []): array
    {
        $pos = 0;
        $matches = [];
        preg_match_all(
            '`\{(\w+)(<.*?>)?(\?[^\}]*?)?\}`',
            $path,
            $matches,
            PREG_SET_ORDER|PREG_OFFSET_CAPTURE
        );

        $tokens = [];
        $regex = '';
        foreach ($matches as $match) {
            $string = $match[0][0];
            /** @var int $offset */
            $offset = $match[0][1];
            $name = $match[1][0];
            $previousText = substr($path, $pos, $offset - $pos);
            $pos = $offset + strlen($string);

            $token = self::processMatch($match, $constraints, $params, $name, $previousText);
            $regex .= preg_quote($token['previousText'], self::DELIMINATOR)
                .$token['regex'].($token['optional'] ? '?' : '');

            if (!empty($token['previousText'])) {
                $tokens[] = [
                    'type' => 'text',
                    'prefix' => $token['previousText'],
                ];
            }
            unset($token['previousText']);
            $tokens[] = $token;
        }

        $remainingText = substr($path, $pos);
        if (!empty($remainingText)) {
            $tokens[] = [
                'type' => 'text',
                'prefix' => $remainingText,
            ];
            $regex .= preg_quote($remainingText, self::DELIMINATOR);
        }

        return [
            'regex' => self::DELIMINATOR.'^'.$regex.'$'.self::DELIMINATOR,
            'tokens' => $tokens,
            'constraints' => $constraints,
            'params' => $params,
        ];
    }

    /**
     * Processes a path match.
     *
     * @param array $match
     * @param array $constraints
     * @param array $params
     * @param string $name
     * @param string $previousText
     *
     * @return array
     */
    private static function processMatch(
        array $match,
        array &$constraints,
        array &$params,
        string $name,
        string $previousText
    ): array {
        if (!empty($match[2][0])) {
            $constraints[$name] = substr($match[2][0], 1, -1);
        }

        $regex = '(?<'.$name.'>'.($constraints[$name] ?? '.+?').')';
        $isOptional = false;
        $prefix = '';

        if (isset($match[3])) {
            $isOptional = true;
            $default = substr($match[3][0], 1);
            $params[$name] = $default ?: null;

            $prefix = substr($previousText, -1);
            if (preg_match('`['.self::SEPARATORS.']`', $prefix)) {
                $previousText = substr($previousText, 0, -1);
                $regex = '(?:'.preg_quote($prefix, self::DELIMINATOR).$regex.')';
            } else {
                $prefix = '';
            }
        }

        return [
            'type' => 'param',
            'name' => $name,
            'optional' => $isOptional,
            'regex' => $regex,
            'prefix' => $prefix,
            'previousText' => $previousText,
        ];
    }
}
