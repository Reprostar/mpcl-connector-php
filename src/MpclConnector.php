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
    const DEFAULT_HOST = 'https://mypclist.net/api/';
    const DEFAULT_USER_AGENT = 'MpclConnectorPHP';
    const DEFAULT_TIMEOUT = 20;

    private $apiHost = self::DEFAULT_HOST;
    private $apiKey;
    private $apiToken;
    private $requestTimeout = self::DEFAULT_TIMEOUT;
    private $userAgent = self::DEFAULT_USER_AGENT;

    private $totalTimeSpent = 0;

    /**
     * MpclConnector constructor.
     * @param $apiKey
     * @param $apiToken
     * @param null $userAgent
     * @param null $requestTimeout
     * @param null $apiHost
     * @throws MpclConnectorException
     */
    public function __construct($apiKey, $apiToken, $userAgent = null, $requestTimeout = null, $apiHost = null)
    {
        $this->apiKey = (string) $apiKey;
        $this->apiToken = (string) $apiToken;

        if($userAgent !== null){
            $this->userAgent = (string) $userAgent;
        }

        if($requestTimeout !== null){
            $this->requestTimeout = (int) $requestTimeout;
        }

        if($apiHost !== null){
            $this->apiHost = (string) $apiHost;
        }

        if (!function_exists('curl_init')) {
            throw new MpclConnectorException('Curl is not enabled on this server');
        }
    }

    /**
     * Get time in seconds spent on the network communication with API server
     * @return float
     */
    public function getTotalTimeSpent(){
        return $this->totalTimeSpent;
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
        $post['action'] = $action;
        $post['api_key'] = $this->apiKey;
        $post['api_token'] = $this->apiToken;
        $post['params'] = $params;

        // Prepare CURL transaction
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiHost);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->requestTimeout);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($ch, CURLOPT_HEADER, 1);

        // Execute transaction & get results
        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $result = substr($response, $headerSize);

        // Close connection
        curl_close($ch);

        // Measure duration of this request
        $requestDuration = (microtime(1) - $startTime);
        $this->totalTimeSpent += $requestDuration;

        // Validate server response
        $ret = json_decode($result, JSON_OBJECT_AS_ARRAY);
        if (!is_array($ret) || !isset($ret['status'])) {
            throw new MpclConnectorException('Invalid response format');
        }

        if ($ret['status'] === 'error') {
            throw new MpclConnectorException('Errors received', $ret['message'], isset($ret['error_id']) ? $ret['error_id'] : -1);
        }

        return $ret['response'];
    }

    /**
     * @return bool|mixed
     * @throws MpclConnectorException
     */
    public function ping()
    {
        return $this->doRequest('Ping');
    }

    /**
     * @param $id
     * @return MpclMachineRemoteModel
     * @throws MpclConnectorException
     */
    public function getMachine($id)
    {
        $data = $this->doRequest('GetMachine', array(
            'id' => (int) $id
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
        $data =  $this->doRequest('GetUser', array(
            'id' => (int) $id
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
        $arr = $this->doRequest('GetTypes', array(
            'query' => (string) $query,
            'limit' => (int) $limit,
            'offset' => (int) $offset
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
        $arr = $this->doRequest('GetManufacturers', array(
            'query' => (string) $query,
            'limit' => (int) $limit,
            'offset' => (int) $offset
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
     * @return MpclPhotoRemoteModel
     * @throws MpclConnectorException
     */
    public function getPhoto($slug)
    {
        $data = $this->doRequest('GetPhoto', array(
            'slug' => (string) $slug
        ));

        $model = new MpclPhotoRemoteModel();
        $model->fromAssoc($data);

        return $model;
    }

    /**
     * @param bool|array $ids
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
    public function getMachinesList($ids = false, $onlyStandalone = false, $onlyExtensions = false, $returnFormat = 0, $returnPhotos = 0, $limit = 20, $offset = 0, $orderBy = 'id', $orderDir = 2)
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

        $data = $this->doRequest('GetMachinesList', $params);

        if($returnFormat === 1){
            return $data;
        }

        $model = new MpclMachinesSetRemoteModel();
        $model->fromAssoc($data);

        return $model;
    }

    /**
     * @param $id
     * @return bool|mixed
     * @throws MpclConnectorException
     */
    public function deleteMachine($id){
        return $this->doRequest('DeleteMachine', array(
            'id' => (int) $id
        ));
    }

    /**
     * @param $name
     * @param int $parentId
     * @param bool $isVisible
     * @return MpclCategoryRemoteModel
     * @throws MpclConnectorException
     */
    public function createCategory($name, $parentId = 0, $isVisible = true){
        $data = $this->doRequest('CreateCategory', array(
            'name' => (string) $name,
            'parentId' => (int) $parentId,
            'isVisible' => (boolean) $isVisible
        ));

        $model = new MpclCategoryRemoteModel();
        $model->fromAssoc($data);

        return $model;
    }

    /**
     * @param $id
     * @return bool|mixed
     * @throws MpclConnectorException
     */
    public function deleteCategory($id){
        return $this->doRequest('DeleteCategory', array(
            'id' => (int) $id
        ));
    }

    /**
     * @param null $parentId
     * @return MpclCategoryRemoteModel[]
     * @throws MpclConnectorException
     */
    public function getCategories($parentId = null){
        $params = array();

        if($parentId !== null){
            $params['parentId'] = (int) $parentId;
        }

        $arr = $this->doRequest('GetCategories', $params);

        foreach($arr as $k => $data){
            $model = new MpclCategoryRemoteModel();
            $model->fromAssoc($data);

            $arr[$k] = $model;
        }

        return $arr;
    }

    /**
     * @param $id
     * @param bool $resolvePath
     * @return MpclCategoryRemoteModel
     * @throws MpclConnectorException
     */
    public function getCategory($id, $resolvePath = false){
        $data = $this->doRequest('GetCategory', array(
            'id' => (int) $id,
            'resolvePath' => (boolean) $resolvePath
        ));

        $model = new MpclCategoryRemoteModel();
        $model->fromAssoc($data);

        return $model;
    }

    /**
     * @param $id
     * @param $parentId
     * @return bool|mixed
     * @throws MpclConnectorException
     */
    public function moveCategory($id, $parentId){
        return $this->doRequest('MoveCategory', array(
            'id' => (int) $id,
            'parentId' => (int) $parentId
        ));
    }

    /**
     * @param $id
     * @param $name
     * @param $isVisible
     * @return bool|mixed
     * @throws MpclConnectorException
     */
    public function updateCategory($id, $name, $isVisible){
        return $this->doRequest('UpdateCategory', array(
            'id' => (int) $id,
            'name' => (string) $name,
            'isVisible' => (boolean) $isVisible
        ));
    }

    /**
     * @param $data
     * @return bool|mixed
     * @throws MpclConnectorException
     */
    public function createMachine($data){
        return $this->updateMachine(-1, $data);
    }

    /**
     * @param $id
     * @param $data
     * @return bool|mixed
     * @throws MpclConnectorException
     */
    public function updateMachine($id, $data){
        return $this->doRequest('UpdateMachine', [
            'id' => (int) $id
        ] + $data);
    }

    /**
     * @param $data
     * @param $origName
     * @return MpclPhotoRemoteModel
     * @throws MpclConnectorException
     */
    public function uploadPhoto($data, $origName){
        $data = $this->doRequest('UploadPhoto', [
            'data' => $data,
            'orig_name' => $origName
        ]);

        $ret = new MpclPhotoRemoteModel();
        $ret->fromAssoc($data);

        return $ret;
    }
}