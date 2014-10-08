<?php
/**************************************************************************************************
* APPS INFOMATION
*==================================================================================================
* CREATE BY : hungnd88@appota.com
* TIME CREATE : 05/04/2014
* PROJECT BELONG TO :   DEALER-APPOTA-COM
* *************************************************************************************************
*/
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Common_model extends CI_Model {
    public function __construct()
    {
        parent::__construct();
        $this->db = $this->load->database("default", true);
    }

    /**************************************************************************************************
     * GET ALL DATA BY SEARCH
     */
    public function get_data($table, $select, $conditions, $count_data=false, $multiple_record=true, $is_test=false)
    {
        if(!$table) return false;
        $this->db->from($table);
        $this->db->select($select);
        if (!empty($conditions))
        {
            $array_keys = array_keys($conditions);
            if (in_array("sql", $array_keys))
            {
                $query = $this->db->query($conditions["sql"]);
                if ($query->num_rows > 0)
                {
                    if ($multiple_record == true) $data = $query->result(); else $data = $query->row();
                }
                else $data = false;
                return $data;
            }
            else
            {
                foreach ($conditions as $key => $condition) {
                    if ($key == 'distinct') foreach ($condition as $k => $value) {$this->db->distinct($value);}
                    if ($key == 'where') foreach ($condition as $k => $value) {$this->db->where($k, $value);}
                    if ($key == 'where_in') foreach ($condition as $k => $value) {$this->db->where_in($k, $value);}
                    if ($key == 'like') foreach ($condition as $k => $value) {$this->db->like($k, $value);}
                    if ($key == 'group_by') foreach ($condition as $k => $value) {$this->db->group_by($value);}
                    if ($key == 'order_by') foreach ($condition as $k => $value) {$this->db->order_by($k, $value);}
                    if ($key == 'limit') $this->db->limit($condition[1], $condition[0]);
                }
            }
        }
        if ($count_data == true)
        {
            $data = $this->db->count_all_results();
            if ($is_test == 1) {echo "<pre>"; var_dump($this->db->last_query()); echo "</pre>";}
            return $data;
        }
        else $query = $this->db->get();
        if ($is_test == 1) {echo "<pre>"; var_dump($this->db->last_query()); echo "</pre>";}
        if ($query->num_rows > 0)
        {
            if ($multiple_record == true) $data = $query->result(); else $data = $query->row();
        }
        else $data = false;
        return $data;
    }

    /**************************************************************************************************
     * ADD DATA
     */
    public function add_data($table = '', $data = array(),$is_test = false)
    {
        if ($table == '') return false;
        if (!empty($data)) $this->db->insert($table, $data);
        if ($is_test == 1) {echo "<pre>"; var_dump($this->db->last_query()); echo "</pre>";}
        return $this->db->insert_id();
    }

    public function update_data($table = '', $conditions = array(), $data = array(), $is_test = false)
    {
        if($table == '') return false;
        if (!empty($conditions))
        {
            $array_keys = array_keys($conditions);
            if (in_array("sql", $array_keys))
            {
                $query = $this->db->query($conditions["sql"]);
            }
            else 
            {
                if (!in_array("where", $array_keys)) return false;
                foreach ($conditions as $key => $condition) {
                    if ($key == 'where')
                    {
                        if (!is_array($condition) || empty($condition)) return false;
                        foreach ($condition as $k => $value) {$this->db->where($k, $value);}
                    }
                    if ($key == 'like') foreach ($condition as $k => $value) {$this->db->like($k, $value);}
                }
                $this->db->update($table, $data);
                if ($is_test == 1) {echo "<pre>"; var_dump($this->db->last_query()); echo "</pre>";}
            }
        }
        return true;
    }

    public function remove_data($table = '', $conditions = array(), $is_test = false)
    {
        if ($table == '') return false;
        if (!empty($conditions) && is_array($conditions))
        {
            $array_keys = array_keys($conditions);
            if (in_array("sql", $array_keys))
            {
                $query = $this->db->query($conditions["sql"]);
            }
            else
            {
                if (!in_array("where", $array_keys)) return false;
                foreach ($conditions as $key => $condition) {
                    if ($key == 'where')
                    {
                        if (!is_array($condition) || count($condition) == 0 || empty($condition))  return false;
                        $res = $this->check_null($condition);
                        if (!$res) return false;
                        foreach ($condition as $k => $value) {$this->db->where($k, $value);}
                    }
                    if ($key == 'like') foreach ($condition as $k => $value) {$this->db->like($k, $value);}
                }
                $this->db->delete($table);
            }
        }
        return true;
    }

    private function check_null($array)
    {
        foreach ($array as $key => $value) {
            if (is_null($array[$key])) {
                return false;
            }
        }
        return true;
    }

    public function x_encode_simple($input)
    {
        $input = $input.PRIVATE_KEY_ENDCODE;
        return strtr(base64_encode($input), '+/=', '-_,');
    }

    public function x_decode_simple($input)
    {
        $decode = base64_decode(strtr($input, '-_,', '+/='));
        return substr($decode, 0, strpos($decode, PRIVATE_KEY_ENDCODE));
    }
}

/* End of file common_model.php */
/* Location: ./application/models/common_model.php */
?>