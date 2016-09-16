<?php
/**
 * Created by PhpStorm.
 * User: pfcode
 * Date: 17.09.16
 * Time: 00:01
 */

namespace pfcode\MpclConnector;

use Exception;

class MpclConnectorException extends Exception
{
    private $received_message;
    private $received_error_id;

    public function __construct($message, $received_message = null, $error_id = -1)
    {
        parent::__construct($message);

        $this->received_message = $received_message;
        $this->received_error_id = $error_id;
    }

    /**
     * @return string
     */
    public function getReceivedErrors(){
        $ret = "#" . (string) intval($this->received_error_id);

        if(!is_null($this->received_message)){
            $ret .= ": " . $this->received_message;
        } else{
            $ret .= " [Unknown message]";
        }

        return $ret;
    }
}