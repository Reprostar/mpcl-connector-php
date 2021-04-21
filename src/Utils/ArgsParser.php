<?php


namespace Reprostar\MpclConnector\Utils;


final class ArgsParser
{
    /**
     * @param array|scalar $input
     * @param array $defaults
     * @param string|null $defaultField
     * @return array
     */
    public function parse($input, array $defaults = [], $defaultField = null)
    {
        if (is_array($input)) {
            foreach ($input as $k => $v) {
                $defaults[$k] = $v;
            }
        }

        if (is_scalar($input) && $defaultField !== null) {
            $defaults[$defaultField] = $input;
        }

        return $defaults;
    }
}