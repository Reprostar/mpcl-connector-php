<?php


namespace Reprostar\MpclConnector\Utils;


use Reprostar\MpclConnector\MpclConnectorException;

final class Http
{
    /** @var array */
    private $params;

    /**
     * Http constructor.
     * @param array $params
     */
    public function __construct(array $params) {
        $this->params = $params;
    }

    /**
     * Perform a CURL request
     * @param $action
     * @param array $params
     * @return bool|mixed
     * @throws MpclConnectorException
     */
    public function doRequest($action, array $params = [])
    {
        // Build request
        $post['action'] = $action;
        $post['api_key'] = $this->params['api_key'];
        $post['api_token'] = $this->params['api_token'];
        $post['params'] = $params;

        // Prepare cURL transaction
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->params['host']);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->params['user_agent']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->params['timeout']);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($ch, CURLOPT_HEADER, 1);

        // Execute transaction & get results
        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $result = substr($response, $headerSize);

        // Close connection
        curl_close($ch);

        // Validate server response
        $ret = json_decode($result, JSON_OBJECT_AS_ARRAY);
        if (!is_array($ret) || !isset($ret['status'])) {
            throw new MpclConnectorException('Invalid response format has been received from server.');
        }

        if ($ret['status'] === 'error') {
            throw new MpclConnectorException($ret['message'], isset($ret['error_id']) ? $ret['error_id'] : 0);
        }

        return $ret['response'];
    }
}