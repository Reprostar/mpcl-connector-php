<?php

/**
 * Created by PhpStorm.
 * User: pfcode
 * Date: 17.09.16
 * Time: 00:03
 */

namespace Reprostar\MpclConnector;

abstract class RemoteModel
{
    /**
     * Convert model to associative array
     * @param int $maxDepth
     * @return array
     */
    public function toAssoc($maxDepth = 10){
        if(!$maxDepth){
            return null;
        }

        $ret = (array) $this;

        foreach($ret as $key => $value){
            if(is_object($value) && is_subclass_of($value, self::class)){
                $ret[$key] = $value->toAssoc($maxDepth - 1);
            }
        }

        return $ret;
    }

    /**
     * Load model parameters from associative array
     * @param array $assoc
     */
    public function fromAssoc(array $assoc){
        foreach($assoc as $key => $value){
            if(property_exists($this, $key)){
                $this->$key = $value;
            }
        }
    }
}