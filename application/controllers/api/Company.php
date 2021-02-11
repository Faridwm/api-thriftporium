<?php

use Opis\JsonSchema\Validator;
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Company extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        // load model company
        $this->load->model("Company_model");
    }

    public function index_get()
    {
        $company = $this->Company_model->get_company();
        if ($company) {
            $api = [
                "code" => 200,
                "status" => true,
                "message" => "successful get company",
                "data" => $company
            ];
            $this->response($api, REST_Controller::HTTP_OK);
        } else {
            $api = [
                "code" => 404,
                "status" => false,
                "message" => "Company not found"
            ];
            $this->response($api, REST_Controller::HTTP_NOT_FOUND);
        }
    }

    private function company_validation($json_data)
    {
        $validate = new Validator();

        $schema = (object) [
            "type" => "object",
            "properties" => (object) [
                "name" => (object) [
                    "type" => "string",
                    "maxLength" => 100
                ],
                "street" => (object) [
                    "type" => "string",
                    "maxLength" => 255
                ],
                "city" => (object) [
                    "type" => 'string',
                    "maxLength" => 100
                ],
                "province" => (object) [
                    "type" => 'string',
                    "maxLength" => 100
                ],
                "telp" => (object) [
                    "type" => "string",
                    "maxLength" => 50
                ],
                "fax" => (object) [
                    "type" => 'string',
                    "maxLength" => 50
                ],
                "email" => (object) [
                    "type" => "string",
                    "maxLength" => 100,
                    "format" => "email"
                ],
                "instagram" => (object) [
                    "type" => "string",
                    "maxLength" => 100
                ],
                "twitter" => (object) [
                    "type" => "string",
                    "maxLength" => 100
                ],
                "website" => (object) [
                    "type" => "string",
                    "maxLength" => 100,
                    "format" => "hostname"
                ],

            ],
            "required" => ["name", "street", "city", "province", "telp", "fax", "email", "instagram", "twitter", "website"],
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

    public function index_put($id)
    {
        $data = $this->put();

        if ($this->company_validation($data)) {
            $data["id"] = (int) $id;
            $result = $this->Company_model->update_company($data);
            if ($result === 1) {
                $api = [
                    "code" => 200,
                    "status" => true,
                    "message" => "successful update company",
                    "data" => null
                ];
                $this->response($api, REST_Controller::HTTP_OK);
            } elseif ($result === 0) {
                $api = [
                    "code" => 304,
                    "status" => false,
                    "message" => "Company has not modified"
                ];
                $this->response($api, REST_Controller::HTTP_NOT_MODIFIED);
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
