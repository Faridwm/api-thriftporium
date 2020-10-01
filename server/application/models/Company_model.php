<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Company_model extends CI_Model
{
    public function get_company()
    {
        return $this->db->query("SELECT * FROM company LIMIT 1")->row_array();
    }

    public function update_company($company_data)
    {
        $id = $company_data["id"];
        $name = $company_data["name"];
        $street = $company_data["street"];
        $city = $company_data["city"];
        $province = $company_data["province"];
        $telp = $company_data["telp"];
        $fax = $company_data["fax"];
        $email = $company_data["email"];
        $instagram = $company_data["instagram"];
        $twitter = $company_data["twitter"];
        $website = $company_data["website"];

        $query_update = "UPDATE company SET company_name = '$name', company_street = '$street', company_city = '$city', company_province = '$province', company_telp = '$telp', company_fax = '$fax', company_email = '$email', company_instagram = '$instagram', company_twitter = '$twitter', company_website = '$website' WHERE id = $id";
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
