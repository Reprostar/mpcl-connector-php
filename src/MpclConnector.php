<?php

/**
 * Created by PhpStorm.
 * User: pfcode
 * Date: 16.09.16
 * Time: 23:58
 */

namespace Reprostar\MpclConnector;

class MpclConnector
{
    const DEFAULT_HOST = "http://mypclist.net/api";
    const DEFAULT_USER_AGENT = "MpclConnectorPHP";
    const DEFAULT_TIMEOUT = 20;

    private $mpcl_host = self::DEFAULT_HOST;
    private $mpcl_apikey = null;
    private $mpcl_apitoken = null;
    private $requestTimeout = self::DEFAULT_TIMEOUT;
    private $userAgent = self::DEFAULT_USER_AGENT;

    private static $timeRequests = 0;

    /**
     * MpclConnector constructor.
     * @param $apikey
     * @param $apitoken
     * @param null $userAgent
     * @param null $requestTimeout
     * @param null $host
     * @throws MpclConnectorException
     */
    public function __construct($apikey, $apitoken, $userAgent = null, $requestTimeout = null, $host = null)
    {
        $this->mpcl_apikey = (string) $apikey;
        $this->mpcl_apitoken = (string) $apitoken;

        if(!is_null($userAgent)){
            $this->userAgent = (string) $userAgent;
        }

        if(!is_null($requestTimeout)){
            $this->requestTimeout = (int) $requestTimeout;
        }

        if(!is_null($host)){
            $this->mpcl_host = (string) $host;
        }

        if (!function_exists('curl_init')) {
            throw new MpclConnectorException("Curl is not enabled on this server");
        }
    }

    /**
     * Perform a CURL request
     * @param $action
     * @param array $params
     * @return bool|mixed
     * @throws MpclConnectorException
     */
    private function doRequest($action, array $params = array())
    {
        $startTime = microtime(1);

        // Build request
        $post["action"] = $action;
        $post["api_key"] = $this->mpcl_apikey;
        $post["api_token"] = $this->mpcl_apitoken;
        $post["params"] = $params;

        // Prepare CURL transaction
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->mpcl_host);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->requestTimeout);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($ch, CURLOPT_HEADER, 1);

        // Execute transaction & get results
        $response = curl_exec($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $result = substr($response, $header_size);

        // Close connection
        curl_close($ch);

        // Measure duration of this request
        $requestDuration = (microtime(1) - $startTime);
        self::$timeRequests += $requestDuration;

        // Validate server response
        $ret = json_decode($result, JSON_OBJECT_AS_ARRAY);
        if (!is_array($ret) || !isset($ret['status'])) {
            throw new MpclConnectorException("Invalid response format");
        }

        if ($ret['status'] == "error") {
            throw new MpclConnectorException("Errors received", $ret['message'], $ret['error_id']);
        }

        return $ret['response'];
    }

    /**
     * @return bool|mixed
     * @throws MpclConnectorException
     */
    public function ping()
    {
        return $this->doRequest("Ping");
    }

    /**
     * @param $id
     * @return MpclMachineRemoteModel
     * @throws MpclConnectorException
     */
    public function getMachine($id)
    {
        $data = $this->doRequest("GetMachine", array(
            "id" => (int) $id
        ));

        $model = new MpclMachineRemoteModel();
        $model->fromAssoc($data);

        return $model;
    }

    /**
     * @param bool $id
     * @return MpclUserRemoteModel
     * @throws MpclConnectorException
     */
    public function getUser($id = false)
    {
        $data =  $this->doRequest("GetUser", array(
            "id" => (int) $id
        ));

        $model = new MpclUserRemoteModel();
        $model->fromAssoc($data);

        return $model;
    }

    /**
     * @param $query
     * @param int $limit
     * @param int $offset
     * @return bool|mixed
     * @throws MpclConnectorException
     */
    public function getTypes($query, $limit = 20, $offset = 0)
    {
        $arr = $this->doRequest("GetTypes", array(
            "query" => (string) $query,
            "limit" => (int) $limit,
            "offset" => (int) $offset
        ));

        foreach($arr as $k => $data){
            $model = new MpclTypeRemoteModel();
            $model->fromAssoc($data);

            $arr[$k] = $model;
        }

        return $arr;
    }

    /**
     * @param $query
     * @param int $limit
     * @param int $offset
     * @return bool|mixed
     * @throws MpclConnectorException
     */
    public function getManufacturers($query, $limit = 20, $offset = 0)
    {
        $arr = $this->doRequest("GetManufacturers", array(
            "query" => (string) $query,
            "limit" => (int) $limit,
            "offset" => (int) $offset
        ));

        foreach($arr as $k => $data){
            $model = new MpclManufacturerRemoteModel();
            $model->fromAssoc($data);

            $arr[$k] = $model;
        }

        return $arr;
    }

    /**
     * @param $slug
     * @return MpclUserRemoteModel
     * @throws MpclConnectorException
     */
    public function getPhoto($slug)
    {
        $data = $this->doRequest("GetPhoto", array(
            "slug" => (string) $slug
        ));

        $model = new MpclUserRemoteModel();
        $model->fromAssoc($data);

        return $model;
    }

    /**
     * @param bool $ids
     * @param bool $onlyStandalone
     * @param bool $onlyExtensions
     * @param int $returnFormat - 0: Models, 1: array of IDs
     * @param int $returnPhotos
     * @param int $limit
     * @param int $offset
     * @param string $orderBy
     * @param int $orderDir - 1: ASC, 2: DESC
     * @return MpclMachinesSetRemoteModel|array
     * @throws MpclConnectorException
     */
    public function getMachinesList($ids = false, $onlyStandalone = false, $onlyExtensions = false, $returnFormat = 0, $returnPhotos = 0, $limit = 20, $offset = 0, $orderBy = "id", $orderDir = 2)
    {
        $params = array(
            'onlyStandalone' => $onlyStandalone ? 1 : 0,
            'onlyExtensions' => $onlyExtensions ? 1 : 0,
            'returnFormat' => (int) $returnFormat,
            'returnPhotos' => $returnPhotos ? 1 : 0,
            'limit' => (int) $limit,
            'offset' => (int) $offset,
            'orderBy' => (string) $orderBy,
            'orderDir' => (int) $orderDir
        );

        if(is_array($ids)){
            $params['ids'] = $ids;
        }

        $data = $this->doRequest("GetMachinesList", $params);

        if($returnFormat == 1){
            return $data;
        } else{
            $model = new MpclMachinesSetRemoteModel();
            $model->fromAssoc($data);

            return $model;
        }
    }

    /**
     * @param $id
     * @return bool|mixed
     * @throws MpclConnectorException
     */
    public function deleteMachine($id){
        return $this->doRequest("DeleteMachine", array(
            "id" => (int) $id
        ));
    }
}