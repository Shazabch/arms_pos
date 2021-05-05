<?php
function smarty_block_repeat($params, $content, &$smarty)
{
	$out = '';
	if (isset($params['s']))
	{
	    for ($i=$params['s'];$i<=$params['e']; $i++)
	        $out .= $content;
	        
    }
	else
		for ($i=0;$i<$params['n']; $i++)
	        $out .= $content;

    return $out;
}
?>
