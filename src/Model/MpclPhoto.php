<?php

namespace Reprostar\MpclConnector\Model;

class MpclPhoto extends BaseModel
{
    /** @var int */
    public $id;
    /** @var string */
    public $slug;
    /** @var string|null */
    public $orig_name;
    /** @var int */
    public $size;
    /** @var string */
    public $extension;
    /** @var string|null */
    public $orig_uri;
    /** @var  MpclPhoto[]|string[] */
    public $thumbnails;
}