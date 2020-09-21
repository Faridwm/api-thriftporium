<?php

use Opis\JsonSchema\Validator;
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class CategoryProduct extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        // load model category prodcut
        $this->load->model("CategoryProduct_model");
    }

    public function index_get()
    {
        $category_product = null;

        parse_str($_SERVER["QUERY_STRING"], $query_array);

        if (count($query_array) === 0) {
            $category_product = $this->CategoryProduct_model->get_category_all();
        } else {
            $keys = array_keys($query_array);
            if (count($keys) > 1) {
                $error['status'] = 400;
                $error['message'] = "invalid request in query string";
                $this->response($error, REST_Controller::HTTP_BAD_REQUEST);
            }
            switch ($keys[0]) {
                case 'id':
                    $category_product = $this->CategoryProduct_model->get_category_by_id((int) $query_array["id"]);
                    break;
                case 'name':
                    $category_product = $this->CategoryProduct_model->get_category_by_name($query_array["name"]);
                    break;
                default:
                    $api['code'] = 400;
                    $api['status'] = false;
                    $api['message'] = "invalid key";
                    $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    break;
            }
        }

        if ($category_product) {
            $api['code'] = 200;
            $api['status'] = true;
            $api['message'] = 'successful';
            $api['category_product'] = $category_product;
            $this->response($api, REST_Controller::HTTP_OK);
        } else {
            $api['code'] = 404;
            $api['status'] = false;
            $api['message'] = "category not found";
            $this->response($api, REST_Controller::HTTP_NOT_FOUND);
        }
    }

    private function cp_validation($json_data)
    {
        $validate = new Validator();

        $schema = (object) [
            "type" => "object",
            "properties" => (object) [
                "name" => (object) [
                    "type" => "string",
                    "maxLength" => 45
                ],
                "description" => (object) [
                    "type" => "string",
                    "maxLength" => 45
                ]
            ],
            "required" => ["name", "description"],
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

        if ($this->cp_validation($data)) {
            $result = $this->CategoryProduct_model->add_product_category($data);
            if ($result === 1) {
                $api['code'] = 200;
                $api['status'] = true;
                $api['message'] = "Category has been added";
                $this->response($api, REST_Controller::HTTP_OK);
            } else {
                $api['code'] = 500;
                $api['status'] = false;
                $api['message'] = "connot add new category";
                $api['error_details'] = $result['message'];
                $this->response($api, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }

    public function index_delete($id)
    {
        $result = $this->CategoryProduct_model->delete_product_category((int) $id);
        if ($result === 1) {
            $api['code'] = 200;
            $api['status'] = true;
            $api['message'] = "Category has been deleted";
            $this->response($api, REST_Controller::HTTP_OK);
        } elseif (!$this->CategoryProduct_model->get_category_by_id((int) $id)) {
            $api['code'] = 404;
            $api['status'] = false;
            $api['message'] = "Category has not found";
            $this->response($api, REST_Controller::HTTP_NOT_FOUND);
        } else {
            $api['code'] = 500;
            $api['status'] = false;
            $api['message'] = "connot delete category";
            $api['error_details'] = $result['message'];
            $this->response($api, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function index_put($id)
    {
        $data = $this->put();

        if ($this->cp_validation($data)) {
            $data["id"] = (int) $id;
            $result = $this->CategoryProduct_model->update_product_category($data);
            if ($result === 1) {
                $api['code'] = 200;
                $api['status'] = true;
                $api['message'] = "Category has been updated";
                $this->response($api, REST_Controller::HTTP_OK);
            } elseif ($result === 0) {
                $api['code'] = 304;
                $api['status'] = false;
                $api['message'] = "Category has not modified";
                $this->response($api, REST_Controller::HTTP_NOT_MODIFIED);
            } elseif (!$this->CategoryProduct_model->get_category_by_id((int) $id)) {
                $api['code'] = 404;
                $api['status'] = false;
                $api['message'] = "Category has not found";
                $this->response($api, REST_Controller::HTTP_NOT_FOUND);
            } else {
                $api['code'] = 500;
                $api['status'] = false;
                $api['message'] = "connot update category";
                $api['error_details'] = $result['message'];
                $this->response($api, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
}
