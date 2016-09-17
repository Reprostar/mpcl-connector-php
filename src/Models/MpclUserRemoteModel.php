<?php
/**
 * Created by PhpStorm.
 * User: pfcode
 * Date: 17.09.16
 * Time: 00:06
 */

namespace Reprostar\MpclConnector;


class MpclUserRemoteModel extends RemoteModel
{
    public $id;
    public $username;
    public $email;
    public $machines_count;
    public $last_seen;
    public $register_time;
    public $location;
    public $website;
}