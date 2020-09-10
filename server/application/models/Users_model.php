<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Users_model extends CI_Model
{
    public function get_user_role()
    {
        return $this->db->query("SELECT `id`, `role_id`, `role_name` FROM user_roles")->result_array();
    }

    public function login($user_login)
    {
        $email = $user_login['email'];
        $password = $user_login['password'];

        $data = $this->db->query("SELECT u.id, u.user_id, u.user_email, u.user_password, ur.role_id, u.user_firstname, u.user_lastname FROM users u JOIN user_roles ur ON u.user_role = ur.id WHERE user_email = '$email'")->row_array();
        if ($data === null) {
            $error['code'] = 404;
            $error['status'] = false;
            $error['message'] = 'Not user has found';
            return $error;
        } else {
            if (password_verify($password, $data['user_password'])) {
                unset($data['user_password']);
                // var_dump($data);
                // die;
                $user_data['status'] = true;
                $user_data['login_data'] = $data;
                return $user_data;
            } else {
                $error['code'] = 400;
                $error['status'] = false;
                $error['message'] = 'Wrong password';
                return $error;
            }
        }
    }

    public function register_user($user_data)
    {
        $first_name = $user_data['first_name'];
        $last_name = $user_data['last_name'];
        $email = $user_data['email'];
        $password = password_hash($user_data['password'], PASSWORD_DEFAULT);
        $role = $user_data['role'];

        $check_email = $this->db->query("SELECT 1 FROM users WHERE user_email = '$email'")->row_array();
        // var_dump($check_email);
        // die;

        if ($check_email !== null) {
            $error['code'] = 400;
            $error['message'] = 'e-mail has been used';
            return $error;
        } else {
            $role_id = $this->db->query("SELECT id FROM user_roles WHERE role_id = $role")->row_array()['id'];
            // var_dump($role_id);
            // die;
            $query_insert = "INSERT INTO users(`id`, `user_id`, `user_email`, `user_password`, `user_role`, `user_firstname`, `user_lastname`) VALUES (uuid_short(), user_id(), '$email', '$password', $role_id, '$first_name', '$last_name')";
            // var_dump($query_insert);
            // die;

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
    }

    public function get_user_details($user_id)
    {
        $user_details =  $this->db->query("SELECT * FROM vw_user_profile WHERE id = $user_id")->row_array();
        unset($user_details['id']);
        return $user_details;
    }

    public function update_user_detail($user_data)
    {
        $id = $user_data['id'];
        $first_name = $user_data['first_name'];
        $last_name = $user_data['last_name'];
        $gender = $user_data['gender'];
        $phone = $user_data['phone'];
        $address = $user_data['address'];
        $city = $user_data['city'];
        $zipcode = $user_data['zipcode'];
        $image = $user_data['image'];

        $check_id = $this->db->query("SELECT 1 FROM users WHERE id = $id")->row_array();

        if ($check_id) {
            $query_update = "UPDATE users SET user_firstname = '$first_name', user_lastname = '$last_name', user_gender = '$gender', user_phone = '$phone', user_address = '$address', user_city = '$city', user_zipcode = '$zipcode', user_image = '$image', modified_at = NOW() WHERE id = $id";

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
        } else {
            $error['code'] = 404;
            $error['message'] = 'user not found';
            return $error;
        }
    }

    public function update_password($user_data)
    {
        $id = $user_data['id'];
        $new_password = password_hash($user_data['new_password'], PASSWORD_DEFAULT);
        $old_password = $user_data['old_password'];

        $user = $this->db->query("SELECT user_password FROM users WHERE id = $id")->row_array();
        if ($user) {
            if (password_verify($old_password, $user['user_password'])) {
                $query_update = "UPDATE users SET user_password = '$new_password', modified_at = NOW() WHERE id = $id";
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
            } else {
                $error['code'] = 400;
                $error['status'] = false;
                $error['message'] = 'Wrong password';
                return $error;
            }
        } else {
            $error['code'] = 404;
            $error['message'] = 'user not found';
            return $error;
        }
    }
}
