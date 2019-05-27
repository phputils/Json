<?php
/**
* PHPUtils\Json\Json.php
*
* @author Garett Robson <info@garettrobson.co.uk
*/

declare(strict_types=1);

namespace PHPUtils\Json;

use stdClass;
use TypeError;
use ParseError;
use RuntimeException;

/**
*
* Class to offer convenience functions for common json_decode'ed objects.
*
*/
final class Json
{
    /**
    *
    * Combine objects into one.
    *
    * Takes a variable number *$sources* and combines them into the *$destination*.
    *
    * @param object $destination The objects to merge into a new object.
    * @param object $sources The objects to merge into a new object.
    * @return object The new $destination object, with all merged properties.
    */
    public static function combine(object $destination, object ...$sources)
    {
        foreach ($sources as $source) {
            foreach ($source as $property => $value) {
                $valueType = gettype($value);
                if (
                    $valueType === 'array' &&
                    property_exists($destination, $property) &&
                    is_array($destination->$property)
                ) {
                    $destination->$property = array_merge($destination->$property, $value);
                } else {
                    if ($valueType === 'object') {
                        $dest = $destination->$property ?? new stdClass;
                        $destination->$property = static::combine($dest, $value);
                    } else {
                        $destination->$property = $value;
                    }
                }
            }
        }
        return $destination;
    }

    /**
    * Merge objects into a new one.
    *
    * Takes a variable number *$sources* and produces a new, merged, one.
    *
    * @param object ...$sources The objects to merge into a new object.
    * @return object The new object.
    */
    public static function merge(object ...$sources)
    {
        return static::combine(new stdClass, ...$soruces);
    }

    /**
    *
    * Lookup a value in an object.
    *
    * Uses an *$address* to retrieve the values from a nested *$source* object
    *
    * @param object $source The object to search.
    * @param string $address The address of the value to return.
    * @param mixed $default The value to return when the value is not found.
    * @param string $delimiter The delimiter for the address.
    * @return mixed The value retrieved from the $source object.
    */
    public static function get(object $source, string $address, $default = null, string $delimiter = '.')
    {
        $parts = explode($delimiter, $address);
        $container = $source;
        while ($key = array_shift($parts)) {
            $type = gettype($container);
            switch ($type) {
                case 'array':
                    if (!array_key_exists($key, $container)) {
                        return $default;
                    }
                    $container = $contianer[$key];
                    break;
                case 'object':
                    if (!property_exists($container, $key)) {
                        return $default;
                    }
                    $container = $container->$key;
                    break;
                default:
                    return $default;
            }
        }
        return $container;
    }

    /**
    *
    * Assign a value in an object.
    *
    * Uses an *$address* to retrieve the values from a nested *$source* object
    *
    * @param object $source The object to search.
    * @param string $address The address of the value to assign.
    * @param mixed $value The value to set in the $source object.
    * @throws TypeError If the key maps to a non-collection (i.e. a string, bool, int, etc).
    * @param string $delimiter The delimiter for the address.
    */
    public static function set(object $source, string $address, $value, string $delimiter = '.')
    {
        $parts = explode($delimiter, $address);
        $container = $source;
        while ($key = array_shift($parts)) {
            $type = gettype($container);
            switch ($type) {
                case 'array':
                    if (!array_key_exists($key, $container)) {
                        $contianer[$key] = new stdClass;
                    }
                    $container = &$contianer[$key];
                    break;
                case 'object':
                    if (!property_exists($container, $key)) {
                        $container->$key = new stdClass;
                    }
                    $container = &$container->$key;
                    break;
                default:
                    throw new TypeError(
                        sprintf(
                            "%s : Unable to assign value",
                            __METHOD__,
                            json_last_error_msg()
                        )
                    );
            }
        }
        $container = $value;
    }

