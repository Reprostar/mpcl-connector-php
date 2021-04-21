<?php

namespace Reprostar\MpclConnector\Model;

abstract class BaseModel
{
    /**
     * Convert model to an associative array
     * @param int $maxDepth
     * @return array
     */
    public function toArray($maxDepth = 10)
    {
        if (!$maxDepth) {
            return null;
        }

        $ret = (array)$this;

        foreach ($ret as $key => $value) {
            if (is_object($value) && is_subclass_of($value, self::class)) {
                $ret[$key] = $value->toArray($maxDepth - 1);
            }
        }

        return $ret;
    }

    /**
     * Convert data from an associative array to model object
     * @param array|mixed $data
     * @return static|mixed Returns hydrated object or untouched data, in case if input is not an array
     */
    public static function hydrate($data)
    {
        if (!is_array($data)) {
            return $data;
        }

        $class = static::class;
        $model = new $class();

        foreach ($data as $key => $value) {
            if (property_exists($model, $key)) {
                $model->$key = $value;
            }
        }

        return $model;
    }
}