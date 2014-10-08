<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**************************************************************************************************
* ADMIN CMS INFOMATION
*==================================================================================================
* CREATE BY : hungnd88@appota.com
* TIME CREATE : 22/04/2014
* PROJECT BELONG TO :   DEALER-APPOTA-COM
* *************************************************************************************************
*/
class Admin extends CI_Controller {
    private $obj = array();
    private $method = "";
    public function __construct()
    {
        parent::__construct();
        $this->method = $this->router->fetch_class()."/".$this->router->fetch_method();
        $this->load->library("pagin_customize");
        $this->load->library("auth");
        $this->auth->permission($this->method);
        $this->load->model("common_model");

    }
    public function index()
    {
        die('No, action');
    }

    /**
     * LIST ALL ADMIN
     */
    public function manager_admin($submit = 'true', $username="none", $role="none", $status="none", $limit=10, $page=1)
    {
        # Khởi tạo tham số
        $data     = array();
        $start    = intval(($limit*($page-1)));
        $segement = 3;
        if ($submit == "false")
        {
            $username = trim($this->input->post("username"));
            if ($username == "") $username = "none";
            $role = trim($this->input->post("role"));
            if ($role == "") $role = "none";
            $status = trim($this->input->post("status"));
            if ($status == "") $status = "none";
        }
        $data["variables"] = array("username"=>$username, "role"=>$role, "status" => $status, "limit"=>$limit, "page"=>$page);
        $where = array();
        if ($role != "none") $where["role"] = $role;
        if ($username != "none") $where["username"] = new MongoRegex("/".urldecode($username)."/i");
        if ($status != "none") $where["status"] = $status;
        $conditions = array("where" => $where, "order_by" => array("_id" => "desc"), "limit" => array($start, $limit));

        # Lấy dữ liệu từ database và tạo phân trang
        $data["records"]   = $this->common_model->get_data(
            "admin", #->table
            array(), #->select
            $conditions, #->conditions
            false, #->is count records
            true #->multiple records
        );
        unset($conditions["limit"]);
        $data["total"]     = $this->common_model->get_data(
            "admin", #->table
            array(), #->select
            $conditions, #->conditions
            true, #->is count records
            true #->multiple records
        );
        $data['total_all'] = $this->common_model->get_data(
            "admin", #->table
            array(), #->select
            $conditions, #->conditions
            true, #->is count records
            true #->multiple records
        );
        $data["link"] = site_url().'backend/admin/manager_admin/true/'.$username."/".$role."/".$status."/".$limit;
        $data["pagin"]  = $this->pagin_customize->paging($page, $data["total"], $limit, $segement, $data["link"], false);
        $data["start"] = $start + 1;
        $header["admin"] = array('lv1'=>"manager_admin");

        #Hiển thị trên view
        $this->load->view('backend/header', $header);
        $this->load->view('backend/admin/manager_admin', $data, false);
        $this->load->view('backend/footer');
    }

    /**
     *  Create admin
     */
    public function add_admin()
    {
        $data = array();
        $data["controllers"] = $this->auth->listController();
        if (isset($_POST["submitx"]))
        {
            if (trim($this->input->post("username") == "")) die("Tên tài khoản không được để trống");
            $check = $this->common_model->get_data(
                "admin", #->table
                array(), #->select
                array("where" => array("username" => $this->input->post("username"))), #->conditions
                false, #->is count records
                false #->multiple records
            );
            if ($check) $data["error"] = "Tên tài khoản đã tồn tại";
            else
            {
                $salt = random_string("alnum", 5);
                $info_insert = array(
                    "username" => $this->input->post("username"),
                    "password" => md5($this->input->post("password").$salt),
                    "role" => $this->input->post("role"),
                    "status" => $this->input->post("status"),
                    "salt" => $salt,
                    "time_create" => time(),
                    "time_update" => time(),
                    "create_by" => $this->session->userdata("admin_name"),
                    "update_by" => ""
                );

                # update permission
                $permission = array();
                $controller_check = false;
                foreach ($data["controllers"] as $key => $value) {
                    if ($this->input->post($key))
                    {
                        foreach ($this->input->post($key) as $per) {
                            $permission[] = $per;
                            $controller_check = true;
                        }
                        if ($controller_check == true)
                        {
                            $permission[] = $key;    
                            $permission[] = $key."/index";    
                        } 
                    }
                }
                $info_insert["permissions"] = $permission;
                $res = $this->common_model->add_data(
                    "admin", #->table
                    $info_insert #->info insert
                );

                $action_log = "Thêm mới tài khoản admin & ID = ". (string) $res;
                $this->auth->save_log($this->method, $action_log, json_encode($info_insert, JSON_PRETTY_PRINT));

                if ($res) $data["success"] = "Thêm mới thành công";
                else $data["error"] = "Có lỗi xảy ra, vui lòng thử lại";
            }
        }
        $data["controllers"] = $this->auth->listController();
        $data['alias_group'] = $this->auth->alias_group_controlers();
        $header["admin"] = array('lv1'=>"manager_admin");
        #Hiển thị trên view
        $this->load->view('backend/header', $header);
        $this->load->view('backend/admin/add_admin', $data, false);
        $this->load->view('backend/footer');
    }

