<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Province_model extends CI_Model
{
    public function get_province_all()
    {
        return $this->db->query("SELECT id, province_name FROM province")->result_array();
    }

    public function get_province_by_id($id)
    {
        return $this->db->query("SELECT id, province_name FROM province WHERE id = $id")->row_array();
    }

    public function get_province_by_name($name)
    {
        return $this->db->query("SELECT id, province_name FROM province WHERE province_name LIKE '%$name%'")->result_array();
    }
}
