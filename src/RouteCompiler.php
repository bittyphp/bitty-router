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
            self::DELIMINATOR.'\{(\w+)(<.*?>)?(\?[^\}]*?)?\}'.self::DELIMINATOR,
            $path,
            $matches,
            PREG_SET_ORDER|PREG_OFFSET_CAPTURE
        );

        $compiled = '';
        foreach ($matches as $match) {
            $string = $match[0][0];
            /** @var int $offset */
            $offset = $match[0][1];
            $name = $match[1][0];
            $previousText = substr($path, $pos, $offset - $pos);
            $pos = $offset + strlen($string);

            $compiled .= self::processMatch($match, $constraints, $params, $name, $previousText);
        }

        $remainingText = substr($path, $pos);
        $compiled .= preg_quote($remainingText, self::DELIMINATOR);

        return [
            'regex' => self::DELIMINATOR.'^'.$compiled.'$'.self::DELIMINATOR,
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
     * @return string
     */
    private static function processMatch(
        array $match,
        array &$constraints,
        array &$params,
        string $name,
        string $previousText
    ): string {
        if (!empty($match[2][0])) {
            $constraints[$name] = substr($match[2][0], 1, -1);
        }

        $regex = '(?<'.$name.'>'.($constraints[$name] ?? '.+?').')';

        if (isset($match[3])) {
            $default = substr($match[3][0], 1);
            $params[$name] = $default ?: null;

            $previousChar = substr($previousText, -1);
            if (preg_match('`['.self::SEPARATORS.']`', $previousChar)) {
                $previousText = substr($previousText, 0, -1);
                $regex = '(?:'.preg_quote($previousChar, self::DELIMINATOR).$regex.')?';
            } else {
                $regex .= '?';
            }
        }

        return preg_quote($previousText, self::DELIMINATOR).$regex;
    }
}