    /**
     *  Edit admin
     */
    public function edit_admin($id="")
    {
        $data = array();
        # Nếu không có quyền root hoặc admin thì không được thay đổi
        if (!in_array($this->session->userdata('role'), array("root", "admin"))) redirect("backend/dashboard");
        $data["controllers"] = $this->auth->listController();
        if (isset($_POST["submitx"]))
        {
            # Quyền root là cao nhất và chỉ duy nhất root được thay đổi cho chính mình

            $info_update = array(
                "role" => $this->input->post("role"),
                "status" => $this->input->post("status"),
                "time_update" => $this->input->post("time_update"),
                "update_by" => $this->session->userdata("admin_name")
            );
            if ($this->input->post("change_pass")) $info_update["password"] = md5($this->input->post("password").$this->input->post("salt"));
            # update permission
            $permission = array();
            foreach ($data["controllers"] as $key => $value) {
                $controller_check = false;
                if ($this->input->post($key))
                {
                    foreach ($this->input->post($key) as $per) {
                        $controller_check = true;
                        $permission[] = $per;
                    }
                }
                if ($controller_check == true)
                {
                    $permission[] = $key;    
                    $permission[] = $key."/index";    
                } 
            }
            $info_update["permissions"] = $permission;
            $res = $this->common_model->update_data(
                "admin", #->table
                array("where" => array("_id" => new MongoId($id))), #->conditions
                $info_update #->info update
            );

            $action_log = "Cập nhật tài khoản admin & ID = ". $id;
            $this->auth->save_log($this->method, $action_log, json_encode($info_update, JSON_PRETTY_PRINT));

            if ($res) $data["success"] = "Cập nhật thành công";
            else $data["error"] = "Cập nhật thất bại";
        }
        $data["record"] = $this->common_model->get_data(
            "admin", #->table
            array(), #->select
            array("where" => array("_id" => new MongoId($id))), #->conditions
            false, #->is count records
            false #->multiple records
        );
        
        if (!$data["record"]) die("Tài khoản không tồn tại");
        if ($data["record"]->role == "root" && $this->session->userdata('role')=="root" && $data["record"]->_id != new MongoId($id)) die("Không thể sửa quyền root của người khác");
        if ($data["record"]->role == "root" && $this->session->userdata('role') == "admin") die ("Quyền của bạn thấp hơn quyền của tài khoản cần sửa");
        $data["controllers"] = $this->auth->listController();
        $data['alias_group'] = $this->auth->alias_group_controlers();
        $header["admin"] = array('lv1'=>"manager_admin");

        #Hiển thị trên view
        $this->load->view('backend/header', $header);
        $this->load->view('backend/admin/edit_admin', $data, false);
        $this->load->view('backend/footer');
    }

    /**
     *  Remove record
     * @param  string $id id of admin
     * @return [type]     [description]
     */
    public function remove_admin($id="0")
    {
        if (!isset($_POST['selected'])) exit($this->simple->xreturn(false, 1, 'Bạn chưa chọn bản ghi nào', array()));
        $list_group = $_POST["selected"];
        if (count($list_group) > 0)
        {
            foreach ($list_group as $key => $value) {
                $this->common_model->remove_data(
                    'admin', #->table
                    array(
                        'where' => array('_id' => new MongoId($value))
                    ) #->conditions
                );
                $action_log = "Xóa tài khoản & ID = ". $value;
                $this->auth->save_log($this->method, $action_log, json_encode(array($value), JSON_PRETTY_PRINT));
            }
            exit ($this->simple->xreturn(true, 0, 'Xóa thành công '.count($list_group).' được chọn', array('list_group' => $list_group)));
        }
        else exit($this->simple->xreturn(false, 1, 'Bạn chưa chọn bản ghi nào', array()));
    }
    
    /**
     * [change_password description]
     * @return [type] [description]
     */
    public function change_password()
    {   
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
            $old_password = md5($this->input->post("old_password").$this->input->post("salt"));
            if ($old_password == $info_admin->password && $info_admin->salt == $this->input->post("salt"))
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
        $header["admin"] = array('lv1'=>"manager_admin");

        #Hiển thị trên view
        $this->load->view('backend/header', $header);
        $this->load->view('backend/admin/edit_admin', $data, false);
        $this->load->view('backend/footer');        
    }

