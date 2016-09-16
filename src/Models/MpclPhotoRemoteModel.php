<?php
/**
 * Created by PhpStorm.
 * User: pfcode
 * Date: 17.09.16
 * Time: 00:05
 */

namespace pfcode\MpclConnector;


class MpclPhotoRemoteModel extends RemoteModel
{
    public $id;
    public $slug;
    public $orig_name;
    public $size;
    public $extension;
    public $orig_uri;
    public $thumbnails;
}