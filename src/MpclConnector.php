<?php
/** @noinspection PhpUnused */

namespace Reprostar\MpclConnector;

use Reprostar\MpclConnector\Model\MpclCategory;
use Reprostar\MpclConnector\Model\MpclMachine;
use Reprostar\MpclConnector\Model\MpclMachinesSet;
use Reprostar\MpclConnector\Model\MpclManufacturer;
use Reprostar\MpclConnector\Model\MpclPhoto;
use Reprostar\MpclConnector\Model\MpclType;
use Reprostar\MpclConnector\Model\MpclUser;
use Reprostar\MpclConnector\Utils\ArgsParser;
use Reprostar\MpclConnector\Utils\Http;

class MpclConnector
{
    const VERSION = '2.0.0';

    private $params = [
        /* MyPCList API endpoint */
        'host' => 'https://mypclist.net/api/',

        /* Maximum time to wait for response */
        'timeout' => 10,

        /* Identification string of this library or application for MyPCList API */
        'user_agent' => 'MpclConnectorPHP/' . self::VERSION,

        /* Legacy API key (optional) */
        /** @deprecated Using separate key and token strings is deprecated. Please use a single api_token instead. */
        'api_key' => null,

        /* API token */
        'api_token' => null,
    ];

    /** @var ArgsParser */
    private $argsParser;

    /** @var Http */
    private $http;

