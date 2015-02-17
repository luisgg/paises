<?php

require_once "db_utils.inc";

class mysql_dbconn extends dbconn {
	var $fconn;
	function db_extension_installed() {
		if (function_exists('mysql_connect')) {
				return(true);
		} else {
				dbu_handle_error("Please make sure MySQL extension is loaded in php.ini");
		}
	}
	function db_connect($dbhost, $dbuser, $dbpass, $dbname, $dbport) {
		if ($this->fconn) $this->close();
		$this->fconn = @mysql_connect(isset($dbport) ? $dbhost.':'.$dbport : $dbhost, $dbuser, $dbpass) or dbu_handle_error($this->db_lasterror());
		@mysql_select_db($dbname) or dbu_handle_error($this->db_lasterror());
		$this->db_query('set names utf8');
		$this->db_query('set character set utf8');
		$this->db_query('set character_set_connection=utf8');
	}
	function db_close() {
		if ($this->fconn) mysql_close($this->fconn);
		$this->fconn = NULL;
	}
	function db_lasterror() {
		return ($this->fconn) ? mysql_error($this->fconn) : mysql_error();
	}
	function db_query($sql) {
		return mysql_query($sql, $this->fconn);
	}
    function db_free_query($qry) {
        if ($qry) mysql_free_result($qry);
    }    
	function db_query_limit($sql, $limit) {
		return $sql . (empty($limit)? "" : " LIMIT $limit");
	}
	function db_query_seek($qry, $offset) {
		return @mysql_data_seek($qry, $offset);
	}
	function db_next($qry) {
		return @mysql_fetch_array($qry, MYSQL_ASSOC);
	}
	function db_quote_value($value) {
		if (!is_numeric($value)) {
			return "'" . mysql_real_escape_string($value, $this->fconn) . "'";
		} else {
			return $value;
		}
	}
	function db_quote_field($fld) {
		return '`' . $fld . '`';
	}
    function db_quote_table($scheme, $table) {
        return empty($scheme) ? '`' . $table. '`' : '`' . $scheme . '`.`' . $table . '`';
    }
    function db_last_inserted_id() {
        return mysql_insert_id($this->fconn);
    }
    function db_get_one_row($sql) {
        $qry = $this->db_query($sql);
        $rslt = $qry ? $this->db_next($qry) : NULL;
        $this->db_free_query($qry);
        return $rslt;
    }
    function db_get_all_rows($sql) {
        $qry = $this->db_query($sql);
        if ($qry) {
            $rslt = array();
            while ($v = $this->db_next($qry)) {
                array_push($rslt, $v);
            }
            $this->db_free_query($qry);
        } else 
            $rslt = FALSE;
        return $rslt;
    }
    function db_rows_affected() {
        return mysql_affected_rows($this->fconn);
    }
}
