<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {rss} plugin
 *
 * Type:     function<br>
 * Name:     rss<br>
 *

  usage:

  {RSS src="http://news.google.com.my/nwshp?tab=wn&topic=b&output=rss" assign="feed1"}
  <ol>
  {section name=i loop=$feed1.items}
  <li> {$feed1.items[i].title} {$feed1.items[i].description} ....
  {/section}
  </ol>
 */
function smarty_function_RSS($params, &$smarty)
{
    if (empty($params['src'])) {
        $smarty->_trigger_fatal_error("[plugin] parameter 'src' cannot be empty");
        return;
    }
    if (empty($params['assign'])) {
        $smarty->_trigger_fatal_error("[plugin] parameter 'assign' cannot be empty");
        return;
    }
	// Create lastRSS object
	$rss = new lastRSS($params['tags']);
	if (!empty($params['n'])) $rss->items_limit = intval($params['n']);

    // Set cache dir, cache interval and character encoding
    if (!is_dir("rss_cache"))
    {
    	mkdir("rss_cache",0777);
    	chmod("rss_cache",0777);
    }
	$rss->cache_dir = 'rss_cache';
	$rss->cache_time = 3600; // (1 hr)
        if (isset($params['type'])) $rss->feedtype = $params['type'];
	$content = $rss->Get($params['src']);

	$smarty->assign($params['assign'],$content);
}

/* vim: set expandtab: */

?>
