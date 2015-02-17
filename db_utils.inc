<?php
define('FLD_DISPLAY', 'FLD_DISPLAY');
define('FLD_DATABASE', 'FLD_DATABASE');
define('FLD_INPUT', 'FLD_INPUT');
define('FLD_INPUT_TYPE', 'FLD_INPUT_TYPE');
define('FLD_INPUT_SZ', 'FLD_INPUT_SZ');
define('FLD_INPUT_MAXLEN', 'FLD_INPUT_MAXLEN');
define('FLD_INPUT_DFLT', 'FLD_INPUT_DFLT');
define('FLD_INPUT_NOTEMPTY', 'FLD_INPUT_NOTEMPTY');
define('FLD_INPUT_VALIDATION', 'FLD_INPUT_VALIDATION');
define('FLD_VISIBLE', 'FLD_VISIBLE');
define('FLD_DISPLAY_SZ', 'FLD_DISPLAY_SZ');
define('FLD_ID', 'FLD_ID');

define('PG_TOTAL_ROWS', 'PG_TOTAL_ROWS');
define('PG_TOTAL_PAGES', 'PG_TOTAL_PAGES');
define('PG_CUR_PAGE', 'PG_CUR_PAGE');
define('PG_PAGE_FIRST', 'PG_PAGE_FIRST');
define('PG_PAGE_LAST', 'PG_PAGE_LAST');
define('PG_PAGE_PREV', 'PG_PAGE_PREV');
define('PG_PAGE_NEXT', 'PG_PAGE_NEXT');
define('PG_PAGES', 'PG_PAGES');

define('RKEY', 'RKEY');

class dbconn {
	function db_extension_installed() {
	}
	function db_connect($dbhost, $dbuser, $dbpass, $dbname, $dbport) {
		return false;
	}
	function db_close() {
	}
	function db_lasterror() {
	}
	function db_query($sql) {
	}
	function db_query_limit($sql, $limit) {
		return $sql;
	}
	function db_query_seek($qry, $offset) {
		for ($i=1; $i < $offset; $i++) {
			if (!$this->dbnext($qry))
				return false;
		}
		return true;
	}
	function db_next($qry) {
		return false;
	}
	function db_quote_value($value) {
        return $value;
	}
	function db_quote_field($fld) {
        return $fld;
	}
	function db_quote_alias($alias) {
        return $alias;
	}
    function db_quote_table($scheme, $table) {
        return empty($scheme) ? '"' . $table. '"' : '"' . $scheme . '"."' . $table . '"';
    }
}

function dbu_factory($dbclass) {
	$dbclass .= '_dbconn';
	return new $dbclass;
}

function dbu_handle_error($msg) {
	$out = <<<EMSG
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head>
<title>ERROR</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head><body>
	  <h5>$msg</h5>
</body></html>
EMSG;
	echo $out;
	die();
}


/**
* @param array flddef
* @return array of keys or string in case of simple key
*/

function dbu_get_keys(&$flddef) {
	$keys = false;
	foreach ($flddef as $fkey=>$fld) {
		if ($fld[FLD_ID]) {
			if (is_array($keys)) {
				$keys[] = $fkey;
			} else if ($keys) {
				$keys = array($keys);
				$keys[] = $fkey;
			} else {
				$keys = $fkey;
			}
		}
	}
	return $keys;
}

function dbu_pack_key(&$row, &$keys) {
	if (is_array($keys)) {
		$val = array();
		foreach($keys as $subkey) {
			$val[$subkey] = $row[$subkey];
		}
		return serialize($val);
	} else {
		return $row[$keys];
	}
}

function dbu_extract_key(&$input, &$keys) {
	$key = $input[RKEY];
	if (get_magic_quotes_gpc()) {
		$key = stripslashes($key);
	}
	if (is_array($keys)) {
		$key = unserialize($key);
	} else {
		$key = array($keys => $key);
	}
	return $key;
}

/**
* @param array flddef
* @param string table
* @param dbconn dbconn
* @param array keys
* @param int start
* @param int row_count
* @param int pg_displ
* @param array pager
* @return dbconn
*/

