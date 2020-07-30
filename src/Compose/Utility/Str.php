<?php declare(strict_types=1);

namespace Compose\Utility;

class Str
{
    /**
     * Does the given string end in the given character?
     *
     * @param $str
     * @param $char
     *
     * @return bool
     */
    public static function endsIn($str, $char)
    {
        return substr($str, -1) === $char;
    }

    /**
     * Remove characters from the end of a string
     *
     * @param $str
     * @param int $numChars
     *
     * @return bool|string
     */
    public static function removeEnd($str, $numChars = 1)
    {
        return substr($str, 0, -1 * abs($numChars));
    }

    /**
     * Generate a unique, random string
     *
     * @param int $length
     *
     * @return string
     * @throws \Exception
     */
    public static function random(int $length = 16)
    {
        if ($length < 1) {
            throw new \RangeException('Length must be a positive integer');
        }

        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $str = '';
        $max = mb_strlen($characters, '8bit') - 1;

        for ($i = 0; $i < $length; $i++) {
            $str .= $characters[random_int(0, $max)];
        }

        return $str;
    }
}