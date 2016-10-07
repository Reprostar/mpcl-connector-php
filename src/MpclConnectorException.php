<?php
/**
 * Created by PhpStorm.
 * User: pfcode
 * Date: 17.09.16
 * Time: 00:01
 */

namespace Reprostar\MpclConnector;

use Exception;

class MpclConnectorException extends Exception
{
    private $receivedMessage;
    private $receivedErrorId;

    public function __construct($message, $receivedMessage = null, $errorId = -1)
    {
        parent::__construct($message);

        $this->receivedMessage = $receivedMessage;
        $this->receivedErrorId = $errorId;
    }

    /**
     * @return string
     */
    public function getReceivedErrors(){
        $ret = "#" . (string) intval($this->receivedErrorId);

        if(!is_null($this->receivedMessage)){
            $ret .= ": " . $this->receivedMessage;
        } else{
            $ret .= " [Unknown message]";
        }

        return $ret;
    }
}