<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**************************************************************************************************
* LIB CONTAINS FUNCTION REGULAR USES 
*==================================================================================================
* CREATE BY : hungnd88@appota.com
* TIME CREATE : 24/06/2014
* TIME UPDATE : 
* PROJECT BELONG TO :   APPOTA - PUSH
* *************************************************************************************************
*/
class Simple
{
    protected 	$ci;

	public function __construct()
	{
        $this->ci =& get_instance();
	}

    public function auth($type='')
    {
        if (!in_array($type, array('dev', 'admin'))) die ('Miss type in simple library');
        if ($type == 'dev')
        {
            if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1')
            {
                $session = array(
                    'user_id' => "1",
                    'username' => 'hungsama',
                    'status' => 'active',
                    'avatar' => base_url('public/img/dev-images/avatar-dev-default.png')
                );
            }
            else 
            {
                $this->ci->load->helper('cookie');
                $require = array('push_user_id', 'push_user_name', 'push_status', 'push_hash');
                foreach ($require as $key => $r) {
                    if (!get_cookie($r)) 
                    {
                        setcookie($r, NULL, 1, "/", "appota.com");
                        redirect('dev/error/error_session');
                    }

                }
                $this->ci->load->model("common_model");
                $count_user = $this->ci->common_model->get_data(
                    'apps', #->table
                    array(), #->select
                    array(
                        'where' => array(
                            'user_id' => get_cookie('push_user_id'),
                        )
                    ), #->conditions
                    true, #->is count records
                    true #->multiple records
                );
                if ($count_user == 0) header("Location: https://developer.appota.com/");
                $push_hash = md5(get_cookie('push_user_id').get_cookie('push_user_name')."t@o.l@.@ppot@.Push");
                if ($push_hash != get_cookie('push_hash')) die('Push hash is wrong !');
                $session = array(
                    'user_id' => get_cookie('push_user_id'),
                    'username' => get_cookie('push_user_name'),
                    'status' => get_cookie('push_status'),
                    'avatar' => base_url('public/img/dev-images/avatar-dev-default.png')
                );
            }
            $this->ci->session->set_userdata($session);
        }
    }

	/**
	 * [unix_time_of_start_or_end_date convert date to unix time with start date or end date]
	 * @param  [string]  $date     ['d-m-Y']
	 * @param  boolean $is_start [description]
	 * @return [integer]            [unix time]
	 */
	public function unix_time($date, $is_start= true)
    {
        $date = explode('-', $date);
        if ($is_start === false) $date = strtotime($date[2].'/'.$date[1].'/'.$date[0].' 23:59:59');
        else $date = strtotime($date[2].'/'.$date[1].'/'.$date[0].' 00:00:00');
        return $date;
    }	

    /**
     * [trans remove unicode in string]
     * @param  [string] $str [input string]
     * @return [string]      [string]
     */
    public function trans ($str){
        $unicode = array(
            'a'=>'á|à|ả|ã|ạ|ă|ắ|ặ|ằ|ẳ|ẵ|â|ấ|ầ|ẩ|ẫ|ậ',
            'd'=>'đ',
            'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'i'=>'í|ì|ỉ|ĩ|ị',
            'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'y'=>'ý|ỳ|ỷ|ỹ|ỵ',
            'A'=>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ặ|Ằ|Ẳ|Ẵ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'D'=>'Đ',
            'E'=>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'I'=>'Í|Ì|Ỉ|Ĩ|Ị',
            'O'=>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'U'=>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'Y'=>'Ý|Ỳ|Ỷ|Ỹ|Ỵ',
        );
        
       foreach($unicode as $nonUnicode=>$uni){
            $str = preg_replace("/($uni)/i", $nonUnicode, $str);
       }
        return $str;
    }

    /**
     * [generateRandomString - random string in string]
     * @param  integer $length [description]
     * @return [type]          [description]
     */
    public function generateRandomString($length = 10) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, strlen($characters) - 1)];
	    }
	    return $randomString;
	}

    public function xreturn($status=true, $error=0, $msg='Thành công',$data=array())
    {
        return json_encode(array(
            'status' => $status,
            'error_code' => $error,
            'msg' => $msg,
            'data' => $data
        ));
    }

    public function success_or_error($error = 0, $data)
    {
        if(count($data) == 0) return false;
        if ($error != 0)
        {
            $temp ='<div class="form-group">
                <div class="alert alert-danger alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>';
            foreach ($data as $key => $value) {
                $temp .= '<h4><i class="fa fa-times-circle"></i> '.$value.' </h4>';
            }
            $temp .='</div></div>';
        }
        else
        {
            $temp ='<div class="form-group">
                <div class="alert alert-success alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>';
            foreach ($data as $key => $value) {
                $temp .= '<h4><i class="fa fa-check-circle"></i> '.$value.' </h4>';
            }
            $temp .='</div></div>';
        }
        return $temp;
    }

    public function no_record()
    {
        return "<div class='alert alert-danger alert-dismissable'>
                <center><h4>Không tìm thấy bản ghi nào</h4></center>
            </div>";
    }

    public function _debug($data, $break = true)
    {
        echo "<pre>"; var_dump($data); echo "</pre>"; 
        if ($break === true) die('Time : '.date('H:i:s d-m-Y'));
    }

    public function sign_collection($user_id)
    {
        if (!$user_id) return false;
        $int = intval($user_id);
        if ($int > 0 && $int < 10) $collection = '0'.$int;
        else if ($int >= 10) $collection = substr((String) $int, -2);
        if (isset($collection)) return $collection; else return false;
    }
}

/* End of file simple.php */
/* Location: ./application/libraries/simple.php */

?>