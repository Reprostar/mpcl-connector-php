<?php

namespace Reprostar\MpclConnector\Model;

class MpclMachine extends BaseModel
{
    /** @var int */
    public $id;
    /** @var int */
    public $uid;
    /** @var int */
    public $created;
    /** @var int|null */
    public $modified;
    /** @var string */
    public $slug;
    /** @var bool */
    public $is_visible;
    /** @var string|null */
    public $description;
    /** @var int */
    public $physical_state;
    /** @var string|null */
    public $custom_name;
    /** @var string */
    public $name;
    /** @var string|null */
    public $manufacturer;
    /** @var int|null */
    public $manufacturer_id;
    /** @var string|null */
    public $year_of_production;
    /** @var string|null */
    public $type_name;
    /** @var int|null */
    public $type_id;
    /** @var string|null */
    public $serial_number;
    /** @var string|null */
    public $price;
    /** @var string[]|MpclPhoto[] */
    public $photos;
    /** @var bool */
    public $is_extension;
    /** @var bool */
    public $is_standalone;
    /** @var bool */
    public $is_forsale;
}