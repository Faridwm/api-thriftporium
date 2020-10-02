<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Product_model extends CI_Model
{
    public function get_product_all($status = null, $name = null, $category_name = null)
    {

        $string_query = "SELECT * FROM vw_products";

        if ($status !== null) {
            $string_query = $string_query . "  WHERE product_status = $status";

            if ($name) {
                $string_query =  $string_query . " AND product_name LIKE '%$name%'";
            }
            if ($category_name) {
                $string_query =  $string_query . " AND category_name LIKE '%$category_name%'";
            }
        } elseif ($name) {
            $string_query =  $string_query . " WHERE product_name LIKE '%$name%'";
            if ($category_name) {
                $string_query =  $string_query . " AND category_name LIKE '%$category_name%'";
            }
        } elseif ($category_name) {
            $string_query =  $string_query . " WHERE category_name LIKE '%$category_name%'";
        }

        // var_dump($string_query);
        // die;

        $product = $this->db->query($string_query)->result_array();
        if ($product) {
            for ($i = 0; $i < count($product); $i++) {
                $product[$i]['product_pictures'] = explode(", ", $product[$i]['product_pictures']);
            }
            return $product;
        } else {
            return false;
        }
    }

    public function get_product_by_id($id)
    {
        $product = $this->db->query("SELECT * FROM vw_products WHERE id = $id")->row_array();
        if ($product) {
            $product['product_pictures'] = explode(", ", $product['product_pictures']);
            return $product;
        } else {
            return false;
        }
    }

    public function get_product_by_name($name)
    {
        $product = $this->db->query("SELECT * FROM vw_products WHERE product_name LIKE '%$name%'")->result_array();
        if ($product) {
            for ($i = 0; $i < count($product); $i++) {
                $product[$i]['product_pictures'] = explode(", ", $product[$i]['product_pictures']);
            }
            return $product;
        } else {
            return false;
        }
    }

    public function get_product_by_category_name($category_name)
    {
        $product = $this->db->query("SELECT * FROM vw_products category_name LIKE '%$category_name%'")->result_array();
        if ($product) {
            for ($i = 0; $i < count($product); $i++) {
                $product[$i]['product_pictures'] = explode(", ", $product[$i]['product_pictures']);
            }
            return $product;
        } else {
            return false;
        }
    }

    public function add_product($product_data)
    {
        $name = $product_data['name'];
        $description = $product_data['description'];
        $stock = $product_data['stock'];
        $category_id = $product_data['category_id'];
        $price = $product_data['price'];
        $pictures = $product_data['pictures'];

        $this->db->trans_begin();

        $uuid = $this->db->query("SELECT uuid_short()")->row_array()["uuid_short()"];

        $query_insert = "INSERT INTO products(id, product_id, product_name, product_description, product_stock, product_avaibility, product_price, product_category) VALUES ($uuid, product_id(), '$name', '$description', $stock, $stock, $price, $category_id)";
        if (!$this->db->simple_query($query_insert)) {
            $error = $this->db->error();
            $this->db->trans_rollback();
            return $error;
        } else {
            for ($i = 0; $i < count($pictures); $i++) {
                $query_insert_picture = "INSERT INTO product_pictures(id, product_id, picture) VALUES(pp_id(), $uuid, '$pictures[$i]')";
                if (!$this->db->simple_query($query_insert_picture)) {
                    $error = $this->db->error();
                    $this->db->trans_rollback();
                    return $error;
                }
            }
            $affected_row = $this->db->affected_rows();
            $this->db->trans_commit();
            return $affected_row;
        }
    }

    public function delete_product($id_product)
    {
        $query_delete = "DELETE FROM products WHERE id = $id_product";
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

    public function update_product($product_data)
    {
        $id = $product_data['id'];
        $name = $product_data['name'];
        $description = $product_data['description'];
        $stock = $product_data['stock'];
        $avaibility = $product_data['avaibility'];
        $category_id = $product_data['category_id'];
        $price = $product_data['price'];
        $pictures = $product_data['pictures'];

        $this->db->trans_begin();
        $query_delete_picture = "DELETE FROM product_pictures WHERE product_id = $id";
        if (!$this->db->simple_query($query_delete_picture)) {
            $error = $this->db->error();
            $this->db->trans_rollback();
            return $error;
        } else {
            $query_update = "UPDATE products SET product_name = '$name', product_description = '$description', product_stock = '$stock', product_avaibility = $avaibility, product_price = '$price', product_category = $category_id, modified_at = NOW() WHERE id = $id";
            if (!$this->db->simple_query($query_update)) {
                $error = $this->db->error();
                $this->db->trans_rollback();
                return $error;
            } else {
                for ($i = 0; $i < count($pictures); $i++) {
                    $query_insert_picture = "INSERT INTO product_pictures(id, product_id, picture) VALUES(pp_id(), $id, '$pictures[$i]')";
                    if (!$this->db->simple_query($query_insert_picture)) {
                        $error = $this->db->error();
                        $this->db->trans_rollback();
                        return $error;
                    }
                }
                $affected_row = $this->db->affected_rows();
                $this->db->trans_commit();
                return $affected_row;
            }
        }
    }

    public function update_status_product($id, $new_status)
    {
        switch ($new_status) {
            case 'unpublish':
                $new_status = 0;
                break;
            case 'publish':
                $new_status = 1;
                break;
            case 'sold':
                $new_status = 2;
                break;
            default:
                return false;
                break;
        }
        $query = "UPDATE products SET product_status = $new_status WHERE id = $id";
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
