<?php
/**
 * prom.ua HTTP API implementation.
 *
 * @author AlxJzx100 <alxjzx100@gmail.com>
 * @version 1.0.0
 */

namespace Alxjzx100\PromUa;

use Exception;

class PromUa
{
    protected $apiKey;
    protected static $apiUrl = 'https://my.prom.ua/api/v1';
    protected $connectionType = 'curl';

    /**
     * @throws Exception
     */
    public function __construct(string $apiKey)
    {
        if (!empty($apiKey)) {
            $this->apiKey = $apiKey;
        } else {
            throw new Exception("Api key is empty");
        }
    }

    /**
     * curl - default connection type. Any other string given will activate file_get_contents() method
     * @param string $type
     * @return $this
     */
    public function setConnectionType(string $type): PromUa
    {
        $this->connectionType = $type;
        return $this;
    }

    /**
     * Returns current connection type
     * @return string
     */
    public function getConnectionType(): string
    {
        return $this->connectionType;
    }

    /**
     * $params = ['status', 'date_from', 'date_to', 'last_modified_from', 'last_modified_to', 'limit', 'last_id']
     * @param $params
     * @return mixed
     */
    public function getOrders($params = null)
    {
        $path = '/orders/list';

        if ($params !== null)
            $path .= '?' . http_build_query($params);

        return $this->request($path);
    }

    /**
     * Список товаров.
     * $params = array ['last_modified_from', 'last_modified_to', 'limit' , 'last_id', 'group_id']
     * @param $params
     * @return mixed
     */
    public function getProducts($params = null)
    {
        $path = '/products/list';

        if ($params !== null)
            $path .= '?' . http_build_query($params);

        return $this->request($path);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function getProduct(int $id)
    {
        $path = '/products/' . $id;

        return $this->request($path);
    }

    /**
     * Получить товар по внутреннему ID
     * @param string $id
     * @return mixed
     */
    public function getProductByExternalId(string $id)
    {
        $path = '/products/by_external_id/' . urlencode($id);

        return $this->request($path);
    }

    /**
     * $params can be array ['limit' => int, 'last_id' => int, 'search_term' => string ]
     * @param $params
     * @return void
     */
    public function getClients($params = null)
    {
        $path = '/clients/list';

        if ($params !== null)
            $path .= '?' . http_build_query($params);

        return $this->request($path);
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function getOrder(int $id)
    {
        $path = '/orders/' . $id;

        return $this->request($path);
    }

    /**
     * Return order statuses
     * @return mixed
     */
    public function getOrderStatusList()
    {
        $path = '/order_status_options/list';

        return $this->request($path);
    }

    /**
     * Return delivery options list
     * @return mixed
     */
    public function getDeliveryOptionsList()
    {
        $path = '/delivery_options/list';

        return $this->request($path);
    }

    /**
     * [
     *  {
     *      "id": "string", //Обязательно
     *      "presence": "available",
     *      "presence_sure": true,
     *      "price": 0.00001,
     *      "status": "on_display",
     *      "prices": [
     *          {
     *              "price": 0,
     *              "minimum_order_quantity": 0
     *          }
     *      ],
     *      "discount": {
     *          "value": 0,
     *          "type": "amount",
     *          "date_start": "string",
     *          "date_end": "string"
     *      },
     *      "name": "string",
     *      "keywords": "string",
     *      "description": "string",
     *      "quantity_in_stock": 0
     *  }
     * ]
     * @param array $data
     * @return mixed
     */
    public function editProductByExternalId(array $data)
    {
        $path = '/products/edit_by_external_id';
        return $this->request($path, $data, "POST");
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