    /**
    *
    * Remove a key and associated value from an object.
    *
    * Uses an *$address* to remove a key and associated value from a nested *$source* object
    *
    * @param object $source The object to search.
    * @param string $address The address of the value to unset.
    * @param string $delimiter The delimiter for the address.
    * @return boolean True if the key was round and removed, false if not.
    */
    public static function remove(object $source, string $address, string $delimiter = '.')
    {
        $parts = explode($delimiter, $address);
        $key = array_pop($parts);

        $address = implode($delimiter, $parts);
        $container = static::get($source, $address, null, $delimiter);

        switch (gettype($container)) {
            case 'array':
                if (!array_key_exists($key, $container)) {
                    return false;
                }
                unset($container[$key]);
                break;
            case 'object':
                if (!property_exists($container, $key)) {
                    return false;
                }
                unset($container->$key);
                break;
            default:
                return false;
        }
        return true;
    }

    /**
    *
    * Check that a key exists in an object.
    *
    * Uses an *$address* to verify that the keys exist in the *$source* object.
    *
    * @param object $source The object to search.
    * @param string $address The address of the value to unset.
    * @param string $delimiter The delimiter for the address.
    * @return boolean True if the key was round and removed, false if not.
    */
    public static function exists(object $source, string $address, string $delimiter = '.')
    {
        $parts = explode($delimiter, $address);
        $container = $source;
        while ($key = array_shift($parts)) {
            $type = gettype($container);
            switch ($type) {
                case 'array':
                    if (!array_key_exists($key, $container)) {
                        return false;
                    }
                    $container = $contianer[$key];
                    break;
                case 'object':
                    if (!property_exists($container, $key)) {
                        return false;
                    }
                    $container = $container->$key;
                    break;
                default:
                    return false;
            }
        }
        return true;
    }

    /**
    *
    * Performs a json_decode and returns the result..
    *
    * Simply does a json_decode on the string and returns the result. The main
    * benifit is that a ParseError is thrown if there is an issue doing this.
    *
    * @param string $json Json string to decode.
    * @throws ParseError If the json is not able to be decoded.
    * @return object The decoded json object.
    */
    public static function fromString(string $json)
    {
        $object = json_decode($json);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new ParseError(
                sprintf(
                    "%s : Error parsing string : %s",
                    __METHOD__,
                    json_last_error_msg()
                )
            );
        }
        return $object;
    }

    /**
    *
    * Loads a file from a path and performs a fromString on the content,
    * returns the result.
    *
    * Like fromString this is simple boilerplate, but throws exceptions when
    * problems arise; RuntimeException when the $path is not a file, and
    * ParseError when the contents do not decode.
    *
    * @param string $path Path of the file to decode.
    * @throws RuntimeException If the $path is not a file.
    * @throws ParseError If the json is not able to be decoded.
    * @return boolean True if the key was round and removed, false if not.
    */
    public static function fromFile(string $path)
    {
        if (is_file($path)) {
            return static::fromString(file_get_contents($path));
        } else {
            throw new RuntimeException(
                sprintf(
                    "%s : Path is not a file : %s",
                    __METHOD__,
                    $path
                )
            );
        }
    }

    /**
    *
    * Loads all files which match the filter, merging the decoded files into
    * the $destination object.
    *
    * Like fromString this is simple boilerplate, but throws exceptions when
    * problems arise; RuntimeException when the $path is not a file, and
    * ParseError when the contents do not decode.
    *
    * @param string $path Path of the file to decode.
    * @throws RuntimeException If the $path is not a file.
    * @throws ParseError If the json is not able to be decoded.
    * @return boolean True if the key was round and removed, false if not.
    */
    public static function loadPath(object $destination, string $path, bool $recurse = true, string $filter = "/\.json$/i")
    {
        if (is_dir($path)) {
            $files = array_diff(scandir($path), array('.', '..'));
            foreach ($files as $file) {
                $subPath = $path . DIRECTORY_SEPARATOR . $file;
                var_dump($subPath);
                if ($recurse && is_dir($subPath)) {
                    static::loadPath($destination, $subPath, $recurse, $filter);
                } elseif (is_file($subPath) && preg_match($filter, $subPath)) {
                    $object = static::fromFile($subPath);
                    static::combine($destination, $object);
                }
            }
            return $destination;
        } else {
            throw new RuntimeException(
                sprintf(
                    "%s : Path is not a directory : %s",
                    __METHOD__,
                    $path
                )
            );
        }
    }
}
