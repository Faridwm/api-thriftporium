<?php

use Opis\JsonSchema\Validator;
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Shipping extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        // load model shipping
        $this->load->model("Shipping_model");
    }

    public function index_get()
    {
        $shipping = null;

        parse_str($_SERVER["QUERY_STRING"], $query_array);

        if (count($query_array) === 0) {
            $shipping = $this->Shipping_model->get_shipping();
        } else {
            $keys = array_keys($query_array);
            if (count($keys) > 2) {
                $api = [
                    "code" => 400,
                    "status" => false,
                    "message" => "invalid request in query string"
                ];
                $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
            }
            switch ($keys[0]) {
                case 'id':
                    if (isset($keys[1])) {
                        $api = [
                            "code" => 400,
                            "status" => false,
                            "message" => "invalid key"
                        ];
                        $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                        break;
                    }
                    $shipping = $this->Shipping_model->get_shipping((int) $query_array["id"], null, null);
                    break;
                case 'user':
                case 'status':
                case 'order':
                    $status = (isset($query_array["status"])) ? $query_array["status"] : null;
                    $user = (isset($query_array["user"])) ? $query_array["user"] : null;

                    switch ($status) {
                        case null:
                            $status = null;
                            break;
                        case 'cancel':
                            $status = 0;
                            break;
                        case 'packing':
                            $status = 1;
                            break;
                        case 'delivery':
                            $status = 2;
                            break;
                        case 'arrived':
                            $status = 3;
                            break;
                        default:
                            $api = [
                                "code" => 400,
                                "status" => false,
                                "message" => "invalid key"
                            ];
                            $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                            break;
                    }

                    $shipping = $this->Shipping_model->get_shipping(null, $user, $status);
                    break;
                default:
                    $api = [
                        "code" => 400,
                        "status" => false,
                        "message" => "invalid key"
                    ];
                    $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    break;
            }
        }
        if ($shipping) {
            $api = [
                "code" => 200,
                "status" => true,
                "message" => "successful get shipping",
                "data" => $shipping
            ];
            $this->response($api, REST_Controller::HTTP_OK);
        } else {
            $api = [
                "code" => 404,
                "status" => false,
                "message" => "shipping not found"
            ];
            $this->response($api, REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function receipt_put($id)
    {
        $data = $this->put();
        $validate = new Validator();

        $schema = (object) [
            "type" => "object",
            "properties" => (object) [
                "receipt_number" => (object) [
                    "type" => "string",
                    "maxLenght" => 100
                ],
                "receipt_picture" => (object) [
                    "type" => "string",
                    "maxLength" => 255
                ]
            ],
            "required" => ["receipt_number", "receipt_picture"],
            "additionalProperties" => false
        ];

        $validation = $validate->dataValidation((object) $data, $schema);
        if (!$validation->isValid()) {
            $api = [
                "code" => 400,
                "status" => false,
                "message" => $validation->getFirstError()->keyword(),
                "error_detail" => $validation->getFirstError()->dataPointer()
            ];
            // $api['message'] = $api['error'] . " in " .  $api['error_data'] . " field";
            $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
        } else {
            $result = $this->Shipping_model->update_shipping_receipt((int) $id, $data["receipt_number"], $data["receipt_picture"]);
            if ($result === 1) {
                $api = [
                    "code" => 200,
                    "status" => true,
                    "message" => "successful add shipping receipt",
                    "data" => null
                ];
                $this->response($api, REST_Controller::HTTP_OK);
            } elseif ($result === 0) {
                $api = [
                    "code" => 304,
                    "status" => false,
                    "message" => "Shipping status has not modified"
                ];
                $this->response($api, REST_Controller::HTTP_NOT_MODIFIED);
            } elseif (!$this->Shipping_model->get_shipping((int) $id)) {
                $api = [
                    "code" => 404,
                    "status" => false,
                    "message" => "shipping not found"
                ];
                $this->response($api, REST_Controller::HTTP_NOT_FOUND);
            } else {
                $api = [
                    "code" => 500,
                    "status" => false,
                    "message" => "failed",
                    "error_detail" => $result["message"]
                ];
                $this->response($api, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }

    public function arrived_put($id)
    {
        $result = $this->Shipping_model->arrive_shipping((int) $id);
        if ($result === 1) {
            $api = [
                "code" => 200,
                "status" => true,
                "message" => "successful change shipping status to arrived",
                "data" => null
            ];
            $this->response($api, REST_Controller::HTTP_OK);
        } elseif ($result === 0) {
            $api = [
                "code" => 304,
                "status" => false,
                "message" => "Shipping status has not modified"
            ];
            $this->response($api, REST_Controller::HTTP_NOT_MODIFIED);
        } elseif (!$this->Shipping_model->get_shipping((int) $id)) {
            $api = [
                "code" => 404,
                "status" => false,
                "message" => "shipping not found"
            ];
            $this->response($api, REST_Controller::HTTP_NOT_FOUND);
        } else {
            $api = [
                "code" => 500,
                "status" => false,
                "message" => "failed",
                "error_detail" => $result["message"]
            ];
            $this->response($api, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
