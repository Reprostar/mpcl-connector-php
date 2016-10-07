<?php
/**
 * Created by PhpStorm.
 * User: pfcode
 * Date: 07.10.16
 * Time: 20:46
 */

namespace Reprostar\MpclConnector;


class MpclCategoryRemoteModel extends RemoteModel
{
    public $id;
    public $uid;
    public $name;
    public $parent_id;
    public $is_visible;
    public $path;
    public $exploded_path;
    public $path_names;
}