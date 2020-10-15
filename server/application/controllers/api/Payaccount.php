<?php

use Opis\JsonSchema\Validator;
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Payaccount extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        // load model payment account
        $this->load->model("Payaccount_model");
    }

    public function index_get()
    {
        $payment_account = null;

        parse_str($_SERVER["QUERY_STRING"], $query_array);

        if (count($query_array) === 0) {
            $payment_account = $this->Payaccount_model->get_paymentacc_all();
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
                case 'id':
                    $payment_account = $this->Payaccount_model->get_paymentacc_by_id((int) $query_array["id"]);
                    break;
                case 'name':
                    $payment_account = $this->Payaccount_model->get_paymentacc_by_name($query_array["name"]);
                    break;
                case 'type':
                    $payment_account = $this->Payaccount_model->get_paymentacc_by_type($query_array["type"]);
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

        if ($payment_account) {
            $api = [
                "code" => 200,
                "status" => true,
                "message" => "successful get payment account",
                "data" => $payment_account
            ];
            $this->response($api, REST_Controller::HTTP_OK);
        } else {
            $api = [
                "code" => 404,
                "status" => false,
                "message" => "payment account not found"
            ];
            $this->response($api, REST_Controller::HTTP_NOT_FOUND);
        }
    }

    private function pay_acc_validation($json_data)
    {
        $validate = new Validator();

        $schema = (object) [
            "type" => "object",
            "properties" => (object) [
                "name" => (object) [
                    "type" => "string",
                    "maxLength" => 100
                ],
                "type" => (object) [
                    "enum" => ["TRANSFER", "VA", "FINTECH"]
                ],
                "account_number" => (object) [
                    "type" => "string",
                    "maxLength" => 100
                ],
                "account_name" => (object) [
                    "type" => "string",
                    "maxLength" => 100
                ],
                "description" => (object) [
                    "type" => "string",
                    "maxLength" => 255
                ],
                "icon" => (object) [
                    "type" => "string",
                    "maxLength" => 255
                ]
            ],
            "required" => ["name", "type", "account_number", "account_name", "description", "icon"],
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

        if ($this->pay_acc_validation($data)) {
            $result = $this->Payaccount_model->add_paymentacc($data);
            if ($result === 1) {
                $api = [
                    "code" => 200,
                    "status" => true,
                    "message" => "successful create payment account",
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
        $result = $this->Payaccount_model->delete_paymentacc((int) $id);
        if ($result === 1) {
            $api = [
                "code" => 200,
                "status" => true,
                "message" => "successful delete payment account",
                "data" => null
            ];
            $this->response($api, REST_Controller::HTTP_OK);
        } elseif (!$this->Payaccount_model->get_paymentacc_by_id((int) $id)) {
            $api = [
                "code" => 404,
                "status" => false,
                "message" => "payment account not found"
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

        if ($this->pay_acc_validation($data)) {
            $data["id"] = (int) $id;
            $result = $this->Payaccount_model->update_paymentacc($data);
            if ($result === 1) {
                $api = [
                    "code" => 200,
                    "status" => true,
                    "message" => "successful update payment account",
                    "data" => null
                ];
                $this->response($api, REST_Controller::HTTP_OK);
            } elseif ($result === 0) {
                $api = [
                    "code" => 304,
                    "status" => false,
                    "message" => "payment account has not modified"
                ];
                $this->response($api, REST_Controller::HTTP_NOT_MODIFIED);
            } elseif (!$this->Payaccount_model->get_paymentacc_by_id((int) $id)) {
                $api = [
                    "code" => 404,
                    "status" => false,
                    "message" => "payment account not found"
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
