<?php

namespace YiiFactoryGirl;

/**
 * Generate sequential string
 *
 * Example
 *   FactorySequence::get('foo_{{sequence}}'); // -> 'foo_0'
 *   FactorySequence::get('bar_{{sequence}}'); // -> 'bar_1'
 */
class Sequence
{
    CONST DEFAULT_SYMBOL = 'default';
    protected static $_sequence = array();
    protected static $_regexp = "/({{sequence(\(:(.*)\)){0,}?}})/";

    /**
     * Gets Sequence
     * @param string $str
     * @return string
     * @uses FactorySequence::get('bar_{{sequence}}'); // -> 'bar_0'
     *       Next call FactorySequence::get('bar_{{sequence}}'); // -> 'bar_1'
     * @uses with Symbol
     *       FactorySequence::get('foo_{{sequence(:bar)}}'); // -> 'foo_0'
     *       FactorySequence::get('foo_{{sequence(:baz)}}'); // -> 'foo_0'
     */
    public static function get($str)
    {
        if (!preg_match(self::$_regexp, $str, $matches)) {
            return $str;
        }

        if (count($matches) == 2) {
            return str_replace($matches[1], self::next(), $str);
        } else if (count($matches) == 4) {
            $sequenceParams = explode(',', $matches[3]);
            if (count($sequenceParams) == 2) {
                return str_replace($matches[1], self::next(trim($sequenceParams[0]), trim($sequenceParams[1])), $str);
            } else {
                return str_replace($matches[1], self::next(trim($sequenceParams[0])), $str);
            }
        }
    }

    /**
     * Reset Sequence Number
     * @param string $symbol reset target
     * @param integer $start reset number
     */
    protected static function reset($symbol = null, $start = 0)
    {
        self::$_sequence[self::getSymbol($symbol)] = $start;
    }

    /**
     * All Reset Sequence Number
     */
    public static function resetAll()
    {
        self::$_sequence = array();
    }

    /**
     * Get next Sequence Number
     * @param null $symbol
     * @param int $start
     * @return int
     */
    protected static function next($symbol = null, $start = 0)
    {
        $symbol = self::getSymbol($symbol);
        if (isset(self::$_sequence[$symbol])) {
            self::$_sequence[$symbol] = self::$_sequence[$symbol] + 1;
            return self::$_sequence[$symbol];
        } else {
            return self::$_sequence[$symbol] = $start;
        }
    }

    /**
     * Get Symbol
     * if $symbol is null, return DEFAULT_SYMBOL
     */
    protected static function getSymbol($symbol)
    {
        return ($symbol !== null) ? $symbol : self::DEFAULT_SYMBOL;
    }
}
