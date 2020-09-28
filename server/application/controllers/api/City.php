<?php

use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . 'libraries/REST_Controller.php';
require APPPATH . 'libraries/Format.php';

class City extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        // load city model
        $this->load->model("City_model");
    }

    public function index_get()
    {
        $city = null;

        parse_str($_SERVER["QUERY_STRING"], $query_array);

        if (count($query_array) === 0) {
            $city = $this->City_model->get_city_all();
        } else {
            $keys = array_keys($query_array);
            if (count($keys) > 1) {
                $error['status'] = 400;
                $error['message'] = "invalid request in query string";
                $this->response($error, REST_Controller::HTTP_BAD_REQUEST);
            }

            switch ($keys[0]) {
                case 'c_id':
                    $city = $this->City_model->get_city_by_city_id((int) $query_array["c_id"]);
                    break;
                case 'c_name':
                    $city = $this->City_model->get_city_by_city_name($query_array["c_name"]);
                    break;
                case 'p_id':
                    $city = $this->City_model->get_city_by_province_id((int) $query_array["p_id"]);
                    break;
                case 'p_name':
                    $city = $this->City_model->get_city_by_province_name($query_array["p_name"]);
                    break;
                default:
                    $api['code'] = 400;
                    $api['status'] = false;
                    $api['message'] = "invalid key";
                    $this->response($api, REST_Controller::HTTP_BAD_REQUEST);
                    break;
            }
        }

        if ($city) {
            $api['code'] = 200;
            $api['status'] = true;
            $api['message'] = 'successful';
            $api['cities'] = $city;
            $this->response($api, REST_Controller::HTTP_OK);
        } else {
            $api['code'] = 404;
            $api['status'] = false;
            $api['message'] = "city not found";
            $this->response($api, REST_Controller::HTTP_NOT_FOUND);
        }
    }
}
