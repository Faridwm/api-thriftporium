<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Shipping_model extends CI_Model
{
    public function get_shipping($id = null, $user = null, $status = null)
    {
        $query = "SELECT * FROM vw_shipping";
        if ($user === null && $status === null) {
            $query = $query;
        } elseif ($id && (!$user) && (!$status)) {
            $query = $query . " WHERE id = $id";
        } else {
            if ($status !== null) {
                $query = $query . " WHERE shipping_status = $status";
                if ($user) {
                    $query = $query . " AND user_id = $user";
                }
            } elseif ($user !== null) {
                $query = $query . " WHERE user_id = $user";
            }
        }
        // var_dump($query);
        // die;

        $shipping = $this->db->query($query)->result_array();

        if ($shipping) {
            return $shipping;
        } else {
            return false;
        }
    }

    public function update_shipping_receipt($id, $receipt, $picture)
    {
        $query = "UPDATE shipping SET shipping_receipt = '$receipt', shipping_receipt_picture = '$picture', shipping_status = 2, modifiedAt = NOW() WHERE id = $id";
        $this->db->trans_begin();
        if (!$this->db->simple_query($query)) {
            $error = $this->db->error();
            $this->db->trans_rollback();
            return $error;
        } else {
            $affected_row = $this->db->affected_rows();
            $this->db->trans_commit();
            return $affected_row;
        }
    }

    public function arrive_shipping($id)
    {
        $query = "UPDATE shipping SET shipping_status = 3, modifiedAt = NOW() WHERE id = $id";
        $this->db->trans_begin();
        if (!$this->db->simple_query($query)) {
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
