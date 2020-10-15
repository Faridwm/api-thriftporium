<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Province extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        // load province model
        $this->load->model("Province_model");
    }

    public function index_get()
    {
        $province = null;

        parse_str($_SERVER["QUERY_STRING"], $query_array);

        if (count($query_array) === 0) {
            $province = $this->Province_model->get_province_all();
        } else {
            $keys = array_keys($query_array);
            if (count($keys) > 1) {
                $api = [
                    "code" => 400,
                    "status" => false,
                    "message" => "failed",
                    "error_detail" => "invalid request in query string"
                ];
                $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
            }

            switch ($keys[0]) {
                case 'id':
                    $province = $this->Province_model->get_province_by_id((int)$query_array["id"]);
                    break;
                case 'name':
                    $province = $this->Province_model->get_province_by_name($query_array["name"]);
                    break;
                default:
                    $api = [
                        "code" => 400,
                        "status" => false,
                        "message" => "failed",
                        "error_detail" => "invalid key"
                    ];
                    $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    break;
            }
        }

        if ($province) {
            $api = [
                "code" => 200,
                "status" => true,
                "message" => "successful",
                "data" => $province
            ];
            $this->response($api, REST_Controller::HTTP_OK);
        } else {
            $api = [
                "code" => 404,
                "status" => false,
                "message" => "failed",
                "error_detail" => "Province not found"
            ];
            $this->response($api, REST_Controller::HTTP_NOT_FOUND);
        }
    }
}
