<?php

use Opis\JsonSchema\Validator;
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Payment extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        // load model paymnet
        $this->load->model("Payment_model");
    }

    public function index_get()
    {
        $payment = null;

        parse_str($_SERVER["QUERY_STRING"], $query_array);

        if (count($query_array) === 0) {
            $payment = $this->Payment_model->get_payment();
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
                    $payment = $this->Payment_model->get_payment($query_array["id"], null, null, null);
                    break;
                case 'user':
                case 'status':
                case 'order':
                    $status = (isset($query_array["status"])) ? $query_array["status"] : null;
                    $user = (isset($query_array["user"])) ? $query_array["user"] : null;
                    $order = (isset($query_array["order"])) ? $query_array["order"] : null;

                    switch ($status) {
                        case null:
                            $status = null;
                            break;
                        case 'cancel':
                            $status = 0;
                            break;
                        case 'waiting':
                            $status = 1;
                            break;
                        case 'upload':
                            $status = 2;
                            break;
                        case 'verified':
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

                    $payment = $this->Payment_model->get_payment(null, $user, $order, $status);
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
        if ($payment) {
            $api = [
                "code" => 200,
                "status" => true,
                "message" => "successful get payment",
                "data" => $payment
            ];
            $this->response($api, REST_Controller::HTTP_OK);
        } else {
            $api = [
                "code" => 404,
                "status" => false,
                "message" => "payment not found"
            ];
            $this->response($api, REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function index_post()
    {
        $data = $this->post();

        $validate = new Validator();

        $schema = (object) [
            "type" => "object",
            "properties" => (object) [
                "user_id" => (object) [
                    "type" => "integer"
                ],
                "order_id" => (object) [
                    "type" => "integer"
                ],
                "transfer_to" => (object) [
                    "type" => 'integer'
                ],
                "total_price" => (object) [
                    "type" => "number"
                ]
            ],
            "required" => ["user_id", "order_id", "transfer_to", "total_price"],
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
            $result = $this->Payment_model->make_payment($data);
            if ($result === 1) {
                $api = [
                    "code" => 200,
                    "status" => true,
                    "message" => "successful make payment",
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

    public function bank_put($id)
    {
        $data = $this->put();

        $validate = new Validator();

        $schema = (object) [
            "type" => "object",
            "properties" => (object) [
                "account_bank" => (object) [
                    "type" => "string",
                    "maxLength" => 100
                ],
                "account_name" => (object) [
                    "type" => "string",
                    "maxLength" => 100
                ],
                "account_number" => (object) [
                    "type" => 'string',
                    "maxLength" => 100
                ]
            ],
            "required" => ["account_bank", "account_name", "account_number"],
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
            $result = $this->Payment_model->update_payment_bank_user($id, $data);
            if ($result === 1) {
                $api = [
                    "code" => 200,
                    "status" => true,
                    "message" => "successful update user bank account",
                    "data" => null
                ];
                $this->response($api, REST_Controller::HTTP_OK);
            } elseif ($result === 0) {
                $api = [
                    "code" => 304,
                    "status" => false,
                    "message" => "payment status has not modified"
                ];
                $this->response($api, REST_Controller::HTTP_NOT_MODIFIED);
            } elseif (!$this->Payment_model->get_payment((int) $id, null, null, null)) {
                $api = [
                    "code" => 404,
                    "status" => false,
                    "message" => "payment not found"
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

    public function canceled_put($id)
    {
        $result = $this->Payment_model->canceled_payment($id);
        if ($result === 1) {
            $api = [
                "code" => 200,
                "status" => true,
                "message" => "successful change payment status to canceled",
                "data" => null
            ];
            $this->response($api, REST_Controller::HTTP_OK);
        } elseif ($result === 0) {
            $api = [
                "code" => 304,
                "status" => false,
                "message" => "payment status has not modified"
            ];
            $this->response($api, REST_Controller::HTTP_NOT_MODIFIED);
        } elseif ($result === -1) {
            $api = [
                "code" => 400,
                "status" => false,
                "message" => "payment status already canceled"
            ];
            $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
        } elseif ($result === -2) {
            $api = [
                "code" => 400,
                "status" => false,
                "message" => "payment status cannot be modified"
            ];
            $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
        } elseif (!$this->Payment_model->get_payment((int) $id, null, null, null)) {
            $api = [
                "code" => 404,
                "status" => false,
                "message" => "payment not found"
            ];
            $this->response($api, REST_Controller::HTTP_NOT_FOUND);
        } else {
            $api = [
                "code" => 404,
                "status" => false,
                "message" => "payment not found"
            ];
            $this->response($api, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function receipt_put($id)
    {
        $data = $this->put();

        $validate = new Validator();

        $schema = (object) [
            "type" => "object",
            "properties" => (object) [
                "receipt" => (object) [
                    "type" => "string",
                    "maxLength" => 255
                ],
                "method" => (object) [
                    "enum" => ["UPLOAD", "REJECT"]
                ]
            ],
            "required" => ["method"],
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

            $result = $this->Payment_model->update_payment_receipt($id, isset($data["receipt"]) ? $data["receipt"] : null, $data["method"]);
            if ($result === 1) {
                $api = [
                    "code" => 200,
                    "status" => true,
                    "message" => "successful " . $data["method"] . " receipt",
                    "data" => null
                ];
                $this->response($api, REST_Controller::HTTP_OK);
            } elseif ($result === 0) {
                $api = [
                    "code" => 304,
                    "status" => false,
                    "message" => "receipt has not modified"
                ];
                $this->response($api, REST_Controller::HTTP_NOT_MODIFIED);
            } elseif ($result === -1) {
                $api = [
                    "code" => 400,
                    "status" => false,
                    "message" => "Receipt already canceled"
                ];
                $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
            } elseif ($result === -2) {
                $api = [
                    "code" => 400,
                    "status" => false,
                    "message" => "Receipt already verified"
                ];
                $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
            } elseif ($result === -3) {
                $api = [
                    "code" => 400,
                    "status" => false,
                    "message" => "wrong method"
                ];
                $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
            } elseif (!$this->Payment_model->get_payment((int) $id, null, null, null)) {
                $api = [
                    "code" => 404,
                    "status" => false,
                    "message" => "payment not found"
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

    public function transfer_put()
    {
        $result = null;

        parse_str($_SERVER["QUERY_STRING"], $query_array);

        if (count($query_array) === 0) {
            $api = [
                "code" => 400,
                "status" => false,
                "message" => "invalid request in query string"
            ];
            $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
            // $result = $this->Payment_model->update_payment_transfer_to();
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
                case 'id' or 'transfer_to':
                    $result = $this->Payment_model->update_payment_transfer_to((int)$query_array["id"], (int) $query_array["transfer_to"]);
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
                    "message" => "successful change transfer destination",
                    "data" => null
                ];
                $this->response($api, REST_Controller::HTTP_OK);
            } elseif ($result === 0) {
                $api = [
                    "code" => 304,
                    "status" => false,
                    "message" => "payment has not modified"
                ];
                $this->response($api, REST_Controller::HTTP_NOT_MODIFIED);
            } elseif ($result === -1) {
                $api = [
                    "code" => 400,
                    "status" => false,
                    "message" => "payment already canceled"
                ];
                $api['message'] = "payment already canceled";
                $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
            } elseif ($result === -2) {
                $api = [
                    "code" => 400,
                    "status" => false,
                    "message" => "payment already upload receipt"
                ];
                $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
            } elseif (!$this->Payment_model->get_payment((int) $query_array["id"], null, null, null)) {
                $api = [
                    "code" => 404,
                    "status" => false,
                    "message" => "payment not found"
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

    public function verified_put($id)
    {
        $result = $this->Payment_model->verfied_payment($id);
        if ($result === 1) {
            $api = [
                "code" => 200,
                "status" => true,
                "message" => "successful change payment status to verivied",
                "data" => null
            ];
            $this->response($api, REST_Controller::HTTP_OK);
        } elseif ($result === 0) {
            $api = [
                "code" => 304,
                "status" => false,
                "message" => "payment status has not modified"
            ];
            $this->response($api, REST_Controller::HTTP_NOT_MODIFIED);
        } elseif ($result === -1) {
            $api = [
                "code" => 400,
                "status" => false,
                "message" => "payment status already canceled"
            ];
            $api['message'] = "payment status already canceled";
            $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
        } elseif (!$this->Payment_model->get_payment((int) $id, null, null, null)) {
            $api = [
                "code" => 404,
                "status" => false,
                "message" => "payment not found"
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
