<?php

use Opis\JsonSchema\Validator;
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Cart extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        // load model Cart
        $this->load->model("Cart_model");
    }

    public function index_get()
    {
        $cart = null;

        parse_str($_SERVER["QUERY_STRING"], $query_array);

        if (count($query_array) === 0) {
            redirect('welcome');
        } else {
            $keys = array_keys($query_array);
            if (count($keys) > 1) {
                $api = [
                    "code" => 400,
                    "status" => false,
                    "message" => "invalid request in query string"
                ];
                $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
            }

            switch ($keys[0]) {
                case 'user':
                    $cart = $this->Cart_model->get_cart_user((int) $query_array["user"]);
                    break;
                default:
                    $api = [
                        "code" => 400,
                        "status" => false,
                        "message" => "invalid key in query string"
                    ];
                    $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    break;
            }
        }

        if ($cart) {
            $api = [
                "code" => 200,
                "status" => true,
                "message" => "succesful get user cart",
                "data" => $cart
            ];
            $this->response($api, REST_Controller::HTTP_OK);
        } else {
            $api = [
                "code" => 404,
                "status" => false,
                "message" => "cart is empty"
            ];
            $this->response($api, REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function index_post($user_id)
    {
        $data = $this->post();
        $validate = new Validator();

        $schema = (object) [
            "type" => "object",
            "properties" => (object) [
                "product" => (object) [
                    "type" => "integer"
                ],
                "qty" => (object) [
                    "type" => "integer"
                ]
            ],
            "required" => ["product", "qty"],
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
            $result = $this->Cart_model->add_cart_user((int) $user_id, (int) $data["product"], (int) $data["qty"]);
            if ($result === 1) {
                $api = [
                    "code" => 200,
                    "status" => true,
                    "message" => "successful insert product to cart",
                    "data" => null
                ];
                $this->response($api, REST_Controller::HTTP_OK);
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

    public function index_delete($user_id, $product_id)
    {
        // var_dump($user_id);
        // var_dump($product_id);
        // die;
        $result = $this->Cart_model->delete_cart_user((int) $user_id, (int) $product_id);
        if ($result === 1) {
            $api = [
                "code" => 200,
                "status" => true,
                "message" => "successful delete product from cart",
                "data" => null
            ];
            $this->response($api, REST_Controller::HTTP_OK);
        } elseif ($result === 0) {
            $api = [
                "code" => 404,
                "status" => false,
                "message" => "product not found in cart"
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

    public function index_put($user_id)
    {
        $data = $this->put();

        $validate = new Validator();

        $schema = (object) [
            "type" => "object",
            "properties" => (object) [
                "product" => (object) [
                    "type" => "integer"
                ],
                "qty" => (object) [
                    "type" => "integer"
                ]
            ],
            "required" => ["product", "qty"],
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
            $result = $this->Cart_model->update_cart_user((int) $user_id, (int) $data["product"], (int) $data["qty"]);
            if ($result === 1) {
                $api = [
                    "code" => 200,
                    "status" => true,
                    "message" => "successful update product in cart",
                    "data" => null
                ];
                $this->response($api, REST_Controller::HTTP_OK);
            } elseif ($result === 0) {
                $api = [
                    "code" => 304,
                    "status" => false,
                    "message" => "cart not modified"
                ];
                $this->response($api, REST_Controller::HTTP_NOT_MODIFIED);
            } elseif (!$this->Cart_model->check_cart((int) $user_id, (int) $data["product"])) {
                $api = [
                    "code" => 404,
                    "status" => false,
                    "message" => "product not found"
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
}
