<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty default modifier plugin
 *
 * Type:     modifier<br>
 * Name:     default<br>
 * Purpose:  designate default value for empty variables
 * @link http://smarty.php.net/manual/en/language.modifier.default.php
 *          default (Smarty online manual)
 * @param string
 * @param string
 * @return string
 */
function smarty_function_get2ditem($params, &$smarty)
{
	$arr = $params['array'];
   // print ">>r=$params[r] c=$params[c]";
	if ($arr[$params[r]][$params[c]])
	{
		if (isset($params['retval']))
		{
			return $params['retval'];
		}
		else
		{
			return $arr[$params[r]][$params[c]];
		}
	}
   // return 1;
}

/* vim: set expandtab: */

?>

