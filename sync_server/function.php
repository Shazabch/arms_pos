<?php
/*
5/16/2016 4:10 PM Andy
- Enhanced to compatible with php7.
*/
function ms($str,$null_if_empty=0){
	if ($str == '' && $null_if_empty) return "null";
	settype($str, 'string');
	$str = str_replace("'", "''", $str);
	$str = str_replace("\\", "\\\\", $str);
	return "'" . (trim($str)) . "'";
}



function mi($intv,$null_if_empty=0){
	if ($intv == '' && $null_if_empty) return "null";
	$intv = str_replace(",","",$intv);
	settype($intv, 'int');
	return $intv;
}


function mf($floatv,$null_if_empty=0){
	if ($floatv == '' && $null_if_empty) return "null";
	$floatv = str_replace(",","",$floatv);
	settype($floatv, 'float');
	return $floatv;
}

function mysql_insert_by_field($arr, $fields = false, $null_if_empty=0)
{
	$ret = '';

	if (!is_array($fields))
	{
		$fields = array_keys($arr);
	}

	foreach ($fields as $f)
	{
		if (is_numeric($f)) continue;
		$newf[] = "`$f`";
		$v = $arr[$f];
		if ($ret != '') $ret .= ',';
		if (strstr($v, 'CURRENT_TIMESTAMP'))
		    $ret .= $v;
		else
			$ret .= ms($v,$null_if_empty);
	}

	$ret = '(' . join(",", $newf) . ') values (' . $ret . ')';
	return $ret;
}

function mysql_update_by_field($arr, $fields = false,$null_if_empty=0)
{

	$ret = '';

	if (!is_array($fields))
	{
		$fields = array_keys($arr);
	}

	foreach ($fields as $f)
	{
		if (is_numeric($f)) continue;
		$v = $arr[$f];
		if ($ret != '') $ret .= ', ';
		if (strstr($v, 'CURRENT_TIMESTAMP'))
		    $ret .= "`$f` = " . $v;
		else
			$ret .= "`$f` = " . ms($v,$null_if_empty);
	}

//	$ret = '(' . join(",", $fields) . ') values (' . $ret . ')';
	return $ret;
}
?>