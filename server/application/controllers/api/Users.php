<?php

use Opis\JsonSchema\Validator;
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class Users extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        // load model user
        $this->load->model("Users_model");
    }

    public function roles_get()
    {
        $user_role = $this->Users_model->get_user_role();

        // var_dump($user_role);
        // die;
        if ($user_role) {
            $api = [
                "code" => 200,
                "status" => true,
                "message" => "successful get user role",
                "data" => $user_role
            ];
            $this->response($api, REST_Controller::HTTP_OK);
        } else {
            $api = [
                "code" => 404,
                "status" => false,
                "message" => "role not found"
            ];
            $this->response($api, REST_Controller::HTTP_NOT_FOUND);
        }
    }

    public function index_get()
    {
        $users = null;

        parse_str($_SERVER["QUERY_STRING"], $query_array);

        if (count($query_array) === 0) {
            $users = $this->Users_model->get_user_all();
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
                    }
                    $users = $this->Users_model->get_user_by_id((int) $query_array["id"]);
                    break;
                case 'email':
                    if (isset($keys[1])) {
                        $api = [
                            "code" => 400,
                            "status" => false,
                            "message" => "invalid key"
                        ];
                        $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    }
                    $users = $this->Users_model->get_user_by_email($query_array["email"]);
                    break;
                case 'role_id':
                    if (isset($keys[1])) {
                        $api = [
                            "code" => 400,
                            "status" => false,
                            "message" => "invalid key"
                        ];
                        $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    }
                    $users = $this->Users_model->get_user_by_role_id($query_array["role_id"]);
                    break;
                case 'uid':
                    if ($keys[1] === 'provider') {
                        $users = $this->Users_model->get_user_by_sosmed($query_array["uid"], $query_array["provider"]);
                    } else {
                        $api = [
                            "code" => 400,
                            "status" => false,
                            "message" => "invalid key"
                        ];
                        $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    }
                    break;
                case 'provider':
                    if ($keys[1] === 'uid') {
                        $users = $this->Users_model->get_user_by_sosmed($query_array["uid"], $query_array["provider"]);
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

        if ($users) {
            $api = [
                "code" => 200,
                "status" => true,
                "message" => "successful get user",
                "data" => $users
            ];
            $this->response($api, REST_Controller::HTTP_OK);
        } else {
            $api = [
                "code" => 404,
                "status" => false,
                "message" => "user not found"
            ];
            $this->response($api, REST_Controller::HTTP_NOT_FOUND);
        }
    }

    private function register_validation($json_data)
    {
        $validate = new Validator();

        $schema = (object) [
            "type" => "object",
            "properties" => (object) [
                "first_name" => (object) [
                    "type" => "string",
                    "maxLength" => 45
                ],
                "last_name" => (object) [
                    "type" => "string",
                    "maxLength" => 45
                ],
                "email" => (object) [
                    "type" => 'string',
                    "format" => 'email',
                    "maxLength" => 100
                ],
                "password" => (object) [
                    "type" => "string",
                    "maxLength" => 255
                ],
                "role" => (object) [
                    "type" => 'integer'
                ]
            ],
            "required" => ["first_name", "last_name", "email", "password", "role"],
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
        $data['role'] = 2001;

        if ($this->register_validation($data)) {
            // var_dump($data);
            // die;
            $result = $this->Users_model->add_user($data);
            if ($result === 1) {
                $api = [
                    "code" => 200,
                    "status" => true,
                    "message" => "successful create user",
                    "data" => null
                ];
                // $api['user'] = $message;
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

    private function login_validation($json_data)
    {
        $validate = new Validator();

        $schema = (object) [
            "type" => "object",
            "properties" => (object) [
                "email" => (object) [
                    "type" => 'string',
                    "format" => 'email',
                    "maxLength" => 100
                ],
                "password" => (object) [
                    "type" => "string",
                    "maxLength" => 255
                ]
            ],
            "required" => ["email", "password"],
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

    public function login_post()
    {
        $data = $this->post();
        if ($this->login_validation($data)) {
            $result = $this->Users_model->login($data);
            // var_dump($result);
            // die;
            if ($result['status']) {
                $api = [
                    "code" => 200,
                    "status" => true,
                    "message" => "successful login",
                    "data" =>  $result['login_data']
                ];
                $this->response($api, REST_Controller::HTTP_OK);
            } else {
                if ($result['code'] === 404) {
                    $api = [
                        "code" => 404,
                        "status" => false,
                        "message" => "failed",
                        "error_detail" => $result["message"]
                    ];
                    $this->response($api, REST_Controller::HTTP_NOT_FOUND);
                } elseif ($result['code'] === 400) {
                    $api = [
                        "code" => 400,
                        "status" => false,
                        "message" => "failed",
                        "error_detail" => $result["message"]
                    ];
                    $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                }
            }
        }
    }

    public function detail_get($id)
    {
        $user_detail = $this->Users_model->get_user_details((int) $id);
        if ($user_detail) {
            $api = [
                "code" => 200,
                "status" => true,
                "message" => "successful get user detail",
                "data" => $user_detail
            ];
            $this->response($api, REST_Controller::HTTP_OK);
        } else {
            $api = [
                "code" => 404,
                "status" => false,
                "message" => "user not found"
            ];
            $this->response($api, REST_Controller::HTTP_NOT_FOUND);
        }
    }

    private function detail_validation($json_data)
    {
        $validate = new Validator();

        $schema = (object) [
            "type" => "object",
            "properties" => (object) [
                "first_name" => (object) [
                    "type" => "string",
                    "maxLength" => 45
                ],
                "last_name" => (object) [
                    "type" => "string",
                    "maxLength" => 45
                ],
                "gender" => (object) [
                    "enum" => ['M', 'F']
                ],
                "phone" => (object) [
                    "type" => 'string',
                    "maxLength" => 20
                ],
                "address" => (object) [
                    "type" => "string",
                    "maxLength" => 255
                ],
                "city" => (object) [
                    "type" => 'integer'
                ],
                "zipcode" => (object) [
                    "type" => "string",
                    "maxLength" => 10
                ],
                "image" => (object) [
                    "type" => "string",
                    "maxLength" => 255
                ]
            ],
            "required" => ["first_name", "last_name", "gender", "phone", "address", "city", "zipcode", "image"],
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
        if ($this->detail_validation($data)) {
            $data['id'] = (int) $id;

            $result = $this->Users_model->update_user_detail($data);
            if ($result === 1) {
                $api = [
                    "code" => 200,
                    "status" => true,
                    "message" => "successful update user detail",
                    "data" => null
                ];
                $this->response($api, REST_Controller::HTTP_OK);
            } elseif ($result === 0) {
                $api = [
                    "code" => 304,
                    "status" => false,
                    "message" => "profile not changed"
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

    private function change_password_validation($json_data)
    {
        $validate = new Validator();

        $schema = (object) [
            "type" => "object",
            "properties" => (object) [
                "old_password" => (object) [
                    "type" => "string",
                    "maxLength" => 255
                ],
                "new_password" => (object) [
                    "type" => "string",
                    "maxLength" => 255
                ]
            ],
            "required" => ["old_password", "new_password"],
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

    public function password_put($id)
    {
        $data = $this->put();
        if ($this->change_password_validation($data)) {
            $data['id'] = (int) $id;
            $result = $this->Users_model->update_password($data);
            if ($result === 1) {
                $api = [
                    "code" => 200,
                    "status" => true,
                    "message" => "successful update password",
                    "data" => null
                ];
                $this->response($api, REST_Controller::HTTP_OK);
            } elseif ($result === 0) {
                $api = [
                    "code" => 304,
                    "status" => false,
                    "message" => "password not changed"
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

    public function index_delete($id)
    {
        $result = $this->Users_model->delete_user($id);
        if ($result === 1) {
            $api = [
                "code" => 200,
                "status" => true,
                "message" => "successful delete user",
                "data" => null
            ];
            $this->response($api, REST_Controller::HTTP_OK);
        } elseif ($result === 0) {
            $api = [
                "code" => 304,
                "status" => false,
                "message" => "user not deleted"
            ];
            $this->response($api, REST_Controller::HTTP_NOT_MODIFIED);
        } elseif (!$this->Users_model->get_user_by_id((int) $id)) {
            $api = [
                "code" => 404,
                "status" => false,
                "message" => "user not found"
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

    public function socmed_post()
    {
        $data = $this->post();

        $validate = new Validator();

        $schema = (object) [
            "type" => "object",
            "properties" => (object) [
                "first_name" => (object) [
                    "type" => "string",
                    "maxLength" => 45
                ],
                "last_name" => (object) [
                    "type" => "string",
                    "maxLength" => 45
                ],
                "email" => (object) [
                    "type" => 'string',
                    "format" => 'email',
                    "maxLength" => 100
                ],
                "uid" => (object) [
                    "type" => "string",
                    "maxLength" => 100
                ],
                "provider" => (object) [
                    "type" => "string",
                    "maxLength" => 20
                ],
                "role" => (object) [
                    "type" => 'integer'
                ]
            ],
            "required" => ["first_name", "last_name", "email", "uid", "provider", "role"],
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
            $result = $this->Users_model->add_user_with_sosmed($data);
            if ($result >= 1) {
                $api = [
                    "code" => 200,
                    "status" => true,
                    "message" => "successful create user with social media account",
                    "data" => null
                ];
                $this->response($api, REST_Controller::HTTP_OK);
            } elseif ($result === -1) {
                $api = [
                    "code" => 400,
                    "status" => false,
                    "message" => "cannot register sosmed, because it's not user"
                ];
                $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
            } elseif ($result === -2) {
                $api = [
                    "code" => 400,
                    "status" => false,
                    "message" => "cannot register sosmed, because sosmed has already register"
                ];
                $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
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
