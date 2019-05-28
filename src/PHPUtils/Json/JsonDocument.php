<?php

namespace PHPUtils\Json;

use ArrayAccess;
use JsonSerializable;

class JsonDocument implements ArrayAccess, JsonSerializable
{
    public static function fromFile(string $path)
    {
        return new static(Json::fromFile($path));
    }

    public static function fromString(string $json)
    {
        return new static(Json::fromString($json));
    }

    public static function fromObject(object $object)
    {
        return new static($object);
    }

    public static function fromArray(array $array)
    {
        return new static($array);
    }

    protected $jsonData = null;
    protected $jsonEncodeOptions = 0;

    private function __construct($jsonData = null, int $jsonEncodeOptions = 0)
    {
        $this->jsonData = $jsonData;
        $this->jsonEncodeOptions = $jsonEncodeOptions;
    }

    public function getOptions()
    {
        return $this->jsonEncodeOptions;
    }

    public function combine(object ...$sources)
    {
        Json::combine($this->jsonData, ...$sources);
        return $this;
    }

    public function setOptions(int $options)
    {
        $this->jsonEncodeOptions = $options;
        return $this;
    }

    public function setOption(int $option)
    {
        $this->setOptions($this->getOptions() | $option);
        return $this;
    }

    public function isOptionSet(int $options)
    {
        return ($this->getOptions() & $options) === $options;
    }

    public function unsetOptions(int $options)
    {
        $this->setOptions($this->getOptions() & ~$options);
        return $this;
    }

    public function __toString()
    {
        return json_encode($this->jsonData, $this->jsonEncodeOptions);
    }

    public function offsetExists($offset)
    {
        return Json::exists($this->jsonData, $offset);
    }

    public function offsetGet($offset)
    {
        return Json::get($this->jsonData, $offset);
    }

    public function offsetSet($offset, $value)
    {
        Json::set($this->jsonData, $offset, $value);
    }

    public function offsetUnset($offset)
    {
        Json::remove($this->jsonData, $offset);
    }

    public function jsonSerialize()
    {
        return $this->jsonData;
    }

    public function __isset(string $name)
    {
        return Json::exists($this->jsonData, $name);
    }

    public function __unset(string $name)
    {
        return Json::remove($this->jsonData, $name);
    }

    public function __get(string $name)
    {
        return Json::get($this->jsonData, $name);
    }

    public function __set(string $name, string $value)
    {
        Json::set($this->jsonData, $name, $value);
    }

    public function get(string $address, $default = null, string $delimiter = '.')
    {
        return Json::get($this->jsonData, $address, $default, $delimiter);
    }

    public function set(string $address, $value, string $delimiter = '.')
    {
        Json::set($this->jsonData, $address, $value, $delimiter);
        return $this;
    }

    public function remove(string $address, string $delimiter = '.')
    {
        Json::remove($this->jsonData, $address, $delimiter);
    }

    public function exists(string $address, string $delimiter = '.')
    {
        return Json::exists($this->jsonData, $address, $delimiter);
    }

}
