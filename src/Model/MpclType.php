<?php

namespace Reprostar\MpclConnector\Model;

class MpclType extends BaseModel
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