    /**
     *  Log admin
     */
    public function manager_log($open_link="false", $admin_name="none", $method="none", $start_date="none", $end_date="none", $limit=10, $page=1)
    {
        # Khởi tạo tham số
        $data     = array();
        $start    = intval(($limit*($page-1)));
        $segement = 2;
        if ($open_link == "false")
        {
            $admin_name = strip_tags($this->input->post("admin_name"));
            if ($admin_name=="") $admin_name="none";
            $method = strip_tags($this->input->post("method"));
            if ($method == "") $method="none";
            $start_date = str_replace('/', '-',strip_tags($this->input->post("start_date")));
            if ($start_date=="") $start_date="none";
            $end_date = str_replace('/', '-',strip_tags($this->input->post("end_date")));
            if ($end_date=="") $end_date="none";
            if (strip_tags($this->input->post("limit")) !="") $limit = strip_tags($this->input->post("limit"));
        }
        $data["variables"] = array("admin_name"=>urldecode($admin_name), "method"=>urldecode($method), "start_date"=>$start_date, "end_date"=>$end_date,"limit"=>$limit, "page"=>$page);
        $where = array();
        if ($admin_name!="none") $where["admin_name"] = $admin_name;
        if ($method!="none") $where["method"] = $method;

        if ($start_date!="none")
        {
            $date = explode('-', $start_date);
            $time_start = strtotime($date[2].'/'.$date[1].'/'.$date[0]. ' 00:00:00');
        }
        if ($end_date!="none")
        {
            $date = explode('-', $end_date);
            $time_end = strtotime($date[2].'/'.$date[1].'/'.$date[0]. ' 23:59:59');
        }

        if ($admin_name!="none") $where["admin_name"] = new MongoRegex("/".$admin_name."/i");
        if ($method!="none") $where["method"] = new MongoRegex("/".$method."/i");
        if (isset($time_start) || isset($time_end))
        {
            if (isset($time_start) && isset($time_end)) $where["time"] = array('$gte'=>$time_start, '$lte'=>$time_end);
            else if(isset($time_start) && !isset($time_end)) $where["time"] = array('$gte'=>$time_start);
            else $where["time"] = array('$lte'=>$time_end);
        }
        $sort = array("_id"=>"desc");
    
        $conditions = array("where" => $where, "order_by" => $sort, "limit" => array($start, $limit));
    
        # Lấy dữ liệu từ database và tạo phân trang
        $data["records"]   = $this->common_model->get_data(
            "admin_logs", #->table
            array(), #->select
            $conditions, #->conditions
            false, #->is count records
            true #->multiple records
        );
        unset($conditions["limit"]);
        $data["total"] = $this->common_model->get_data(
            "admin_logs", #->table
            array(), #->select
            $conditions, #->conditions
            true, #->is count records
            true #->multiple records
        );
        $data['total_all'] = $this->common_model->get_data(
            "admin_logs", #->table
            array(), #->select
            array(), #->conditions
            true, #->is count records
            true #->multiple records
        );
    
        $data["link"] = site_url('backend/admin/manager_log/true/'.$admin_name."/".$method."/".$start_date."/".$end_date."/".$limit);
        $data["pagin"]  = $this->pagin_customize->paging($page, $data["total"], $limit, $segement, $data["link"], false);
        $data["start"] = $start + 1;
        $header["admin"] = array('lv1'=>"manager_log");
    
        #Hiển thị trên view
        $this->load->view('backend/header', $header);
        $this->load->view('backend/admin/manager_logs', $data, FALSE);
        $this->load->view('backend/footer');
    }

    public function edit_log($id="0")
    {
        if ($id == "0") redirect("backend/admin/manager_log");
        $data = array();
        $data["record"] = $this->common_model->get_data(
            "admin_logs", #->table
            array(), #->select
            array("where"=> array("_id"=> new MongoId($id))), #->conditions
            false, #->is count records
            false #->multiple records
        );
        $header["admin"] = array('lv1'=>"manager_log");
        #Hiển thị trên view
        $this->load->view('backend/header', $header);
        $this->load->view('backend/admin/edit_log', $data, FALSE);
        $this->load->view('backend/footer');
    }

    public function remove_log ($id="0")
    {
        if ($this->session->userdata('role') != "root") die("you have not this permission");
        if ($id!="0") $list_id = array($id);
        else $list_id = $_POST["selected"];
        if (count($list_id) > 0)
        {
            foreach ($list_id as $id)
            {
                if ($this->session->userdata('role') == "limit") die("Tài khoản này không có quyền xóa log");
                $this->common_model->remove_data(
                    "admin_logs", #->table
                    array("where" => array("_id" => new MongoId($id))) #->conditions
                );

                $action_log = "Xóa log admin & ID = ". $id;
                $this->auth->save_log($this->method, $action_log, json_encode(array($id), JSON_PRETTY_PRINT));
            }
            redirect("backend/admin/manager_log");
        }
        else
        {
            redirect("backend/admin/manager_log");
        }
    }
}

/* End of file admin.php */
/* Location: ./application/controllers/backend/admin.php */
?>