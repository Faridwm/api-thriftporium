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
                $error['status'] = 400;
                $error['message'] = "invalid request in query string";
                $this->response($error, REST_Controller::HTTP_BAD_REQUEST);
            }

            switch ($keys[0]) {
                case 'user':
                    $cart = $this->Cart_model->get_cart_user((int) $query_array["user"]);
                    break;
                default:
                    $api['code'] = 400;
                    $api['status'] = false;
                    $api['message'] = "invalid key";
                    $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    break;
            }
        }

        if ($cart) {
            $api['code'] = 200;
            $api['status'] = true;
            $api['message'] = 'successful';
            $api['cart'] = $cart;
            $this->response($api, REST_Controller::HTTP_OK);
        } else {
            $api['code'] = 404;
            $api['status'] = false;
            $api['message'] = "cart is empty";
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
                $error['status'] = 400;
                $error['message'] = "invalid request in query string";
                $this->response($error, REST_Controller::HTTP_BAD_REQUEST);
            }

            switch ($keys[0]) {
                case 'user':
                    if (isset($query_array["product"]) && isset($query_array["qty"])) {
                        if (!$this->Cart_model->check_cart($query_array["user"], $query_array["product"])) {
                            $result = $this->Cart_model->add_cart_user((int) $query_array["user"], (int) $query_array["product"], (int) $query_array["qty"]);
                        } else {
                            $api['code'] = 400;
                            $api['status'] = false;
                            $api['message'] = "product already in cart";
                            $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                        }
                    } else {
                        $api['code'] = 400;
                        $api['status'] = false;
                        $api['message'] = "invalid key";
                        $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    }
                    break;
                case 'product':
                    if (isset($query_array["user"]) && isset($query_array["qty"])) {
                        if (!$this->Cart_model->check_cart($query_array["user"], $query_array["product"])) {
                            $result = $this->Cart_model->add_cart_user((int) $query_array["user"], (int) $query_array["product"], (int) $query_array["qty"]);
                        } else {
                            $api['code'] = 400;
                            $api['status'] = false;
                            $api['message'] = "product already in cart";
                            $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                        }
                    } else {
                        $api['code'] = 400;
                        $api['status'] = false;
                        $api['message'] = "invalid key";
                        $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    }
                    break;
                case 'qty':
                    if (isset($query_array["user"]) && isset($query_array["product"])) {
                        if (!$this->Cart_model->check_cart($query_array["user"], $query_array["product"])) {
                            $result = $this->Cart_model->add_cart_user((int) $query_array["user"], (int) $query_array["product"], (int) $query_array["qty"]);
                        } else {
                            $api['code'] = 400;
                            $api['status'] = false;
                            $api['message'] = "product already in cart";
                            $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                        }
                    } else {
                        $api['code'] = 400;
                        $api['status'] = false;
                        $api['message'] = "invalid key";
                        $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    }
                    break;
                default:
                    $api['code'] = 400;
                    $api['status'] = false;
                    $api['message'] = "invalid key";
                    $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    break;
            }
        }

        if ($result === 1) {
            $api['code'] = 200;
            $api['status'] = true;
            $api['message'] = "Product has been added to cart";
            $this->response($api, REST_Controller::HTTP_OK);
        } else {
            $api['code'] = 500;
            $api['status'] = false;
            $api['message'] = "connot add product to cart";
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
                $error['status'] = 400;
                $error['message'] = "invalid request in query string";
                $this->response($error, REST_Controller::HTTP_BAD_REQUEST);
            }

            switch ($keys[0]) {
                case 'user':
                    if (isset($query_array["product"])) {
                        $result = $this->Cart_model->delete_cart_user($query_array["user"], $query_array["product"]);
                    } else {
                        $api['code'] = 400;
                        $api['status'] = false;
                        $api['message'] = "invalid key";
                        $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    }
                    break;
                case 'product':
                    if (isset($query_array["user"])) {
                        $result = $this->Cart_model->delete_cart_user($query_array["user"], $query_array["product"]);
                    } else {
                        $api['code'] = 400;
                        $api['status'] = false;
                        $api['message'] = "invalid key";
                        $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    }
                    break;
                default:
                    $api['code'] = 400;
                    $api['status'] = false;
                    $api['message'] = "invalid key";
                    $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    break;
            }
        }

        if ($result === 1) {
            $api['code'] = 200;
            $api['status'] = true;
            $api['message'] = "product has been deleted from cart";
            $this->response($api, REST_Controller::HTTP_OK);
        } elseif ($result === 0) {
            $api['code'] = 404;
            $api['status'] = false;
            $api['message'] = "product not found";
        } else {
            $api['code'] = 500;
            $api['status'] = false;
            $api['message'] = "connot delete product";
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
                $error['status'] = 400;
                $error['message'] = "invalid request in query string";
                $this->response($error, REST_Controller::HTTP_BAD_REQUEST);
            }

            switch ($keys[0]) {
                case 'user':
                    if (isset($query_array["product"]) && isset($query_array["qty"])) {
                        $result = $this->Cart_model->update_cart_user($query_array["user"], $query_array["product"], $query_array["qty"]);
                    } else {
                        $api['code'] = 400;
                        $api['status'] = false;
                        $api['message'] = "invalid key";
                        $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    }
                    break;
                case 'product':
                    if (isset($query_array["user"]) && isset($query_array["qty"])) {
                        $result = $this->Cart_model->update_cart_user($query_array["user"], $query_array["product"], $query_array["qty"]);
                    } else {
                        $api['code'] = 400;
                        $api['status'] = false;
                        $api['message'] = "invalid key";
                        $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    }
                    break;
                case 'qty':
                    if (isset($query_array["user"]) && isset($query_array["product"])) {
                        $result = $this->Cart_model->update_cart_user($query_array["user"], $query_array["product"], $query_array["qty"]);
                    } else {
                        $api['code'] = 400;
                        $api['status'] = false;
                        $api['message'] = "invalid key";
                        $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    }
                    break;
                default:
                    $api['code'] = 400;
                    $api['status'] = false;
                    $api['message'] = "invalid key";
                    $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    break;
            }

            if ($result === 1) {
                $api['code'] = 200;
                $api['status'] = true;
                $api['message'] = "Cart has been update";
                $this->response($api, REST_Controller::HTTP_OK);
            } elseif ($result === 0) {
                $api['code'] = 304;
                $api['status'] = false;
                $api['message'] = "Cart not modified";
                $this->response($api, REST_Controller::HTTP_NOT_MODIFIED);
            } elseif (!$this->Cart_model->check_cart($query_array["user"], $query_array["product"])) {
                $api['code'] = 404;
                $api['status'] = false;
                $api['message'] = "product not found";
            } else {
                $api['code'] = 500;
                $api['status'] = false;
                $api['message'] = "connot update cart";
                $this->response($api, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
}
