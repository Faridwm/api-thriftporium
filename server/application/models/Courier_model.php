<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Courier_model extends CI_Model
{
    public function get_courier_all()
    {
        return $this->db->query("SELECT * FROM couriers")->result_array();
    }

    public function get_courier_by_id($id)
    {
        return $this->db->query("SELECT * FROM couriers WHERE id = $id")->row_array();
    }

    public function get_courier_by_name($name)
    {
        return $this->db->query("SELECT * FROM couriers WHERE courier_name LIKE '%$name%'")->result_array();
    }

    public function add_courier($courier_data)
    {
        $name = $courier_data["name"];
        $description = $courier_data["description"];
        $icon = $courier_data["icon"];

        $query_insert = "INSERT INTO couriers VALUES (courier_id(), '$name', '$description', '$icon', NOW())";
        $this->db->trans_begin();
        if (!$this->db->simple_query($query_insert)) {
            $error = $this->db->error();
            $this->db->trans_rollback();
            return $error;
        } else {
            $affected_row = $this->db->affected_rows();
            $this->db->trans_commit();
            return $affected_row;
        }
    }

    public function delete_courier($id)
    {
        $query_delete = "DELETE FROM couriers WHERE id = $id";
        $this->db->trans_begin();
        if (!$this->db->simple_query($query_delete)) {
            $error = $this->db->error();
            $this->db->trans_rollback();
            return $error;
        } else {
            $affected_row = $this->db->affected_rows();
            $this->db->trans_commit();
            return $affected_row;
        }
    }

    public function update_couriers($courier_data)
    {
        $id = $courier_data["id"];
        $name = $courier_data["name"];
        $description = $courier_data["description"];
        $icon = $courier_data["icon"];

        $query_update = "UPDATE couriers SET courier_name = '$name', courier_description = '$description', courier_icon = '$icon' WHERE id = $id";
        $this->db->trans_begin();
        if (!$this->db->simple_query($query_update)) {
            $error = $this->db->error();
            $this->db->trans_rollback();
            return $error;
        } else {
            $affected_row = $this->db->affected_rows();
            $this->db->trans_commit();
            return $affected_row;
        }
    }
}
