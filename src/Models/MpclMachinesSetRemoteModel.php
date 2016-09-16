<?php
/**
 * Created by PhpStorm.
 * User: pfcode
 * Date: 17.09.16
 * Time: 00:05
 */

namespace pfcode\MpclConnector;


class MpclMachinesSetRemoteModel extends RemoteModel
{
    public $total;
    public $length;
    public $items;

    public function fromAssoc(array $assoc)
    {
        parent::fromAssoc($assoc);

        if(is_array($this->items)){
            foreach($this->items as $k => $item){
                $model = new MpclMachineRemoteModel();
                $model->fromAssoc($item);

                $this->items[$k] = $model;
            }
        }
    }
}