<?php
defined('BASEPATH') or exit('No direct script access allowed');

class CategoryProduct_model extends CI_Model
{
    public function get_category_all()
    {
        return $this->db->query("SELECT id, category_id, category_name, category_description FROM product_categories")->result_array();
    }

    public function get_category_by_id($id)
    {
        return $this->db->query("SELECT id, category_id, category_name, category_description FROM product_categories WHERE id = $id")->row_array();
    }

    public function get_category_by_name($name)
    {
        return $this->db->query("SELECT id, category_id, category_name, category_description FROM product_categories WHERE category_name LIKE '%$name%'")->result_array();
    }

    public function add_product_category($category_data)
    {
        $name = $category_data['name'];
        $description = $category_data['description'];

        $query_insert = "INSERT INTO product_categories(id, category_id, category_name, category_description) VALUES (uuid_short(), cp_id(), '$name', '$description')";
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

    public function delete_product_category($id)
    {
        $query_delete = "DELETE FROM product_categories WHERE id = $id";
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

    public function update_product_category($category_data)
    {
        $id = $category_data['id'];
        $name = $category_data['name'];
        $description = $category_data['description'];

        $query_update = "UPDATE product_categories SET category_name = '$name', category_description = '$description' WHERE id = $id";
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
