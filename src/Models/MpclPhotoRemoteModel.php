<?php
/**
 * Created by PhpStorm.
 * User: pfcode
 * Date: 17.09.16
 * Time: 00:05
 */

namespace Reprostar\MpclConnector;


class MpclPhotoRemoteModel extends RemoteModel
{
    public $id;
    public $slug;
    public $orig_name;
    public $size;
    public $extension;
    public $orig_uri;

    /** @var  MpclPhotoRemoteModel[]|string[] */
    public $thumbnails;
}