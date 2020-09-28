<?php

use Opis\JsonSchema\Validator;
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Products extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        // load model category prodcut
        $this->load->model("Product_model");
    }

    public function index_get()
    {
        $product = null;

        parse_str($_SERVER["QUERY_STRING"], $query_array);
        // var_dump($query_array);
        // die;

        if (count($query_array) === 0) {
            $product = $this->Product_model->get_product_all();
        } else {
            $keys = array_keys($query_array);
            switch ($keys[0]) {
                case 'status' or 'category' or 'name':
                    $status = (isset($query_array["status"])) ? $query_array["status"] : null;
                    $name = (isset($query_array["name"])) ? $query_array["name"] : null;
                    $category_name = (isset($query_array["category"])) ? $query_array["category"] : null;
                    // var_dump($status);
                    // var_dump($name);
                    // var_dump($category_name);
                    // die;
                    switch ($status) {
                        case null:
                            $status = null;
                            break;
                        case 'notpublish':
                            $status = 0;
                            break;
                        case 'publish':
                            $status = 1;
                            break;
                        case 'sold':
                            $status = 2;
                            break;
                        default:
                            $api['code'] = 400;
                            $api['status'] = false;
                            $api['message'] = "invalid key";
                            $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                            break;
                    }
                    $product = $this->Product_model->get_product_all($status, $name, $category_name);
                    break;
                case 'id':
                    $product = $this->Product_model->get_product_by_id((int) $query_array["id"]);
                    break;
                default:
                    $api['code'] = 400;
                    $api['status'] = false;
                    $api['message'] = "invalid key";
                    $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    break;
            }
        }

        if ($product) {
            $api['code'] = 200;
            $api['status'] = true;
            $api['message'] = 'successful';
            $api['products'] = $product;
            $this->response($api, REST_Controller::HTTP_OK);
        } else {
            $api['code'] = 404;
            $api['status'] = false;
            $api['message'] = "product not found";
            $this->response($api, REST_Controller::HTTP_NOT_FOUND);
        }
    }

    private function product_validation($json_data)
    {
        $validate = new Validator();

        $schema = (object) [
            "type" => "object",
            "properties" => (object) [
                "name" => (object) [
                    "type" => "string",
                    "maxLength" => 100
                ],
                "description" => (object) [
                    "type" => "string",
                    "maxLength" => 255
                ],
                "stock" => (object) [
                    "type" => 'ineteger'
                ],
                "category_id" => (object) [
                    "type" => "integer"
                ],
                "price" => (object) [
                    "type" => 'integer'
                ],
                "pictures" => (object) [
                    "type" => "array",
                    "minItems" => 1,
                    "maxItems" => 7,
                    "uniqueItems" => true,
                    "items" => (object) [
                        "type" => "string",
                        "maxLength" => 255
                    ]
                ]
            ],
            "required" => ["name", "description", "stock", "category_id", "price", "pictures"],
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
            return true;
        }
    }

    public function index_post()
    {
        $data = $this->post();

        if ($this->product_validation($data)) {
            $result = $this->Product_model->add_product($data);
            if ($result === 1) {
                $api['code'] = 200;
                $api['status'] = true;
                $api['message'] = "Product has been added";
                $this->response($api, REST_Controller::HTTP_OK);
            } else {
                $api['code'] = 500;
                $api['status'] = false;
                $api['message'] = "connot add new Product";
                $api['error_details'] = $result['message'];
                $this->response($api, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }

    public function index_delete($id)
    {
        $result = $this->Product_model->delete_product((int) $id);
        if ($result === 1) {
            $api['code'] = 200;
            $api['status'] = true;
            $api['message'] = "Product has been deleted";
            $this->response($api, REST_Controller::HTTP_OK);
        } elseif (!$this->Product_model->get_product_by_id((int) $id)) {
            $api['code'] = 404;
            $api['status'] = false;
            $api['message'] = "Product has not found";
            $this->response($api, REST_Controller::HTTP_NOT_FOUND);
        } else {
            $api['code'] = 500;
            $api['status'] = false;
            $api['message'] = "connot delete Product";
            $api['error_details'] = $result['message'];
            $this->response($api, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function index_put($id)
    {
        $data = $this->put();
        if ($this->product_validation($data)) {
            $data["id"] = (int) $id;
            $result = $this->Product_model->update_product($data);
            if ($result === 1) {
                $api['code'] = 200;
                $api['status'] = true;
                $api['message'] = "Product has been updated";
                $this->response($api, REST_Controller::HTTP_OK);
            } elseif ($result === 0) {
                $api['code'] = 304;
                $api['status'] = false;
                $api['message'] = "Product has not modified";
                $this->response($api, REST_Controller::HTTP_NOT_MODIFIED);
            } elseif (!$this->Product_model->get_product_by_id((int) $id)) {
                $api['code'] = 404;
                $api['status'] = false;
                $api['message'] = "Product has not found";
                $this->response($api, REST_Controller::HTTP_NOT_FOUND);
            } else {
                $api['code'] = 500;
                $api['status'] = false;
                $api['message'] = "connot update Product";
                $api['error_details'] = $result['message'];
                $this->response($api, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }

    public function unpublish_put($id)
    {
        $result = $this->Product_model->update_status_product($id, "unpublish");
        if ($result === 1) {
            $api['code'] = 200;
            $api['status'] = true;
            $api['message'] = "Product has been updated";
            $this->response($api, REST_Controller::HTTP_OK);
        } elseif ($result === 0) {
            $api['code'] = 304;
            $api['status'] = false;
            $api['message'] = "Product has not modified";
            $this->response($api, REST_Controller::HTTP_NOT_MODIFIED);
        } elseif (!$this->Product_model->get_product_by_id((int) $id)) {
            $api['code'] = 404;
            $api['status'] = false;
            $api['message'] = "Product has not found";
            $this->response($api, REST_Controller::HTTP_NOT_FOUND);
        } else {
            $api['code'] = 500;
            $api['status'] = false;
            $api['message'] = "connot update Product";
            $api['error_details'] = $result['message'];
            $this->response($api, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function publish_put($id)
    {
        $result = $this->Product_model->update_status_product($id, "publish");
        if ($result === 1) {
            $api['code'] = 200;
            $api['status'] = true;
            $api['message'] = "Product has been updated";
            $this->response($api, REST_Controller::HTTP_OK);
        } elseif ($result === 0) {
            $api['code'] = 304;
            $api['status'] = false;
            $api['message'] = "Product has not modified";
            $this->response($api, REST_Controller::HTTP_NOT_MODIFIED);
        } elseif (!$this->Product_model->get_product_by_id((int) $id)) {
            $api['code'] = 404;
            $api['status'] = false;
            $api['message'] = "Product has not found";
            $this->response($api, REST_Controller::HTTP_NOT_FOUND);
        } else {
            $api['code'] = 500;
            $api['status'] = false;
            $api['message'] = "connot update Product";
            $api['error_details'] = $result['message'];
            $this->response($api, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function sold_put($id)
    {
        $result = $this->Product_model->update_status_product($id, "sold");
        if ($result === 1) {
            $api['code'] = 200;
            $api['status'] = true;
            $api['message'] = "Product has been updated";
            $this->response($api, REST_Controller::HTTP_OK);
        } elseif ($result === 0) {
            $api['code'] = 304;
            $api['status'] = false;
            $api['message'] = "Product has not modified";
            $this->response($api, REST_Controller::HTTP_NOT_MODIFIED);
        } elseif (!$this->Product_model->get_product_by_id((int) $id)) {
            $api['code'] = 404;
            $api['status'] = false;
            $api['message'] = "Product has not found";
            $this->response($api, REST_Controller::HTTP_NOT_FOUND);
        } else {
            $api['code'] = 500;
            $api['status'] = false;
            $api['message'] = "connot update Product";
            $api['error_details'] = $result['message'];
            $this->response($api, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}