<?php

require_once "config.inc.php";
require_once "db_utils.inc";
require_once "db_". $config['db'] . ".inc";

define('INP_MODE', 'mode');
define('INP_START', 'start');


define('ERR_INVALID_REQUEST', '<html><body>Invalid request.
    Click <a href="?mode=s">here</a> to return to main page.</body></html>');
define('ERR_NO_KEY', '<html><body>Could not proceed. This form requires a key field that will uniquelly identify records in the table</body></html>');
define('MSG_UPDATED', "Record has been updated successfully.
    Click <a href=\"%s\">here</a> to return to main page.");
define('MSG_INSERTED', "Record has been added successfully.
    Click <a href=\"%s\">here</a> to return to main page.");
define('MSG_DELETED', "Record has been deleted successfully.
    Click <a href=\"%s\">here</a> to return to main page.");

$table = 'ciudades';
$scheme = '';
$fielddef = array(
    'f0' => array(FLD_ID => true, FLD_VISIBLE => true, FLD_DISPLAY => 'id', FLD_DISPLAY_SZ => 2,
        FLD_INPUT => true, FLD_INPUT_TYPE => 'text',
        FLD_INPUT_SZ => 2, FLD_INPUT_MAXLEN => 10, FLD_INPUT_DFLT => '',
        FLD_INPUT_NOTEMPTY => true, FLD_INPUT_VALIDATION => 'Numeric',
        FLD_DATABASE => 'id'),
    'f1' => array(FLD_ID => false, FLD_VISIBLE => true, FLD_DISPLAY => 'ciudad', FLD_DISPLAY_SZ => 20,
        FLD_INPUT => true, FLD_INPUT_TYPE => 'text',
        FLD_INPUT_SZ => 20, FLD_INPUT_MAXLEN => 20, FLD_INPUT_DFLT => '',
        FLD_INPUT_NOTEMPTY => true, FLD_INPUT_VALIDATION => '',
        FLD_DATABASE => 'ciudad'),
    'f2' => array(FLD_ID => false, FLD_VISIBLE => true, FLD_DISPLAY => 'pais', FLD_DISPLAY_SZ => 20,
        FLD_INPUT => true, FLD_INPUT_TYPE => 'text',
        FLD_INPUT_SZ => 20, FLD_INPUT_MAXLEN => 20, FLD_INPUT_DFLT => '',
        FLD_INPUT_NOTEMPTY => true, FLD_INPUT_VALIDATION => '',
        FLD_DATABASE => 'pais'),
    'f3' => array(FLD_ID => false, FLD_VISIBLE => true, FLD_DISPLAY => 'habitantes', FLD_DISPLAY_SZ => 10,
        FLD_INPUT => true, FLD_INPUT_TYPE => 'text',
        FLD_INPUT_SZ => 10, FLD_INPUT_MAXLEN => 10, FLD_INPUT_DFLT => '',
        FLD_INPUT_NOTEMPTY => true, FLD_INPUT_VALIDATION => 'Numeric',
        FLD_DATABASE => 'habitantes'),
    'f4' => array(FLD_ID => false, FLD_VISIBLE => true, FLD_DISPLAY => 'superficie', FLD_DISPLAY_SZ => 10,
        FLD_INPUT => true, FLD_INPUT_TYPE => 'text',
        FLD_INPUT_SZ => 10, FLD_INPUT_MAXLEN => 12, FLD_INPUT_DFLT => '',
        FLD_INPUT_NOTEMPTY => true, FLD_INPUT_VALIDATION => 'Numeric',
        FLD_DATABASE => 'superficie'),
    'f5' => array(FLD_ID => false, FLD_VISIBLE => true, FLD_DISPLAY => 'tieneMetro', FLD_DISPLAY_SZ => 7,
        FLD_INPUT => true, FLD_INPUT_TYPE => 'select',
        FLD_INPUT_SZ => 7, FLD_INPUT_MAXLEN => 5, FLD_INPUT_DFLT => '',
        FLD_INPUT_NOTEMPTY => true, FLD_INPUT_VALIDATION => '',
        FLD_DATABASE => 'tieneMetro')
);
$f5_values = array('0' => 'No tiene Metro', '1' => 'Tiene Metro ', '2' => 'Indiferente ');

$show_data = false;
$show_input = false;
$show_message = false;
$message = NULL;
$start = 0;
$fld_indices_notempty = NULL;
$fld_indices_EMail = NULL;
$fld_indices_Alpha = NULL;
$fld_indices_AlphaNum = NULL;
$fld_indices_Numeric = NULL;
$fld_indices_Float = NULL;
$fld_indices_Date = NULL;
$fld_indices_Time = NULL;

function get_href_back(&$arr, $mode, $start = -1) {
    $href='?';
    if (isset($arr['RLOC'])) {
        $href = $arr['RLOC'];
        if (!empty($href) && ($p = strpos($href, '?')) !== FALSE) {
            $href = substr($href, 0, $p + 1);
        } else
            $href .= '?';
    }
    $href .= "mode=$mode&start=$start";
    return $href;
}

if ($_SERVER['REQUEST_METHOD'] != "POST") {
    $mode = isset($_GET[INP_MODE]) ? $_GET[INP_MODE] : 's';
    if (($mode != 's') && ($mode != 'i') && ($mode != 'u')) {
        dbu_handle_error(ERR_INVALID_REQUEST);
    }
} else if (isset($_POST[INP_MODE])) {
    $mode = $_POST[INP_MODE];
    if (($mode != 'i2') && ($mode != 'u2')) {
        dbu_handle_error(ERR_INVALID_REQUEST);
    }
    if (isset($_POST[INP_START]) && is_numeric($_POST[INP_START]))
        $start = (int)$_POST[INP_START];
} else if (isset($_GET[INP_MODE])) {
    $mode = $_GET[INP_MODE];
    if (($mode != 's') && ($mode != 'i') && ($mode != 'u') && ($mode != 'd')) {
        dbu_handle_error(ERR_INVALID_REQUEST);
    }
    if (isset($_GET[INP_START]) && is_numeric($_GET[INP_START]))
        $start = (int)$_GET['start'];
} else {
    dbu_handle_error(ERR_INVALID_REQUEST);
}

$keys = dbu_get_keys($fielddef);
if (!$keys) {
    dbu_handle_error(ERR_NO_KEY);
}
$idx = 0;
$fld_indices_notempty = '';
foreach($fielddef as $fkey=>$fld) {
    if ($fld[FLD_INPUT]) {
        if ($fld[FLD_INPUT_NOTEMPTY]) {
            if (strlen($fld_indices_notempty) != 0) $fld_indices_notempty .= ', ';
            $fld_indices_notempty .= $idx;
        }
        if (!empty($fld[FLD_INPUT_VALIDATION])) {
            $name = "fld_indices_" . $fld[FLD_INPUT_VALIDATION];
            if (isset(${$name}) && strlen(${$name}) != 0) ${$name} .= ', '; else ${$name} = '';
            ${$name} .= $idx;
        }
    }
    $idx++;
}

$dbconn = dbu_factory($config['db']);
$dbconn->db_extension_installed();
$dbconn->db_connect($config['dbhost'], $config['dblogin'], $config['dbpass'], $config['dbname'], $config['dbport']);

switch ($mode) {
    case 's':
        $pager=array();
        $start = (isset($_GET[INP_START]) && is_numeric($_GET[INP_START])) ? (int)$_GET[INP_START] : 0;
        $rows = dbu_handle_select($fielddef, $scheme, $table, $dbconn, $keys, $start, $config['rows_per_page'], $config['pager_items'], $pager);
        if (!$rows && $dbconn->db_lasterror())
            dbu_handle_error($dbconn->db_lasterror());
        $show_data = true;
        break;
    case 'i':
        $row = dbu_get_input_defaults($fielddef);
        $nextmode = 'i2';
        $show_input = true;
        break;
    case 'i2':
        $rslt = dbu_handle_insert($fielddef, $scheme, $table, $dbconn, $_POST);
        if ($rslt) {
            $show_message = true;
            $href = get_href_back($_POST, 's', $start);
            $message = sprintf(MSG_INSERTED, $href);
        } else {
            dbu_handle_error($dbconn->db_lasterror());
        }
        $dbconn->db_close();
        break;
    case 'u':
        $row = dbu_fetch_by_key($fielddef, $scheme, $table, $dbconn, $_POST, $keys);
        $nextmode = 'u2';
        $show_input = true;
        break;
    case 'u2':
        $rslt = dbu_handle_update($fielddef, $scheme, $table, $dbconn, $_POST, $keys);
        if ($rslt) {
            $show_message = true;
            $href=get_href_back($_POST, 's', $start);
            $message = sprintf(MSG_UPDATED, $href);
        } else {
            dbu_handle_error($dbconn->db_lasterror());
        }
        $dbconn->db_close();
        $nextmode = 's';
        break;
    case 'd':
        $rslt = dbu_handle_delete($fielddef, $scheme, $table, $dbconn, $_POST, $keys);
        if ($rslt) {
            $show_message = true;
            $message = sprintf(MSG_DELETED, $start);
        } else {
            dbu_handle_error($dbconn->db_lasterror());
        }
        $dbconn->db_close();
        $nextmode = 's';
        break;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html><head>
<title>NuSphere DB Wizard generated form</title>
<meta http-equiv="Content-Type" content="text/html<?php echo isset($config['encoding']) ? "\"charset=\"{$config['encoding']}" : '';?>" />
<style type="text/css">
body                 { font-family: Tahoma,sans-serif,Verdana; font-size: 9pt;}
table.datatable      { background: #fcfcfc; }
table.datatable * td { padding: 0px 8px 0px 8px; margin: 0 8px 0 8px; }
tr.sublight          { background: #ededed; }
/*     table.datatable * tr { white-space: nowrap; } */
table.datatable * th { background: #ffffcc; text-align: center; }
</style>
<script  type="text/javascript">
<!--
function doslice(arg, idx) {
    var ret = Array();
    for (var i = idx; i < arg.length; i++) {
        ret.push(arg[i]);
    }
    return ret;
}

function Check(theForm, what, regexp, indices) {
    for (var i = 0; i < indices.length; i++) {
        var el = theForm.elements[indices[i]];
        if (el.value == "") continue;
        var avalue = el.value;
        if (!regexp.test(avalue)) {
            alert("Field is not a valid " + what);
            el.focus();
            return false;
        }
    }
    return true;
}

function CheckEmail(theForm) {
    var regexp = /^[0-9a-z\.\-_]+@[0-9a-z\-\_]+\..+$/i;    
    return Check(theForm, "email", regexp, doslice(arguments, 1));
}

function CheckAlpha(theForm) {
    var regexp = /^[a-z]*$/i;
    return Check(theForm, "alpha value", regexp, doslice(arguments, 1));
}

function CheckAlphaNum(theForm) {
    var regexp = /^[a-z0-9]*$/i;
    return Check(theForm, "alphanumeric value", regexp, doslice(arguments, 1));
}

function CheckNumeric(theForm) {
    for (var i = 1; i < arguments.length; i++) {
        var el = theForm.elements[arguments[i] - 1];
        if (el.value == "") continue;
        var avalue = parseInt(el.value);
        if (isNaN(avalue)) {
            alert("Field is not a valid integer number");
            el.focus();
            return false;
        }
    }
    return true;
}

function CheckFloat(theForm) {
    for (var i = 1; i < arguments.length; i++) {
        var el = theForm.elements[arguments[i]];
        if (el.value == "") continue;
        var avalue = parseFloat(el.value);
        if (isNaN(avalue)) {
            alert("Field is not a valid floating point number");
            el.focus();
            return false;
        }
    }
    return true;
}

function CheckDate(theForm) {
    for (var i = 1; i < arguments.length; i++) {
        var el = theForm.elements[arguments[i]];
        if (el.value == "") continue;
        var avalue = el.value;
        if (isNaN(Date.parse(avalue))) {
            alert("Field is not a valid date");
            el.focus();
            return false;
        }
    }
    return true;
}

function CheckTime(theForm) {
    var regexp = /^[0-9]+:[0-9]+:[0-9]+/i;    
    if (!Check(theForm, "time", regexp,  doslice(arguments, 1)))
        return false;                 
    for (var i = 1; i < arguments.length; i++) {
        var el = theForm.elements[arguments[i]];
        if (el.value == "") continue;
        var avalue = el.value;
        if (isNaN(Date.parse("1/1/1970 " + avalue))) {
            alert("Field is not a valid time");
            el.focus();
            return false;
        }
    }
    return true;
}

function CheckRequiredFields(theForm) {
    for (var i = 1; i < arguments.length; i++) {
        var el = theForm.elements[arguments[i]];
        if (el.value=="") {
            alert("This field may not be empty");
            el.focus();
            return false;
        }
    }
    return true;
}

function CheckForm(theForm) {
    return (
        CheckRequiredFields(theForm<?php echo empty($fld_indices_notempty) ? ", " . $fld_indices_notempty : "" ?>) &&
        CheckEmail(theForm<?php echo isset($fld_indices_EMail) ? ", " . $fld_indices_EMail : "" ?>) &&
        CheckAlpha(theForm<?php echo isset($fld_indices_Alpha) ? ", " . $fld_indices_Alpha : "" ?>) &&
        CheckAlphaNum(theForm<?php echo isset($fld_indices_AlphaNum) ? ", " . $fld_indices_AlphaNum : "" ?>) &&
        CheckNumeric(theForm<?php echo isset($fld_indices_Numeric) ? ", " . $fld_indices_Numeric : "" ?>) &&
        CheckFloat(theForm<?php echo isset($fld_indices_Float) ? ", " . $fld_indices_Float : "" ?>) &&
        CheckDate(theForm<?php echo isset($fld_indices_Date) ? ", " . $fld_indices_Date : "" ?>) &&
        CheckTime(theForm<?php echo isset($fld_indices_Time) ? ", " . $fld_indices_Time: "" ?>)
    )
}
//-->
</script>
</head>
<body>
<?php
    if ($show_message) {
?>
<table cellpadding="1" cellspacing="0" border="0" bgcolor="#ababab">
    <tr><td>
    <table cellpadding="0" cellspacing="1" border="0" bgcolor="#ffffff"><tr><td>
<?php echo $message; ?>
    </table>
    </td></tr>
</table>

<?php
    } else if ($show_input) {
?>
<form name="InputForm" method="post" enctype="multipart-form-data"
    onsubmit="return CheckForm(this)"
    action="">
    <table border="0">
        <?php  // INPUT
            foreach($fielddef as $fkey=>$fld) {
                if ($fld[FLD_INPUT]) {
                    echo "<tr>\n\t<td>$fld[FLD_DISPLAY]</td>\t";
                    $val = htmlentities($row[$fkey], ENT_QUOTES, $config['encoding']);
                    switch ($fld[FLD_INPUT_TYPE]) {
                        case "textarea":
                            echo "<td><textarea name=\"$fkey\" cols=\"$fld[FLD_INPUT_SZ]\" rows=\"15\">$val</textarea></td>\n</tr>";
                            break;
                        case "hidden":
                            echo "<td><input name=\"$fkey\" type=\"$fld[FLD_INPUT_TYPE]\" value=\"$val\" /></td>\n</tr>";
                            break;
                        case "select":
                            echo "<td>". WriteCombo(${$fkey . '_values'}, $fkey, "2") ."</td>\n</tr>";
                            break;
                        case "checkbox":
                            echo "<td><input name=\"$fkey\" type=\"$fld[FLD_INPUT_TYPE]\" size=\"$fld[FLD_INPUT_SZ]\" maxlength=\"$fld[FLD_INPUT_MAXLEN]\" " . ($val ? "checked=\"checked\"" : "") . " value=\"1\" /></td>\n</tr>";
                            break;
                        default:
                            echo "<td><input name=\"$fkey\" type=\"$fld[FLD_INPUT_TYPE]\" size=\"$fld[FLD_INPUT_SZ]\" maxlength=\"$fld[FLD_INPUT_MAXLEN]\" value=\"$val\" /></td>\n</tr>";
                    }
                }
            }
        ?>
        <tr>
            <td>&nbsp;</td>
            <td><input type="submit" value="Save" /></td>
        </tr>
    </table>
    <input type="hidden" name="RLOC" <?php echo "value=\"{$_GET['RLOC']}\""; ?> />
    <input type="hidden" name="start" <?php echo "value=\"{$_GET['start']}\"";?> />
    <input type="hidden" name="mode" <?php echo "value=\"$nextmode\"";?> />
    <?php // KEY
        if(isset($_POST[RKEY])) {
            $key = $_POST[RKEY];
            if (get_magic_quotes_gpc())
                $key = stripslashes($key);
            echo "<input type='hidden' name='".RKEY."' value='".htmlentities($key, ENT_QUOTES, $config['encoding'])."' />";
        }
    ?>
</form>
<?php
    } else if ($show_data) {
?>
<form name="ActionForm" method="post" action="">
<table cellpadding="1" cellspacing="0" border="0" bgcolor="#ababab">
    <tr><td>
    <table cellpadding="0" cellspacing="1" border="0" class="datatable">
        <tr><th style="width: 25px;"></th>
<?php
    // DATA HEADER
    foreach ($fielddef as $fkey=>$fld) {
        if ($fld[FLD_DISPLAY]) {
            $wd = isset($fld[FLD_DISPLAY_SZ]) ? " style=\"width: $fld[FLD_DISPLAY_SZ]ex\"" : "";
            echo "<th$wd>" . htmlentities($fld[FLD_DISPLAY], ENT_QUOTES, $config['encoding']) . "</th>";
        }
    }
?>
        </tr>
<?php  // DATA
    $checked = ' checked="checked"';
    $i = 0;
    foreach($rows as $row) {
        $bk = $i++ % 2 ? "" : ' class="sublight"';
        echo "<tr$bk><td><input type='radio'$checked name='".RKEY."' value='".htmlentities($row[RKEY], ENT_QUOTES, $config['encoding'])."' /></td>";
        foreach ($fielddef as $fkey=>$fld) {
            if ($fld[FLD_VISIBLE]) {
                $value =  htmlentities($row[$fkey], ENT_QUOTES, $config['encoding']);
                if (!isset($value))
                    $value = "&nbsp;";
                echo "<td>$value</td>";
            }
        }
        echo "</tr>\n";
        $checked = '';
    }
?>
    </table>
    </td></tr>
</table><br />
<?php // PAGER
    if (isset($pager[PG_PAGES])) {
        if (isset($pager[PG_PAGE_PREV])) {
            echo "<a href=\"?mode=s&amp;start=$pager[PG_PAGE_PREV]\">Prev</a>&nbsp;";
        } else {
            echo "Prev&nbsp;";
        }
        foreach($pager[PG_PAGES] as $pg => $st) {
            if ($st != $start) {
                echo "<a href=\"?mode=s&amp;start=$st\">$pg</a>&nbsp;";
            } else {
                echo "<b>$pg</b>&nbsp;";
            }
        }
        if (isset($pager[PG_PAGE_NEXT])) {
            echo "<a href=\"?mode=s&amp;start=$pager[PG_PAGE_NEXT]\">Next</a>&nbsp;";
        } else {
            echo "Next&nbsp;";
        }
        echo "<br />";
    }
?>
<br />
<table cellpadding="1" cellspacing="0" border="0" bgcolor="#ababab">
    <tr><td>
    <table cellpadding="1" cellspacing="0" border="0" bgcolor="#fcfcfc">
        <tr><td>
        <input type="button" value="insert" onclick="document.forms.ActionForm.action='?mode=i&amp;start=<?php echo $start; ?>&amp;RLOC=' + escape(window.location.href); document.forms.ActionForm.submit()" />&nbsp;
        <input type="button" value="update" onclick="document.forms.ActionForm.action='?mode=u&amp;start=<?php echo $start; ?>&amp;RLOC=' + escape(window.location.href); document.forms.ActionForm.submit()" />&nbsp;
        <input type="button" value="delete" onclick="document.forms.ActionForm.action='?mode=d&amp;start=<?php echo $start; ?>&amp;RLOC=' + escape(window.location.href); document.forms.ActionForm.submit()" />
        </td></tr>
    </table>
    </td></tr>
</table>
</form>
<?php } ?>
</body>
</html>