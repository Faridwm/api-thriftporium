<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Payment_model extends CI_Model
{
    public function get_payment($payment_id = null, $user = null, $order_id = null, int $status = null)
    {
        $query = "SELECT * FROM vw_payment";
        if ($user === null && $status === null && $payment_id === null) {
            $query = $query;
        } elseif ($payment_id && (!$user) && (!$status) && (!$order_id)) {
            $query = $query . " WHERE id = $payment_id";
        } else {
            if ($status !== null) {
                $query = $query . " WHERE payment_status = $status";
                if ($user) {
                    $query = $query . " AND user_id = $user";
                }
                if ($order_id) {
                    $query = $query . " AND order_id = $order_id";
                }
            } elseif ($user !== null) {
                $query = $query . " WHERE user_id = $user";

                if ($order_id) {
                    $query = $query . " AND order_id = $order_id";
                }
            } elseif ($order_id !== null) {
                $query = $query . " AND order_id = $order_id";
            }
        }
        // var_dump($query);
        // die;

        $payment = $this->db->query($query)->result_array();

        if ($payment) {
            return $payment;
        } else {
            return false;
        }
    }

    public function make_payment($payment_data)
    {
        $order_id = $payment_data["order_id"];
        $user_id = $payment_data["user_id"];
        $transfer_to = $payment_data["transfer_to"];
        $total_price = $payment_data["total_price"];

        $query_insert = "INSERT INTO payments(id, inv_number, order_id, user_id, payment_transfer_to, total_price) VALUES (uuid_short(), inv_num(), $order_id, $user_id, $transfer_to, $total_price)";
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

    public function update_payment_bank_user($payment_id, $account_detail)
    {
        $bank = $account_detail["account_bank"];
        $name = $account_detail["account_name"];
        $number = $account_detail["account_number"];

        $query = "UPDATE payments SET payment_accountbank = '$bank', payment_accountname = '$name', payment_accountnumber = '$number' WHERE id = $payment_id";
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

    public function canceled_payment($payment_id)
    {
        $status = $this->db->query("SELECT payment_status FROM payments WHERE id = $payment_id")->row_array()["payment_status"];

        if ($status === 0) {
            return -1; // sudah di cancel
        } elseif ($status > 1) {
            return -2; // sudah upload bukti bayar, gak bisa cancel
        } else {
            $query = "UPDATE payments SET payment_status = 0 WHERE id = $payment_id";
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

    public function update_payment_receipt($payment_id, $receipt = null, $method)
    {
        $status = $this->db->query("SELECT payment_status FROM payments WHERE id = $payment_id")->row_array()["payment_status"];

        if ($status === 0) {
            return -1; // payment udah di cancel
        } elseif ($status > 2) {
            return -2; // payment udah diverifikasi
        } else {
            if ($method === "UPLOAD") {
                $query_update_payment = "UPDATE payments SET payment_receipt = $receipt, payment_status = 2 WHERE id = $payment_id";
            } elseif ($method === "REJECT") {
                $query_update_payment = "UPDATE payments SET payment_receipt = '', payment_status = 1 WHERE id = $payment_id";
            } else {
                return -3; // salah method
            }
            $this->db->trans_begin();
            if (!$this->db->simple_query($query_update_payment)) {
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

    public function update_payment_transfer_to($payment_id, $transfer_to)
    {
        $status = $this->db->query("SELECT payment_status FROM payments WHERE id = $payment_id")->row_array()["payment_status"];

        if ($status === 0) {
            return -1; //udah di cancel
        } elseif ($status > 1) {
            return -1; // udah upload bukti bayar
        } else {
            $query = "UPDATE payments SET payment_transfet_to = $transfer_to WHERE id = $payment_id";
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

    public function verfied_payment($payment_id)
    {
        $status = $this->db->query("SELECT payment_status FROM payments WHERE id = $payment_id")->row_array()["payment_status"];

        if ($status === 0) {
            return -1; //udah di cancel
        } else {
            $query = "UPDATE payments SET  payment_status = 3 WHERE id = $payment_id";
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
}
