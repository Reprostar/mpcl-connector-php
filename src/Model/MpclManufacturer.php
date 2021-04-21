<?php

namespace Reprostar\MpclConnector\Model;

class MpclManufacturer extends BaseModel
{
    /** @var int */
    public $id;
    /** @var bool */
    public $is_extension;
    /** @var bool */
    public $is_standalone;
    /** @var string */
    public $name;
}