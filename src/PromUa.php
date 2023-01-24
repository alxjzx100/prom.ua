<?php
/**
 * prom.ua HTTP API implementation.
 *
 * @author AlxJzx100 <alxjzx100@gmail.com>
 * @version 1.0.0
 */

namespace Alxjzx100\PromUa;

use Exception;

class PromUa {
    //68a635740de6f2806f7e48756b06c81882630d55
    protected $apiKey;
    protected static $apiUrl = 'https://my.prom.ua/api/v1';
    protected $connectionType = 'curl';

    /**
     * @throws Exception
     */
    public function __construct(string $apiKey)
    {
        if(!empty($apiKey)){
            $this->apiKey = $apiKey;
        }else{
            throw new Exception("Api key is empty");
        }
    }

    public function getConnectionType(): string
    {
        return $this->connectionType;
    }

    public function getOrders($params = null){
        $path = '/orders/list';

        if($params !== null)
            $path .= '?'.http_build_query($params);

        return $this->request($path);
    }

    public function getOrder(int $id){
        $path = '/orders/'.$id;

        return $this->request($path);
    }

    private function request(string $path, $params = null, string $method = "GET")
    {
        $post = $params
            ? json_encode($params)
            : '';

        $header = [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ];
        $url = self::$apiUrl . $path;

        if ('curl' == $this->getConnectionType()) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $result = curl_exec($ch);
            curl_close($ch);
        } else {
            $context = [
                'http' => [
                    'method' => $method,
                    'header' => implode("\r\n", $header),
                    'ssl' => [
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                    ],
                    'content' => $post ?? '',
                ],
            ];

            $result = file_get_contents($url, false, stream_context_create($context));
        }
        return json_decode($result);
    }

}