function dbu_handle_select(&$flddef, $scheme, $table, &$dbconn, &$keys, $start, $row_page, $pg_displ, &$pager, $order_by="") {
	$rows = array();
	$fldlist = '';
	foreach($flddef as $fkey=>$fld) {
		if (!empty($fldlist)) $fldlist.=', ';
		$fldlist .= $dbconn->db_quote_field($fld[FLD_DATABASE]) . " as " . $dbconn->db_quote_alias($fkey);
	}
	$sql=sprintf("select count(*) as %s from %s ", $dbconn->db_quote_alias('cnt'), $dbconn->db_quote_table($scheme, $table));
	$rslt=$dbconn->db_query($sql);
	if (!($total_rows = $dbconn->db_next($rslt)))
		return false;
	$row_page = max($row_page, 1);
	$total_rows = (int)$total_rows['cnt'];
	$pager[PG_TOTAL_ROWS] = $total_rows;
	if ($start == -1) {
		$start = max(0, $total_rows - $row_page);
	}
	$pager[PG_TOTAL_PAGES] = (int) (($total_rows + $row_page - 1)  / $row_page);
	$pg_displ = min($pager[PG_TOTAL_PAGES], $pg_displ);
	$pager[PG_CUR_PAGE] = (int) ($start / $row_page);
	if ($pager[PG_CUR_PAGE] > $pager[PG_TOTAL_PAGES]) {
		$pager[PG_CUR_PAGE] = $pager[PG_TOTAL_PAGES];
		$start = $pager[PG_CUR_PAGE] * $row_page;
	}
	$pager[PG_PAGE_PREV] = ($start > $row_page) ? ($start - $row_page) : ($start > 0 ? 0 : NULL);
	$pager[PG_PAGE_NEXT] = ($start < $total_rows - $row_page) ? ($start + $row_page) : NULL;
	if ($pager[PG_CUR_PAGE] < $pg_displ / 2) {
		$pager[PG_PAGE_FIRST] = 0;
	} else if ($pager[PG_CUR_PAGE] > $pager[PG_TOTAL_PAGES] - $pg_displ / 2) {
		$pager[PG_PAGE_FIRST] = $pager[PG_TOTAL_PAGES] - $pg_displ;
	} else {
		$pager[PG_PAGE_FIRST] = $pager[PG_CUR_PAGE] - (int) ($pg_displ / 2);
	}
	$pager[PG_PAGE_LAST] = $pager[PG_PAGE_FIRST] + $pg_displ - 1;
	$pager[PG_PAGES] = array();
	for ($pg = $pager[PG_PAGE_FIRST]; $pg <= $pager[PG_PAGE_LAST]; $pg++) {
		$pager[PG_PAGES][$pg+1] = $pg * $row_page;
	}

    if ($order_by !="")
        $order_by = "ORDER BY ".$order_by;
	$sql=sprintf("select %s from %s %s", $fldlist, $dbconn->db_quote_table($scheme, $table), $order_by);
	$sql=$dbconn->db_query_limit($sql, $start+$row_page);
	$rslt=$dbconn->db_query($sql);
	if (!$rslt)
		return false;
	if ($dbconn->db_query_seek($rslt, $start)) {
		while (($row_page-- > 0) && ($row = $dbconn->db_next($rslt))) {
			$row[RKEY] = dbu_pack_key($row, $keys);
			$rows[] = $row;
		}
	}
	return $rows;
}

function dbu_get_input_defaults(&$flddef) {
	$row = array();
	foreach($flddef as $fkey=>$fld) {
		if ($fld[FLD_INPUT]) {
			$row[$fkey] = $fld[FLD_INPUT_DFLT];
		}
	}
	return $row;
}

/**
* @param array flddef
* @param string table
* @param dbconn dbconn
* @param array input
* @param array keys
*/

function dbu_fetch_by_key(&$flddef, $scheme, $table, &$dbconn, &$input, &$keys) {
	$fldlist = '';
	$wherelist = '';
	$key = dbu_extract_key($input, $keys);
	foreach($flddef as $fkey=>$fld) {
		if ($fld[FLD_INPUT] || $fld[FLD_ID]) {
			if (!empty($fldlist)) $fldlist.=', ';
			$fldlist .= $dbconn->db_quote_field($fld[FLD_DATABASE]) . " as " . $dbconn->db_quote_alias($fkey);
		}
		if ($fld[FLD_ID]) {
			if (!empty($wherelist)) $wherelist.=' and ';
			$wherelist .= $dbconn->db_quote_field($fld[FLD_DATABASE]) . "=" . $dbconn->db_quote_value($key[$fkey]);
		}
	}
	$sql=sprintf("select %s from %s where %s", $fldlist, $dbconn->db_quote_table($scheme, $table), $wherelist);
	$reslt=$dbconn->db_query($sql);
	if (!$reslt) return
		false;
	$row = $dbconn->db_next($reslt);
	return $row;
}


/**
* @param array flddef
* @param string table
* @param dbconn dbconn
* @param array input
*/

function dbu_handle_insert(&$flddef, $scheme, $table, &$dbconn, &$input) {
	$fldlist = '';
	$vallist = '';
	foreach($flddef as $fkey=>$fld) {
		if ($fld[FLD_INPUT]) {
			if (!empty($fldlist)) 
                $fldlist.=', ';
			$fldlist .= $dbconn->db_quote_field($fld[FLD_DATABASE]);
			if (!empty($vallist)) 
                $vallist.=', ';
			$val = $input[$fkey];
			if (get_magic_quotes_gpc())
				$val = stripslashes($val);
			$vallist .= $dbconn->db_quote_value($val);
		}
	}
	$sql = sprintf("insert into %s (%s) values (%s)",
				$dbconn->db_quote_table($scheme, $table),
				$fldlist,
				$vallist);
	return $dbconn->db_query($sql);
}


