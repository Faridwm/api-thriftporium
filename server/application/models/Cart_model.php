<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cart_model extends CI_Model
{
    public function get_cart_user($user_id)
    {
        return $this->db->query("SELECT * FROM vw_user_cart WHERE user_id = $user_id")->result_array();
    }

    public function check_cart($user_id, $product_id)
    {
        $check = $this->db->query("SELECT 1 FROM carts WHERE user_id = $user_id AND product_id = $product_id")->row_array();
        if ($check) {
            return true;
        } else {
            return false;
        }
    }

    public function add_cart_user($user_id, $product_id, $qty)
    {
        // $check_cart = $this->db->query("SELECT 1 FROM carts WHERE user_id = $user_id AND product_id = $product_id");
        // if ($check_cart) {
        //     return false;
        // }
        $query_insert = "INSERT INTO carts VALUE($user_id, $product_id, $qty)";
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

    public function delete_cart_user($user_id, $product_id)
    {
        $query_delete = "DELETE FROM carts WHERE user_id = $user_id AND product_id = $product_id";
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

    public function update_cart_user($user_id, $product_id, $qty)
    {
        $query_delete = "UPDATE carts SET qty = $qty WHERE user_id = $user_id AND product_id = $product_id";
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
}
