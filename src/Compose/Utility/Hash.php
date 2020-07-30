<?php declare(strict_types=1);

namespace Compose\Utility;

class Hash
{
    /**
     * Can the given value be accessed as an array?
     *
     * @param mixed $value
     *
     * @return bool
     */
    public static function accessible($value)
    {
        return is_array($value) || $value instanceof \ArrayAccess;
    }

    /**
     * Does the given key exist in the array?
     *
     * @param array|\ArrayAccess $array
     * @param $key
     *
     * @return bool
     */
    public static function exists($array, $key)
    {
        return ($array instanceof \ArrayAccess)
            ? $array->offsetExists($key)
            : array_key_exists($key, $array);
    }

    /**
     * Does the array contain given key using dot notation?
     *
     * @param array $array
     * @param string $key
     *
     * @return bool
     */
    public static function has(array $array, string $key)
    {
        if (empty($array) || empty((array) $key)) {
            return false;
        }

        $subArray = $array;

        foreach (explode('.', $key) as $segment) {
            if (! static::accessible($subArray) || ! static::exists($subArray, $segment)) {
                return false;
            }

            $subArray = $subArray[$segment];
        }

        return true;
    }

    /**
     * Get value of given key
     *
     * @param array|\ArrayAccess $array
     * @param string|int|null $key
     * @param mixed $default
     *
     * @return mixed
     */
    public static function get($array, $key, $default = null)
    {
        if (! static::accessible($array) || is_null($key)) {
            return $default;
        }

        if (static::exists($array, $key)) {
            return $array[$key];
        }

        if (strpos((string) $key, '.') === false) {
            return $default;
        }

        $value = $array;

        foreach (explode('.', $key) as $segment) {
            if (! static::accessible($value) || ! static::exists($value, $segment)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Set an array accessible value using a dot-notated key
     *
     * @param array $array
     * @param string $key
     * @param mixed $value
     *
     * @return void|array
     */
    public static function set(&$array, $key, $value)
    {
        if (! static::accessible($array)) {
            return;
        }

        if (is_null($key)) {
            return $array = $value;
        }

        $keys = explode('.', $key);
        $last = array_pop($keys);

        foreach ($keys as $key) {
            if (! array_key_exists($key, $array) || ! is_array($array[$key])) {
                $array[$key] = [];
            }

            $array = &$array[$key];
        }

        $array[$last] = $value;

        return $array;
    }

    /**
     * Remove a key from given hash
     *
     * @param array|\ArrayAccess $array
     * @param string $key
     *
     * @return array|\ArrayAccess
     */
    public static function remove(&$array, $key)
    {
        if (! static::accessible($array) || is_null($key) || ! static::has($array, $key)) {
            return $array;
        }

        $keys = explode('.', $key);
        $last = array_pop($keys);

        foreach ($keys as $key) {
            $array = &$array[$key];
        }

        unset($array[$last]);

        return $array;
    }

    /**
     * Get first value in array
     *
     * @param array|\ArrayAccess $array
     * @param mixed $default
     *
     * @return mixed
     */
    public static function first($array, $default = null)
    {
        return Hash::get($array, array_key_first($array), $default);
    }

    /**
     * Get last value of array
     *
     * @param array|\ArrayAccess $array
     * @param mixed $default
     *
     * @return mixed
     */
    public static function last($array, $default = null)
    {
        return Hash::get($array, array_key_last($array), $default);
    }

    /**
     * Is the given array flat?
     *
     * @param array $items
     *
     * @return bool
     */
    public static function flat(array $items)
    {
        return empty(array_filter($items, fn($item) => is_array($item)));
    }

    /**
     * Flatten a multidimensional array
     *
     * @param array $items
     *
     * @return array
     */
    public static function flatten(array $items)
    {
        if (static::flat($items)) {
            return $items;
        }

        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($items));

        return iterator_to_array($iterator, false);
    }
}