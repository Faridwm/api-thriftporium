<?php
defined('BASEPATH') or exit('No direct script access allowed');

class City_model extends CI_Model
{
    public function get_city_all()
    {
        return $this->db->query("SELECT c.id, c.city_name, c.city_type, c.city_zipcode, c.province_id, p.province_name FROM city c JOIN province p ON p.id = c.province_id")->result_array();
    }

    public function get_city_by_city_id($id)
    {
        return $this->db->query("SELECT c.id, c.city_name, c.city_type, c.city_zipcode, c.province_id, p.province_name FROM city c JOIN province p ON p.id = c.province_id WHERE c.id = $id")->row_array();
    }

    public function get_city_by_province_id($id)
    {
        return $this->db->query("SELECT c.id, c.city_name, c.city_type, c.city_zipcode, c.province_id, p.province_name FROM city c JOIN province p ON p.id = c.province_id WHERE c.province_id = $id")->result_array();
    }

    public function get_city_by_city_name($name)
    {
        return $this->db->query("SELECT c.id, c.city_name, c.city_type, c.city_zipcode, c.province_id, p.province_name FROM city c JOIN province p ON p.id = c.province_id WHERE c.city_name LIKE '%$name%'")->result_array();
    }

    public function get_city_by_province_name($name)
    {
        return $this->db->query("SELECT c.id, c.city_name, c.city_type, c.city_zipcode, c.province_id, p.province_name FROM city c JOIN province p ON p.id = c.province_id WHERE p.province_name LIKE '%$name%'")->result_array();
    }
}
