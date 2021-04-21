<?php

namespace Reprostar\MpclConnector\Model;

class MpclUser extends BaseModel
{
    /** @var int */
    public $id;
    /** @var string */
    public $username;
    /** @var string */
    public $email;
    /** @var int */
    public $machines_count;
    /** @var int */
    public $last_seen;
    /** @var int */
    public $register_time;
    /** @var string|null */
    public $location;
    /** @var string|null */
    public $website;
}