<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    /**
     *  Create By : Hungnd88@appota.com
     *  Time Create : 25/04/2013
     *  Note : 
     */
    class Auth
    {
        protected $ci;
        protected $obj;
        public function Auth()
        {
            $this->ci =& get_instance();
            $this->ci->load->model("common_model");
            $info = $this->ci->common_model->get_data(
                "admin", #->table
                array(), #->select
                array("where" => array("_id" => $this->ci->session->userdata("admin_id"), "status" => "active")), #->conditions
                false, #->is count records
                false #->multiple records
            );
            if (!$info) redirect("backend/login");
            if (!in_array($info->role, array("root", "admin")) && empty($info->permissions)) redirect("backend/login");
            $this->obj = $info;
        }

        public function permission($method = "none")
        {
            if ($method !="none")
            {
                if (!in_array($this->obj->role, array("root", "admin")))
                {
                    if ($this->obj->role !="limit" || !in_array($method, $this->obj->permissions)) redirect("backend/error/error_403");
                }
            }
            else return $this->obj;
        }

        private $script = array(
            'script' => 'Lựa chọn tất cả',
            'script/push_appvn' => 'Gửi push cho hệ thống Appvn - iOS',
            'script/test_one_device' => 'Gửi push test cho Appvn - iOS'
        );

        private $app_dev = array(
            'dev_store' => 'Lựa chọn tất cả',
            'dev_store/manager_app' => 'Xem toàn bộ ứng dụng' 
        );

        private $dev_push = array(
            'dev_push' => 'Lựa chọn tất cả',
            'dev_push/manager_push' => 'Quản lý push',
            'dev_push/reinstall_crontab' => 'Danh sách crontab',
            'script/push_appvn' => 'Gửi push cho hệ thống Appvn - iOS',
            'script/test_one_device' => 'Gửi push test cho Appvn - iOS'
        );

        private $setting_advance = array(
            'config' => 'Lựa chọn tất cả',
            'config/manager_config' => 'Quản lý cài đặt',
            'config/delay_setting/' => 'Sửa một số cài đặt'
        );

        /**
         * [listController description]
         * @return [type] [description]
         */
        public function listController() {
            $this->controllers = array(
                'script' => $this->script,
                'dev_store' => $this->app_dev,
                'dev_push' => $this->dev_push,
                'config' => $this->setting_advance
            );
            return $this->controllers;
        }

        public function alias_group_controlers()
        {
            $this->group = array(
                'script' => 'Cài đặt push riêng AppVn',
                'dev_store' => 'Quản lý Ứng dụng',
                'dev_push' => 'Quản lý push',
                'config' => 'Cài đặt mở rộng'
            );
            return $this->group;
        }

        /**
         *  Save log of admin
         */
        public function save_log($method="", $reason="", $post=array(), $id_edit="")
        {
            $info_insert = array(
                "admin_id" => $this->ci->session->userdata("admin_id"),
                "admin_name" => $this->ci->session->userdata("admin_name"),
                "method" => $method,
                "action" => $reason,
                "id_edit" => $id_edit,
                "post" => $post,
                "time" => time()
            );
            $this->ci->common_model->add_data(
                "admin_logs", #->table
                $info_insert #->info insert
            );
        }
    }

    /* End of file test.php */
    /* Location: ./application/libraries/test.php */

?>