    /**
     * MpclConnector constructor.
     * @param string|array $params - Array with parameters or single-string API token
     * @throws MpclConnectorException
     */
    public function __construct($params)
    {
        if (is_array($params)) {
            $validKeys = array_keys($this->params);

            foreach ($params as $k => $v) {
                if (!in_array($k, $validKeys, true)) {
                    throw new MpclConnectorException("'$k' is not a valid parameter for MpclConnector. Valid options are: " . implode(', ', $validKeys) . '.');
                }

                $this->params[$k] = $v;
            }
        } else if (is_string($params)) {
            $this->params['api_token'] = $params;
        } else {
            throw new MpclConnectorException('Constructor of ' . __CLASS__ . ' expects 1 parameter which is either string with API token or array with connector params.');
        }

        if (!is_string($this->params['host']) || !filter_var($this->params['host'], FILTER_VALIDATE_URL)) {
            throw new MpclConnectorException("'{$this->params['host']}' is not a valid host URL.");
        }

        if (!is_int($this->params['timeout'])) {
            throw new MpclConnectorException("'{$this->params['timeout']}' is not a valid timeout int.");
        }

        if (!is_string($this->params['user_agent'])) {
            throw new MpclConnectorException("'{$this->params['user_agent']}' is not a valid user_agent string.");
        }

        if (!is_string($this->params['api_token'])) {
            throw new MpclConnectorException("'{$this->params['api_token']}' is not a valid api_token string.");
        }

        $separatorPos = strpos($this->params['api_token'], ':');
        if ($this->params['api_key'] === null && ($separatorPos === false || $separatorPos <= 1)) {
            throw new MpclConnectorException("'{$this->params['api_token']}' is not a valid API token. 
            When api_key is not set, api_token is expected to consist of two parts, separated by colon (:) character.");
        }

        if ($this->params['api_key'] !== null && !is_scalar($this->params['api_key'])) {
            throw new MpclConnectorException("'{$this->params['api_key']}' is not a valid api_key string.");
        }

        if ($this->params['api_key'] === null) {
            $apiCredentials = explode(':', $this->params['api_token'], 2);
            $this->params['api_key'] = $apiCredentials[0];
            $this->params['api_token'] = $apiCredentials[1];
        }

        if (!function_exists('curl_version')) {
            throw new MpclConnectorException('MpclConnector needs ext-curl to work properly. Please install and enable cURL extension on your server.');
        }

        if (!function_exists('json_decode')) {
            throw new MpclConnectorException('MpclConnector needs ext-json to work properly. Please install and enable JSON extension on your server.');
        }

        $this->http = new Http($this->params);
        $this->argsParser = new ArgsParser();
    }

    /**
     * @return bool|mixed
     * @throws MpclConnectorException
     */
    public function ping()
    {
        return $this->http->doRequest('Ping');
    }

    /**
     * @param int $id
     * @return MpclMachine
     * @throws MpclConnectorException
     */
    public function getMachine($id)
    {
        $data = $this->http->doRequest('GetMachine', [
            'id' => (int)$id
        ]);

        return MpclMachine::hydrate($data);
    }

    /**
     * @param array|int|null $id
     * @return MpclUser
     * @throws MpclConnectorException
     */
    public function getUser($id = null)
    {
        $data = $this->http->doRequest('GetUser', $this->argsParser->parse($id, ['id' => 0], 'id'));

        return MpclUser::hydrate($data);
    }

    /**
     * @param array|string $query
     * @return bool|MpclType[]
     * @throws MpclConnectorException
     */
    public function getTypes($query)
    {
        $arr = $this->http->doRequest('GetTypes', $this->argsParser->parse($query, [
            'limit' => 20,
            'offset' => 0,
        ], 'query'));

        foreach ($arr as $k => $data) {
            $arr[$k] = MpclType::hydrate($data);
        }

        return $arr;
    }

    /**
     * @param array|string $query
     * @return bool|mixed
     * @throws MpclConnectorException
     */
    public function getManufacturers($query)
    {
        $arr = $this->http->doRequest('GetManufacturers', $this->argsParser->parse($query, [
            'limit' => 20,
            'offset' => 0,
        ], 'query'));

        foreach ($arr as $k => $data) {
            $arr[$k] = MpclManufacturer::hydrate($data);
        }

        return $arr;
    }

    /**
     * @param string $slug
     * @return MpclPhoto
     * @throws MpclConnectorException
     */
    public function getPhoto($slug)
    {
        $data = $this->http->doRequest('GetPhoto', [
            'slug' => (string)$slug
        ]);

        return MpclPhoto::hydrate($data);
    }

    /**
     * @param array $params
     * @return MpclMachinesSet|int[]
     * @throws MpclConnectorException
     */
    public function getMachinesList(array $params)
    {
        $params = $this->argsParser->parse($params, [
            'onlyStandalone' => 0,
            'onlyExtensions' => 0,
            'returnFormat' => MpclMachinesSet::RETURN_MODELS,
            'returnPhotos' => 0,
            'limit' => 20,
            'offset' => 0,
            'orderBy' => 'id',
            'orderDir' => MpclMachinesSet::ORDER_DESC,
        ]);

        $data = $this->http->doRequest('GetMachinesList', $params);

        if ($params['returnFormat'] === MpclMachinesSet::RETURN_IDS) {
            return $data;
        }

        return MpclMachinesSet::hydrate($data);
    }

    /**
     * @param $id
     * @return bool|mixed
     * @throws MpclConnectorException
     */
    public function deleteMachine($id)
    {
        return $this->http->doRequest('DeleteMachine', [
            'id' => (int)$id
        ]);
    }

    /**
     * @param array $params
     * @return MpclCategory
     * @throws MpclConnectorException
     */
    public function createCategory($params)
    {
        $data = $this->http->doRequest('CreateCategory', $this->argsParser->parse($params, [
            'parentId' => 0,
            'isVisible' => 1,
            'name' => 'New category',
        ]));

        return MpclCategory::hydrate($data);
    }

    /**
     * @param int $id
     * @return bool|mixed
     * @throws MpclConnectorException
     */
    public function deleteCategory($id)
    {
        return $this->http->doRequest('DeleteCategory', [
            'id' => (int)$id
        ]);
    }

    /**
     * @param array $params
     * @return MpclCategory[]
     * @throws MpclConnectorException
     */
    public function getCategories($params = [])
    {
        $arr = $this->http->doRequest('GetCategories', $this->argsParser->parse($params));

        foreach ($arr as $k => $data) {
            $arr[$k] = MpclCategory::hydrate($data);
        }

        return $arr;
    }

    /**
     * @param array|int $id
     * @return MpclCategory
     * @throws MpclConnectorException
     */
    public function getCategory($id)
    {
        $data = $this->http->doRequest('GetCategory', $this->argsParser->parse($id, [
            'resolvePath' => 0,
        ], 'id'));

        return MpclCategory::hydrate($data);
    }

    /**
     * @param array $params
     * @return bool|mixed
     * @throws MpclConnectorException
     */
    public function moveCategory($params)
    {
        return $this->http->doRequest('MoveCategory', $this->argsParser->parse($params));
    }

    /**
     * @param array $params
     * @return bool|mixed
     * @throws MpclConnectorException
     */
    public function updateCategory($params)
    {
        return $this->http->doRequest('UpdateCategory', $this->argsParser->parse($params));
    }

    /**
     * @param array $params
     * @return bool|mixed
     * @throws MpclConnectorException
     */
    public function createMachine($params)
    {
        return $this->http->doRequest('UpdateMachine', $this->argsParser->parse($params, [
            'id' => -1,
        ]));
    }

    /**
     * @param array $params
     * @return bool|mixed
     * @throws MpclConnectorException
     */
    public function updateMachine($params)
    {
        return $this->http->doRequest('UpdateMachine', $this->argsParser->parse($params));
    }

    /**
     * @param array $params
     * @return MpclPhoto
     * @throws MpclConnectorException
     */
    public function uploadPhoto($params)
    {
        $data = $this->http->doRequest('UploadPhoto', $this->argsParser->parse($params));

        return MpclPhoto::hydrate($data);
    }
}