<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Users_model extends CI_Model
{
    public function get_user_role()
    {
        return $this->db->query("SELECT `id`, `role_id`, `role_name` FROM user_roles")->result_array();
    }

    public function get_user_all()
    {
        return $this->db->query("SELECT * FROM vw_user_profile")->result_array();
    }

    public function get_user_by_id($id)
    {
        return $this->db->query("SELECT * FROM vw_user_profile WHERE id = $id")->result_array();
    }

    public function get_user_by_email($email)
    {
        return $this->db->query("SELECT * FROM vw_user_profile WHERE user_email = '$email'")->result_array();
    }

    public function get_user_by_role_id($role_id)
    {
        return $this->db->query("SELECT * FROM vw_user_profile WHERE role_id = $role_id")->result_array();
    }

    public function get_user_by_sosmed($uid, $provider)
    {
        return $this->db->query("SELECT * FROM vw_user_profile WHERE uid = '$uid' AND provider = '$provider'")->result_array();
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

    public function add_user($user_data)
    {
        $first_name = $user_data['first_name'];
        $last_name = $user_data['last_name'];
        $email = $user_data['email'];
        $password = $user_data['password'];
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

    public function add_user_with_sosmed($user_data)
    {
        $first_name = $user_data['first_name'];
        $last_name = $user_data['last_name'];
        $email = $user_data['email'];
        $uid = $user_data['uid'];
        $provider = $user_data['provider'];
        $role = $user_data['role'];

        if ($role !== 2001) {
            return -1;
        }

        $this->db->trans_begin();
        $role_id = $this->db->query("SELECT id FROM user_roles WHERE role_id = $role")->row_array()['id'];
        $check_provider = $this->db->query("SELECT 1 FROM sosmed_provider WHERE uid = '$uid' AND provider = '$provider'")->row_array();

        if ($check_provider === null) {
            $check_id = $this->db->query("SELECT id FROM users WHERE user_email = '$email'")->row_array();
            if ($check_id === null) {
                $uuid = $this->db->query("uuid_short()")->row_array()["uuid_short()"];
                $insert_users = "INSERT INTO users(id, user_id, user_email, user_role, user_firstname, user_lastname) VALUES ($uuid, user_id(), '$email', $role_id, '$first_name', '$last_name')";
                $insert_provider = "INSERT INTO sosmed_provider VALUES ('$uid', $uuid, '$provider', NOW())";

                if (!$this->db->simple_query($insert_users)) {
                    $error = $this->db->error();
                    $this->db->trans_rollback();
                    return $error;
                } else {
                    if (!$this->db->simple_query($insert_provider)) {
                        $error = $this->db->error();
                        $this->db->trans_rollback();
                        return $error;
                    }
                    $affected_row = $this->db->affected_rows();
                    $this->db->trans_commit();
                    return $affected_row;
                }
            } else {
                $user_uuid = $check_id["id"];
                $insert_provider = "INSERT INTO sosmed_provider VALUES ('$uid', $user_uuid, '$provider', NOW())";
                if (!$this->db->simple_query($insert_provider)) {
                    $error = $this->db->error();
                    $this->db->trans_rollback();
                    return $error;
                } else {
                    $affected_row = $this->db->affected_rows();
                    $this->db->trans_commit();
                    return $affected_row;
                }
            }
        } else {
            return -2;
        }
    }

    public function delete_user($user_id)
    {
        $query_delete = "UPDATE users SET user_status = 0 WHERE id = $user_id";
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
            $query_update = "UPDATE users SET user_firstname = '$first_name', user_lastname = '$last_name', user_gender = '$gender', user_phone = '$phone', user_address = '$address', user_city = $city, user_zipcode = '$zipcode', user_image = '$image', modified_at = NOW() WHERE id = $id";

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
        $new_password = $user_data['new_password'];
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
