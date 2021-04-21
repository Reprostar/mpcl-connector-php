<?php

namespace Reprostar\MpclConnector\Model;

class MpclMachinesSet extends BaseModel
{
    const ORDER_ASC = 1;
    const ORDER_DESC = 2;

    const RETURN_MODELS = 0;
    const RETURN_IDS = 1;

    /** @var int */
    public $total = 0;
    /** @var int */
    public $length = 0;
    /** @var  MpclMachine[] */
    public $items = [];

    public static function hydrate($data)
    {
        $model = parent::hydrate($data);

        if (($model instanceof self) && is_array($model->items)) {
            foreach ($model->items as $k => $item) {
                $model->items[$k] = MpclMachine::hydrate($item);
            }
        }

        return $model;
    }
}