<?php

use Opis\JsonSchema\Validator;
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Courier extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        // load model courier
        $this->load->model("Courier_model");
    }

    public function index_get()
    {
        $courier = null;

        parse_str($_SERVER["QUERY_STRING"], $query_array);

        if (count($query_array) === 0) {
            $courier = $this->Courier_model->get_courier_all();
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
                    $courier = $this->Courier_model->get_courier_by_id((int) $query_array["id"]);
                    break;
                case 'name':
                    $courier = $this->Courier_model->get_courier_by_name($query_array["name"]);
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

        if ($courier) {
            $api = [
                "code" => 200,
                "status" => true,
                "message" => "successful",
                "data" => $courier
            ];
            $this->response($api, REST_Controller::HTTP_OK);
        } else {
            $api = [
                "code" => 404,
                "status" => false,
                "message" => "failed",
                "error_detail" => "courier not found"
            ];
            $this->response($api, REST_Controller::HTTP_NOT_FOUND);
        }
    }

    private function courier_validation($json_data)
    {
        $validate = new Validator();

        $schema = (object) [
            "type" => "object",
            "properties" => (object) [
                "name" => (object) [
                    "type" => "string",
                    "maxLength" => 50
                ],
                "description" => (object) [
                    "type" => "string",
                    "maxLength" => 255
                ],
                "icon" => (object) [
                    "type" => "string",
                    "maxLength" => 100
                ]
            ],
            "required" => ["name", "description", "icon"],
            "additionalProperties" => false
        ];

        $validation = $validate->dataValidation((object) $json_data, $schema);
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
            return true;
        }
    }

    public function index_post()
    {
        $data = $this->post();

        if ($this->courier_validation($data)) {
            $result = $this->Courier_model->add_courier($data);
            if ($result === 1) {
                $api = [
                    "code" => 200,
                    "status" => true,
                    "message" => "successful",
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

    public function index_delete($id)
    {
        $result = $this->Courier_model->delete_courier((int) $id);
        if ($result === 1) {
            $api = [
                "code" => 200,
                "status" => true,
                "message" => "successful",
                "data" => null
            ];
            $this->response($api, REST_Controller::HTTP_OK);
        } elseif (!$this->Courier_model->get_courier_by_id((int) $id)) {
            $api = [
                "code" => 404,
                "status" => false,
                "message" => "failed",
                "error_detail" => "Courier not found"
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

    public function index_put($id)
    {
        $data = $this->put();

        if ($this->courier_validation($data)) {
            $data["id"] = (int) $id;
            $result = $this->Courier_model->update_courier($data);
            if ($result === 1) {
                $api = [
                    "code" => 200,
                    "status" => true,
                    "message" => "successful",
                    "data" => null
                ];
                $this->response($api, REST_Controller::HTTP_OK);
            } elseif ($result === 0) {
                $api = [
                    "code" => 304,
                    "status" => false,
                    "message" => "failed",
                    "error_detail" => "Courier has not modified"
                ];
                $this->response($api, REST_Controller::HTTP_NOT_MODIFIED);
            } elseif (!$this->Courier_model->get_courier_by_id((int) $id)) {
                $api = [
                    "code" => 404,
                    "status" => false,
                    "message" => "failed",
                    "error_detail" => "Courier not found"
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
