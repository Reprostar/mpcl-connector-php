<?php

namespace Reprostar\MpclConnector\Model;

class MpclCategory extends BaseModel
{
    /** @var int */
    public $id;
    /** @var int */
    public $uid;
    /** @var string */
    public $name;
    /** @var int|null */
    public $parent_id;
    /** @var bool */
    public $is_visible;
    /** @var string */
    public $path;
    /** @var string[] */
    public $exploded_path;
    /** @var array[] */
    public $path_names;
}