/**
* @param array flddef
* @param string table
* @param dbconn dbconn
* @param array input
* @param array keys
*/

function dbu_handle_update(&$flddef, $scheme, $table, &$dbconn, &$input, $keys) {
	$fldlist = '';
	$wherelist = '';
	$key = dbu_extract_key($input, $keys);
	foreach($flddef as $fkey=>$fld) {
		if ($fld[FLD_INPUT]) {
			if (!empty($fldlist)) $fldlist.=', ';
            if (!isset($input[$fkey]) && $flddef[$fkey][FLD_INPUT_TYPE] == 'checkbox')
                $val = 0;
            else
				$val = $input[$fkey];
			if (get_magic_quotes_gpc())
				$val = stripslashes($val);
			$fldlist .= $dbconn->db_quote_field($fld[FLD_DATABASE]) . " = " . $dbconn->db_quote_value($val);
		}
		if ($fld[FLD_ID]) {
			if (!empty($wherelist)) $wherelist.=' and ';
			$wherelist .= $dbconn->db_quote_field($fld[FLD_DATABASE]) . " = " . $dbconn->db_quote_value($key[$fkey]);
		}
	}
	$sql = sprintf("update %s set %s where %s",
				$dbconn->db_quote_table($scheme, $table),
				$fldlist,
				$wherelist);
	return $dbconn->db_query($sql);
}

/**
* @param array flddef
* @param string table
* @param dbconn dbconn
* @param array input
* @param array keys
*/

function dbu_handle_delete(&$flddef, $scheme, $table, &$dbconn, &$input, $keys) {
	$wherelist = '';
	$key = dbu_extract_key($input, $keys);
	foreach($flddef as $fkey=>$fld) {
		if ($fld[FLD_ID]) {
			if (!empty($wherelist)) $wherelist.=' and ';
			$wherelist .= $dbconn->db_quote_field($fld[FLD_DATABASE]) . " = " . $dbconn->db_quote_value($key[$fkey]);
		}
	}
	$sql = sprintf("delete from %s where %s",
				$dbconn->db_quote_table($scheme, $table),
				$wherelist);
	return $dbconn->db_query($sql);
}

function str2date($str){
	global $site;
	ereg('([0-9]{1,2})[./-]([0-9]{1,2})[./-]([0-9]{3,4})', $str, $arr);

	if($site['sdate']=='mdy')
		return "$arr[3]-$arr[1]-$arr[2]";
	if($site['sdate']=='dmy')
		return "$arr[3]-$arr[2]-$arr[1]";
	if($site['sdate']=='ymd')
		return "$arr[1]-$arr[2]-$arr[3]";
}

function date2Str($date){
	global $site;

	$d = split(' ', $date);
	$y = split('-', $d[0]);
	if($site['sdate']=='mdy')
		return "$y[1]$site[dsep]$y[2]$site[dsep]$y[0]";
	elseif($site['sdate']=='dmy')
		return "$y[2]$site[dsep]$y[1]$site[dsep]$y[0]";
	elseif($site['sdate']=='ymd')
		return "$y[0]$site[dsep]$y[1]$site[dsep]$y[2]";
}

function datetime2Str($date){
	global $site;

	$d = split(' ', $date);
	$y = split('-', $d[0]);
	if($site['sdate']=='mdy')
		return "$y[1]$site[dsep]$y[2]$site[dsep]$y[0] $d[1]";
	elseif($site['sdate']=='dmy')
		return "$y[2]$site[dsep]$y[1]$site[dsep]$y[0] $d[1]";
	elseif($site['sdate']=='ymd')
		return "$y[0]$site[dsep]$y[1]$site[dsep]$y[2] $d[1]";
}

function WriteCombo($vals, $name, $selected){
	$res = "<select name='$name'>\n";
	while(list($val, $opt)=each($vals)){
		$res .= "<option value='$val'". (($val==$selected)?' selected="selected"':''). ">$opt</option>\n";
	}
	$res .= "</select>\n";

	return $res;
}

function WriteComboChange($vals, $name, $selected, $primero){
    //document.forms.ActionForm.action == 's' ;
    //$mode='s';
    //echo "<input type=\"hidden\" name=\"INP_MODE\" value='s'>";
    $res = "<select name=\"$name\" method=\"POST\" onChange=document.forms.ActionForm.submit() >\n";
    //$res = "<select name=\"$name\" onChange=document.forms.ActionForm.submit() >\n";
    $res .= "<option value='0'>$primero</option>\n";
    while(list($val, $opt)=each($vals)){
        $res .= "<option value='$val'". (($val==$selected)?' selected="selected"':''). ">$opt</option>\n";
    }
    $res .= "</select>\n";
    return $res;
}


?>