<?php

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

    public function index_post()
    {
        $result = null;

        parse_str($_SERVER["QUERY_STRING"], $query_array);

        if (count($query_array) === 0) {
            redirect('welcome');
        } else {
            $keys = array_keys($query_array);
            if (count($keys) > 3) {
                $api = [
                    "code" => 400,
                    "status" => false,
                    "message" => "invalid request in query string"
                ];
                $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
            }

            switch ($keys[0]) {
                case 'user':
                    if (isset($query_array["product"]) && isset($query_array["qty"])) {
                        if (!$this->Cart_model->check_cart($query_array["user"], $query_array["product"])) {
                            $result = $this->Cart_model->add_cart_user((int) $query_array["user"], (int) $query_array["product"], (int) $query_array["qty"]);
                        } else {
                            $api = [
                                "code" => 400,
                                "status" => false,
                                "message" => "product already in cart"
                            ];
                            $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                        }
                    } else {
                        $api = [
                            "code" => 400,
                            "status" => false,
                            "message" => "invalid key"
                        ];
                        $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    }
                    break;
                case 'product':
                    if (isset($query_array["user"]) && isset($query_array["qty"])) {
                        if (!$this->Cart_model->check_cart($query_array["user"], $query_array["product"])) {
                            $result = $this->Cart_model->add_cart_user((int) $query_array["user"], (int) $query_array["product"], (int) $query_array["qty"]);
                        } else {
                            $api = [
                                "code" => 400,
                                "status" => false,
                                "message" => "product already in cart"
                            ];
                            $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                        }
                    } else {
                        $api = [
                            "code" => 400,
                            "status" => false,
                            "message" => "invalid key"
                        ];
                        $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    }
                    break;
                case 'qty':
                    if (isset($query_array["user"]) && isset($query_array["product"])) {
                        if (!$this->Cart_model->check_cart($query_array["user"], $query_array["product"])) {
                            $result = $this->Cart_model->add_cart_user((int) $query_array["user"], (int) $query_array["product"], (int) $query_array["qty"]);
                        } else {
                            $api = [
                                "code" => 400,
                                "status" => false,
                                "message" => "product already in cart"
                            ];
                            $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                        }
                    } else {
                        $api = [
                            "code" => 400,
                            "status" => false,
                            "message" => "invalid key"
                        ];
                        $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    }
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

    public function index_delete()
    {
        $result = null;

        parse_str($_SERVER["QUERY_STRING"], $query_array);

        if (count($query_array) === 0) {
            redirect('welcome');
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
                case 'user':
                    if (isset($query_array["product"])) {
                        $result = $this->Cart_model->delete_cart_user($query_array["user"], $query_array["product"]);
                    } else {
                        $api = [
                            "code" => 400,
                            "status" => false,
                            "message" => "invalid key"
                        ];
                        $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    }
                    break;
                case 'product':
                    if (isset($query_array["user"])) {
                        $result = $this->Cart_model->delete_cart_user($query_array["user"], $query_array["product"]);
                    } else {
                        $api = [
                            "code" => 400,
                            "status" => false,
                            "message" => "invalid key"
                        ];
                        $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    }
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

    public function index_put()
    {
        $result = null;

        parse_str($_SERVER["QUERY_STRING"], $query_array);

        if (count($query_array) === 0) {
            redirect('welcome');
        } else {
            $keys = array_keys($query_array);
            if (count($keys) > 3) {
                $api = [
                    "code" => 400,
                    "status" => false,
                    "message" => "invalid request in query string"
                ];
                $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
            }

            switch ($keys[0]) {
                case 'user':
                    if (isset($query_array["product"]) && isset($query_array["qty"])) {
                        $result = $this->Cart_model->update_cart_user($query_array["user"], $query_array["product"], $query_array["qty"]);
                    } else {
                        $api = [
                            "code" => 400,
                            "status" => false,
                            "message" => "invalid key"
                        ];
                        $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    }
                    break;
                case 'product':
                    if (isset($query_array["user"]) && isset($query_array["qty"])) {
                        $result = $this->Cart_model->update_cart_user($query_array["user"], $query_array["product"], $query_array["qty"]);
                    } else {
                        $api = [
                            "code" => 400,
                            "status" => false,
                            "message" => "invalid key"
                        ];
                        $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    }
                    break;
                case 'qty':
                    if (isset($query_array["user"]) && isset($query_array["product"])) {
                        $result = $this->Cart_model->update_cart_user($query_array["user"], $query_array["product"], $query_array["qty"]);
                    } else {
                        $api = [
                            "code" => 400,
                            "status" => false,
                            "message" => "invalid key"
                        ];
                        $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    }
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
            } elseif (!$this->Cart_model->check_cart($query_array["user"], $query_array["product"])) {
                $api = [
                    "code" => 404,
                    "status" => false,
                    "message" => "product not found"
                ];
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
