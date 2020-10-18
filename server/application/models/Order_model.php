<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Order_model extends CI_Model
{
    public function get_order($order_id = null, $user = null, $order_num = null, int $status = null)
    {
        $query = "SELECT * FROM vw_order";
        if ($user === null && $status === null && $order_id === null && $order_num = null) {
            $query = $query;
        } elseif ($order_id && $user === null && $status === null && $order_num === null) {
            $query = $query . " WHERE id = $order_id";
        } else {
            if ($status !== null) {
                $query = $query . " WHERE order_status = $status";
                if ($user) {
                    $query = $query . " AND user_id = $user";
                }
                if ($order_num) {
                    $query = $query . " AND order_number LIKE '%$order_num%'";
                }
            } elseif ($user !== null) {
                $query = $query . " WHERE user_id = $user";
                if ($order_num) {
                    $query = $query . " AND order_number LIKE '%$order_num%'";
                }
            } elseif ($order_num === null) {
                $query = $query . " WHERE order_number LIKE '%$order_num%'";
            }
        }
        // var_dump($query);
        // die;

        $order = $this->db->query($query)->result_array();

        if ($order) {
            // $order["total_price"] = 0;
            for ($i = 0; $i < count($order); $i++) {
                $order[$i]["total_price"] = 0;
                $order[$i]["products"] = explode(", ", $order[$i]['products']);

                for ($j = 0; $j < count($order[$i]['products']); $j++) {
                    $p_id = $order[$i]['products'][$j];
                    $product_detail = $this->db->query("SELECT * FROM vw_product_picture WHERE product_id = $p_id")->row_array();
                    // var_dump($product_detail);
                    // die;
                    if ($product_detail) {
                        $order[$i]["total_price"] =  $order[$i]["total_price"] + $product_detail['product_price'];
                        $order[$i]['products'][$j] = $product_detail;
                    } else {
                        return false;
                    }
                }

                $order[$i]["total_price"] += $order[$i]["shipping_price"];
            }
            return $order;
        } else {
            return false;
        }
    }

    // public function get_order_all_active()
    // {
    //     return $this->db->query("SELECT * FROM vw_order WHERE order_status = 1")->result_array();
    // }

    // public function get_order_all_canceled()
    // {
    //     return $this->db->query("SELECT * FROM vw_order WHERE order_status = 0")->result_array();
    // }

    // public function get_order_by_order_id($order_id)
    // {
    //     return $this->db->query("SELECT * FROM vw_order WHERE id = $order_id")->row_array();
    // }

    // public function get_order_by_user_id_active($user_id)
    // {
    //     return $this->db->query("SELECT * FROM vw_order WHERE user_id = $user_id AND order_status = 1")->result_array();
    // }

    // public function get_order_by_user_id_done($user_id)
    // {
    //     return $this->db->query("SELECT * FROM vw_order WHERE user_id = $user_id AND order_status = 2")->result_array();
    // }

    public function make_order($order_data)
    {
        // var_dump($order_data);
        // die;
        $user = $order_data["user"];
        $street = $order_data["street"];
        $city = $order_data["city"];
        $zipcode = $order_data["zipcode"];
        $shipping_receiver = $order_data["shipping_receiver"];
        $shipping_phone = $order_data["shipping_phone"];
        $shipping_courier = $order_data["shipping_courier"];
        $shipping_price = $order_data["shipping_price"];
        $products = $order_data["products"];

        $transfer_to = $order_data["transfer_to"];
        $total_price = $order_data["total_price"];
        $account_bank = $order_data["account_bank"];
        $account_name = $order_data["account_name"];
        $account_number = $order_data["account_number"];

        $this->db->trans_begin();

        $order_uuid = $this->db->query("SELECT uuid_short()")->row_array()["uuid_short()"];
        $query_insert_order = "INSERT INTO orders(id, order_number, user_id, destination_receiver, destination_phone, destination_street, destination_city, destination_zipcode, shipping_courier, shipping_price) VALUES ($order_uuid, order_num(), $user, '$shipping_receiver', '$shipping_phone', '$street', $city, '$zipcode', $shipping_courier, $shipping_price)";
        if (!$this->db->simple_query($query_insert_order)) {
            $error = $this->db->error();
            $this->db->trans_rollback();
            return $error;
        } else {
            for ($i = 0; $i <  count($products); $i++) {
                $product_id = $products[$i]["product_id"];
                $product_qty = $products[$i]["qty"];

                // remove from cart if exists
                $remove_cart = "DELETE FROM carts WHERE user_id = $user AND product_id = $product_id";
                $this->db->simple_query($remove_cart);

                // check avaibility
                $avaibility = $this->db->query("SELECT check_avaibility($product_id, $product_qty) AS `check`")->row_array()["check"];
                if ($avaibility === 1) {
                    $query_insert_op = "INSERT INTO order_product VALUES ($order_uuid, $product_id, $product_qty)";
                    if (!$this->db->simple_query($query_insert_op)) {
                        $error = $this->db->error();
                        $this->db->trans_rollback();
                        return $error;
                        break;
                    } else {
                        continue;
                    }
                } else {
                    $this->db->trans_rollback();
                    $error = -1;
                    return $error;
                    break;
                }
            }

            $query_insert_payment = "INSERT INTO payments(id, inv_number, order_id, user_id, payment_transfer_to, total_price, payment_accountbank, payment_accountname, payment_accountnumber) VALUES (uuid_short(), inv_num(), $order_uuid, $user, $transfer_to, $total_price, '$account_bank', '$account_name', '$account_number')";
            if (!$this->db->simple_query($query_insert_payment)) {
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

    // public function to_payment_order($order_id)
    // {
    //     $status = $this->db->query("SELECT order_status FROM vw_order WHERE id = $order_id")->row_array()["order_status"];

    //     if ($status >= 2) {
    //         return "udah di pembayaran";
    //     } elseif ($status === 0) {
    //         return "udah di cancel";
    //     } else {
    //         $query_update = "UPDATE orders SET order_status = 2 WHERE id = $order_id";
    //         $this->db->trans_begin();
    //         if (!$this->db->simple_query($query_update)) {
    //             $error = $this->db->error();
    //             $this->db->trans_rollback();
    //             return $error;
    //         } else {
    //             $affected_row = $this->db->affected_rows();
    //             $this->db->trans_commit();
    //             return $affected_row;
    //         }
    //     }
    // }

    public function canceled_order($order_id)
    {
        $status = $this->db->query("SELECT order_status FROM vw_order WHERE id = $order_id")->row_array()["order_status"];

        if ($status >= 2) {
            return "gak bisa cancel";
        } elseif ($status === 0) {
            return "udah di cancel";
        } else {
            $query_update = "UPDATE orders SET order_status = 0 WHERE id = $order_id";
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
}
