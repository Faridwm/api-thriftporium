<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Payaccount_model extends CI_Model
{
    public function get_paymentacc_all()
    {
        return $this->db->query("SELECT * FROM payment_account")->result_array();
    }

    public function get_paymentacc_by_id($id)
    {
        return $this->db->query("SELECT * FROM payment_account WHERE id = $id")->row_array();
    }

    public function get_paymentacc_by_name($name)
    {
        return $this->db->query("SELECT * FROM payment_account WHERE pa_name LIKE '%$name%'")->result_array();
    }

    public function get_paymentacc_by_type($type)
    {
        return $this->db->query("SELECT * FROM payment_account WHERE pa_typr = $type")->result_array();
    }

    public function add_paymentacc($acc_data)
    {
        $name = $acc_data['name'];
        $type = strtoupper($acc_data['type']);
        $account_number = $acc_data['account_number'];
        $account_name = $acc_data['account_name'];
        $description = $acc_data['description'];
        $icon = $acc_data['icon'];

        $query_insert = "INSERT INTO payment_account VALUES (uuid_short(), '$name', '$type', '$account_number', '$account_name', '$description', '$icon')";
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

    public function delete_paymentacc($id)
    {
        $query_delete = "DELETE FROM payment_account WHERE id = $id";
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

    public function update_paymentacc($acc_data)
    {
        $id = $acc_data['id'];
        $name = $acc_data['name'];
        $type = strtoupper($acc_data['type']);
        $account_number = $acc_data['account_number'];
        $account_name = $acc_data['account_name'];
        $description = $acc_data['description'];
        $icon = $acc_data['icon'];

        $query_update = "UPDATE payment_account SET pa_name = '$name', pa_type = '$type', pa_accountnumber = '$account_number', pa_accountname = '$account_name', pa_description = '$description', pa_icon = '$icon' WHERE id = $id";
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
