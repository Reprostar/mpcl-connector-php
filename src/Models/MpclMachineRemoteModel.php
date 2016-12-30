<?php

/**
 * Created by PhpStorm.
 * User: pfcode
 * Date: 17.09.16
 * Time: 00:04
 */

namespace Reprostar\MpclConnector;

class MpclMachineRemoteModel extends RemoteModel
{
    public $id;
    public $uid;
    public $created;
    public $modified;
    public $slug;
    public $is_visible;
    public $description;
    public $physical_state;
    public $custom_name;
    public $name;
    public $manufacturer;
    public $manufacturer_id;
    public $year_of_production;
    public $type_name;
    public $type_id;
    public $serial_number;
    public $price;
    public $photos;
    public $is_extension;
    public $is_standalone;
    public $is_forsale;
}