{*
11/28/2014 3:45 PM Andy
- Fix report data not showing at the first time when select the top level category.
*}

{literal}
<script>
function checkCategory(el){
	divc = $('div_cat');
	var obj = divc.getElementsByTagName('input');

	for(var i=0; i<obj.length; i++){
		if(obj[i].name=='multi_category_id[]'){
			obj[i].checked=el.checked;
		}
	}
}
</script>
{/literal}
<input type=hidden name=current_category_level value="{$level|default:1}">
<input type=hidden name=current_root_id value="{$root_id}">
{if $root_id}
<div>
<b>&lt;&lt; Back to <a href="javascript:load_cat('?a=load_cat&ajax=1&root_id={$root_id}&go_back=1')">{$root_category}</a></b>
</div>
{/if}
<div style="white-space:nowrap;padding-left:10px;"><input type=checkbox onChange="checkCategory(this)" name="all_cat" {if $smarty.request.all_cat} checked {/if}>
<b>All</b><br>
</div>
<div id=div_cat>
{foreach from=$category_list item=r}
<div style="white-space:nowrap;padding-left:10px;height:2em;width:220px;overflow:hidden;float:left;"><input type=checkbox name="multi_category_id[]" value="{$r.id}" {if $smarty.request.multi_category_id}{if in_array($r.id,$smarty.request.multi_category_id)}checked {/if}{/if} onChange="change_input_all('all_cat',this)">
<a href="javascript:load_cat('?a=load_cat&ajax=1&root_id={$r.id}')">{$r.description}</a>
</div>

{/foreach}
</div>
<br style="clear:left" />
