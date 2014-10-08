<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Login extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->method = $this->router->fetch_class()."/".$this->router->fetch_method();
        $this->load->model("common_model");
    }
    public function index()
    {
        $data = array();
        parse_str($_SERVER['QUERY_STRING'], $_GET);
        if (isset($_GET['back_url'])) $this->back_url = $_GET['back_url'];
        if(in_array($this->session->userdata('role'), array("root", "admin", "limit")))
        {
            if (count($this->session->userdata('permissions')) == 0)
            {
                $data['error'] = 'Tài khoản này chưa được phân quyền';
                $this->session->sess_destroy();
            }
            else
            {
                if(trim($this->back_url) != "") redirect($this->back_url);
                else redirect('backend/dashboard');
            }
        }
        if(isset($_POST['submit'])) {
            $username = $this->input->post('username');
            $password = $this->input->post('password');
            $result = $this->submit_login($username, $password);
            if($result->status) {
                $reason = "Đăng nhập hệ thống với username = ".$username." & ID = ". $this->session->userdata('admin_id');
                $post = json_encode(array('admin_name' => $username, '_id' => $this->session->userdata('admin_id')),JSON_PRETTY_PRINT);
                $info_insert = array(
                    "admin_id" => $this->session->userdata("admin_id"),
                    "admin_name" => $this->session->userdata("admin_name"),
                    "method" => $this->method,
                    "action" => $reason,
                    "post" => $post,
                    "time" => time()
                );
                $this->common_model->add_data(
                    'admin_logs', #->table
                    $info_insert #->info insert
                );
                unset($info_insert);

                if(isset($this->back_url) && trim($this->back_url) != "") redirect($this->back_url);
                else redirect('backend/dashboard');
                redirect('backend/dashboard');
            }
            else
            {
                $this->session->set_flashdata('error', $result->msg);
                $data["error"] = $result->msg;
            }
        }
        if(isset($data['back_url'])) $this->back_url;
        $this->load->view("backend/login", $data);
    }

    private function submit_login($username, $password)
    {
        if (!$username || !$password) return (object) array("status" => false, "msg" => "Tên đăng nhập hoặc mật khẩu không chính xác");
        $info_user = $this->common_model->get_data(
            "admin", #->table
            array(), #->select
            array("where" => array("username" => $username)), #->conditions
            false, #->is count records
            false #->multiple records
        );

        if (!$info_user) return (object) array("status" => false, "msg" => "Tên đăng nhập không chính xác");
        if ($info_user->status != "active") return (object) array("status" => false, "msg" => "Tài khoản này đang bị khóa");
        else {
            $xpassword = md5($password . $info_user->salt);
            if ($xpassword != $info_user->password) return (object) array("status" => false, "msg" => "Mật khẩu đăng nhập không chính xác");
            else
            {
                # Save ip, time
                $this->common_model->update_data(
                    "admin", #->table
                    array("where" => array("_id" => $info_user->_id)), #->conditions
                    array("last_login" => time(), "ip_address" => $this->input->ip_address()) #->info update
                );

                # Save session data
                $data = array(
                    "admin_id" => $info_user->_id,
                    "admin_name" => $info_user->username,
                    // "permissions" => $info_user->permissions,
                    "permissions" => array('dashboard/index'),
                    "base_url" => base_url(),
                    "role" => $info_user->role,
                    "status" => $info_user->status
                );
                $this->session->set_userdata($data);
                return (object) array("status" => true, "msg" => "Đăng nhập thành công");
            }
        }
    }

    /**
     *  Logout backend
     */
    public function logout()
    {
        $this->session->sess_destroy();
        redirect("backend/login");
    }

    /**
     * [change_password description]
     * @return [type] [description]
     */
    public function change_password()
    {   
        $this->load->library("auth");
        if (!$this->session->userdata('admin_id')) redirect("backend/login");
        $data = array();
        if (isset($_POST["submitx"]))
        {
            $info_admin = $this->common_model->get_data(
                "admin", #->table
                array(), #->select
                array("where" => array("_id" => $this->session->userdata('admin_id'))), #->conditions
                false, #->is count records
                false #->multiple records
            );
            if (!$info_admin) redirect("backend/login");
            $old_password = md5($this->input->post("old_password").$this->input->post("salt"));
            if ($old_password == $info_admin->password && $info_admin->salt == $this->input->post("salt"))
            {
                if ($this->input->post("password") != $this->input->post("repassword")) $data["error"] = "Mật khẩu mới và Mật khẩu gõ lại không khớp";
                else
                {
                    $info_update = array();
                    $info_update["password"] = md5($this->input->post("password").$this->input->post("salt"));
                    $res = $this->common_model->update_data(
                        "admin", #->table
                        array("where" => array("_id" => $this->session->userdata('admin_id'))), #->conditions
                        $info_update #->info update
                    );
                    if ($res) 
                    {
                        # Lưu log thay đổi
                        $action_log = "Tự đổi mật khẩu tài khoản có ID = ". (string) $this->session->userdata('admin_id'); 
                        $this->auth->save_log($this->method, $action_log, json_encode($info_update, JSON_PRETTY_PRINT));
                        $data["success"] = "Cập nhật thành công";
                    }
                    else $data["error"] = "Cập nhật thất bại";
                }                
            }
            else $data["error"] = "Mật khẩu cũ không đúng";
        }
        $data["record"] = $this->common_model->get_data(
            "admin", #->table
            array(), #->select
            array("where" => array("_id" => $this->session->userdata('admin_id'))), #->conditions
            false, #->is count records
            false #->multiple records
        );
        if (!$data["record"]) die("Tài khoản không tồn tại");
        $header["active"] = "add_admin";

        #Hiển thị trên view
        $this->load->view('backend/header', $header);
        $this->load->view('backend/admin/change_password', $data, false);
        $this->load->view('backend/footer');        
    }
}

/* End of file login.php */
/* Location: ./application/controllers/backend/login.php */
?>