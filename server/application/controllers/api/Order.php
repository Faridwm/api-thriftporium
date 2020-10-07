<?php

use Opis\JsonSchema\Validator;
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Order extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        // load model order
        $this->load->model("Order_model");
    }

    public function index_get()
    {
        $order = null;

        parse_str($_SERVER["QUERY_STRING"], $query_array);

        if (count($query_array) === 0) {
            $order = $this->Order_model->get_order();
        } else {
            $keys = array_keys($query_array);
            if (count($keys) > 2) {
                $api['code'] = 400;
                $api['status'] = false;
                $api['message'] = "invalid request in query string";
                $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
            }
            switch ($keys[0]) {
                case 'id':
                    if ($keys[1]) {
                        $api['code'] = 400;
                        $api['status'] = false;
                        $api['message'] = "invalid key";
                        $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                        break;
                    }
                    $order = $this->Order_model->get_order($query_array["id"], null, null);
                    break;
                case 'user' or 'status':
                    $status = (isset($query_array["status"])) ? $query_array["status"] : null;
                    $user = (isset($query_array["user"])) ? $query_array["user"] : null;

                    switch ($status) {
                        case null:
                            $status = null;
                            break;
                        case 'cancel':
                            $status = 0;
                            break;
                        case 'order':
                            $status = 1;
                            break;
                        case 'payment':
                            $status = 2;
                            break;
                        case 'done':
                            $status = 3;
                            break;
                        default:
                            $api['code'] = 400;
                            $api['status'] = false;
                            $api['message'] = "invalid key";
                            $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                            break;
                    }

                    $order = $this->Order_model->get_order(null, $user, $status);
                    break;
                default:
                    $api['code'] = 400;
                    $api['status'] = false;
                    $api['message'] = "invalid key";
                    $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    break;
            }
        }
        if ($order) {
            $api['code'] = 200;
            $api['status'] = true;
            $api['message'] = 'successful';
            $api['orders'] = $order;
            $this->response($api, REST_Controller::HTTP_OK);
        } else {
            $api['code'] = 404;
            $api['status'] = false;
            $api['message'] = "order not found";
            $this->response($api, REST_Controller::HTTP_NOT_FOUND);
        }
    }

    private function order_validation($json_data)
    {
        $validate = new Validator();

        $schema = (object) [
            "type" => "object",
            "properties" => (object) [
                "user" => (object) [
                    "type" => "integer"
                ],
                "street" => (object) [
                    "type" => "string",
                    "maxLength" => 255
                ],
                "city" => (object) [
                    "type" => 'integer'
                ],
                "zipcode" => (object) [
                    "type" => "string",
                    "maxLength" => 10
                ],
                "shipping_receiver" => (object) [
                    "type" => "string",
                    "maxLength" => 100
                ],
                "shipping_phone" => (object) [
                    "type" => "string",
                    "maxLength" => 20
                ],
                "shipping_courier" => (object) [
                    "type" => 'integer'
                ],
                "shipping_price" => (object) [
                    "type" => "integer"
                ],
                "products" => (object) [
                    "type" => "array",
                    "minItems" => 1
                ]
            ],
            "required" => ["user", "street", "city", "zipcode", "shipping_receiver", "shipping_phone", "shipping_courier", "shipping_price", "products"],
            "additionalProperties" => false
        ];

        $validation = $validate->dataValidation((object) $json_data, $schema);
        if (!$validation->isValid()) {
            $api['code'] = 400;
            $api['status'] = false;
            $api['error'] = $validation->getFirstError()->keyword();
            $api['error_data'] =  $validation->getFirstError()->dataPointer();
            // $api['message'] = $api['error'] . " in " .  $api['error_data'] . " field";
            $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
        } else {
            // var_dump($json_data["products"]);
            // die;
            for ($i = 0; $i < count($json_data["products"]); $i++) {
                $schema_product = (object) [
                    "type" => "object",
                    "properties" => (object) [
                        "product_id" => (object) [
                            "type" => "integer"
                        ],
                        "qty" => (object) [
                            "type" => "integer"
                        ]
                    ],
                    "required" => ["product_id", "qty"],
                    "additionalProperties" => false
                ];

                $validation_product = $validate->dataValidation((object) $json_data["products"][$i], $schema_product);
                if (!$validation_product->isValid()) {
                    $api['code'] = 400;
                    $api['status'] = false;
                    $api['error'] = $validation_product->getFirstError()->keyword();
                    $api['error_data'] =  $validation_product->getFirstError()->dataPointer();
                    // $api['message'] = $api['error'] . " in " .  $api['error_data'] . " field";
                    $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                }
            }
            return true;
        }
    }

    public function index_post()
    {
        $data = $this->post();

        if ($this->order_validation($data)) {
            $result = $this->Order_model->make_order($data);
            if ($result === 1) {
                $api['code'] = 200;
                $api['status'] = true;
                $api['message'] = "Order has been created";
                $this->response($api, REST_Controller::HTTP_OK);
            } elseif ($result === -1) {
                $api['code'] = 400;
                $api['status'] = false;
                $api['message'] = "Order gagal, Produk habis";
                $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
            } else {
                $api['code'] = 500;
                $api['status'] = false;
                $api['message'] = "connot make order";
                $api['error_details'] = $result['message'];
                $this->response($api, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }

    public function topayment_put($order_id)
    {
        $result = $this->Order_model->topayment_order($order_id);

        if ($result === 1) {
            $api['code'] = 200;
            $api['status'] = true;
            $api['message'] = "Order status has been updated to payment";
            $this->response($api, REST_Controller::HTTP_OK);
        } elseif ($result === 0) {
            $api['code'] = 304;
            $api['status'] = false;
            $api['message'] = "Order status has not modified";
            $this->response($api, REST_Controller::HTTP_NOT_MODIFIED);
        } elseif (!$this->Order_model->get_order((int) $order_id, null, null)) {
            $api['code'] = 404;
            $api['status'] = false;
            $api['message'] = "Order has not found";
            $this->response($api, REST_Controller::HTTP_NOT_FOUND);
        } else {
            $api['code'] = 500;
            $api['status'] = false;
            $api['message'] = "connot update Order";
            $api['error_details'] = $result['message'];
            $this->response($api, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function canceled_put($order_id)
    {
        $result = $this->Order_model->topayment_order($order_id);

        if ($result === 1) {
            $api['code'] = 200;
            $api['status'] = true;
            $api['message'] = "Order status has been canceled";
            $this->response($api, REST_Controller::HTTP_OK);
        } elseif ($result === 0) {
            $api['code'] = 304;
            $api['status'] = false;
            $api['message'] = "Order status has not modified";
            $this->response($api, REST_Controller::HTTP_NOT_MODIFIED);
        } elseif (!$this->Order_model->get_Order_by_id((int) $order_id, null, null)) {
            $api['code'] = 404;
            $api['status'] = false;
            $api['message'] = "Order has not found";
            $this->response($api, REST_Controller::HTTP_NOT_FOUND);
        } else {
            $api['code'] = 500;
            $api['status'] = false;
            $api['message'] = "connot update Order";
            $api['error_details'] = $result['message'];
            $this->response($api, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
