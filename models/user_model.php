<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Restful model
 *
 * @author Medunitsa Vladimir (medunitsa@outlook.com)
 * @version 1.0
 */

class user_model extends CI_Model {

    /**
     * Save data in database
     *
     * @param string $table Table name
     * @param array $data
     * @return bool
     */
    function save($table, $data) {
    	if($this->db->insert($table, $data)) {
    		return true;
    	} else {
    		return false;
    	}
    }

    /**
     * Check data in database
     *
     * @param string $table Table name
     * @param string $field Field name
     * @param string $value Field value
     * @return bool
     */
    function check($table, $field, $value) {
    	$query = $this->db->query("SELECT * FROM ".$table." WHERE ".$field." = '" . $value . "'");  
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;            
        }
    }

    /**
     * Check like
     *
     * @param string $table Table name
     * @param array $params
     * @return bool
     */
    function check_like($table, $params) {
        $query = $this->db->get_where($table, $params);
        if ($query->num_rows() > 0) {
            return true;
        } else {
            return false;            
        }
    }

    /**
     * Get data from database
     *
     * @param string $table Table name
     * @param array $params
     * @return bool or array $row
     */
    function get($table, $params) {    	
    	$query = $this->db->get_where($table, $params);
    	if ($query->num_rows() > 0) {
    		foreach ($query->result() as $row) {                
    			return $row;
    		}
    	}
    	else {
    		return false;
    	}
    }

    /**
     * Get all data from database
     *
     * @param string $table Table name
     * @param array $params 
     * @return bool or array $array
     */
    function get_all($table, $params, $array = null) {     
        $query = $this->db->get_where($table, $params);
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {                
                $array[] = $row;
            }

            return $array;
        }
        else {
            return false;
        }
    }

    /**
     * Update data in database
     *
     * @param string $table Table name
     * @param array $params
     * @param array $data
     * @return bool
     */
    function update($table, $params, $data) {    	
		$this->db->where($params);
		if($this->db->update($table, $data)) {
			return true;
		} else {
			return false;
		}
    }

    /**
     * Delete data from database
     *
     * @param string $table Table name
     * @param string $params
     * @return bool
     */
    function delete($table, $params) {
        if($this->db->delete($table, $params)) {
            return true;
        } else {
            return false;
        }
    }

    function get_to_end($table) {
        $to_end = time() - 300;
          
        $query = $this->db->query("SELECT `userID`, `taskID`, `desctiption`, `list` FROM ".$table." WHERE date >= ".$to_end." AND date =< " . time());
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $array[] = $row;
            }

            return $array;
        } else {
            return false;
        }

    }

    function get_finish($table) {
        $finish = time() + 300;
          
        $query = $this->db->query("SELECT `userID`, `taskID`, `desctiption`, `list` FROM ".$table." WHERE date >= ".time()." AND date =< " . $finish);
        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $array[] = $row;
            }

            return $array;
        } else {
            return false;
        }

    }

}