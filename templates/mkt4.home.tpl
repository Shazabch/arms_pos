{include file=header.tpl}

<h1>{$PAGE_TITLE}</h1>

{literal}
<script>
function list_sel(n,s)
{
	var i;
	for(i=0;i<=1;i++)
	{
		if (i==n)
		    $('lst'+i).className='active';
		else
		    $('lst'+i).className='';
	}
	$('mkt_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	var pg = '';
	if (s!=undefined) pg = 's='+s;

	new Ajax.Updater('mkt_list', 'mkt4.php', {
		parameters: 'a=ajax_load_mkt_list&t='+n+'&'+pg+'&'+Form.serialize(document.f_m_h),
		evalScripts: true
		});
}

function do_refresh_branch(n,s){
	$('mkt_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	var pg = '';
	if (s!=undefined) pg = 's='+s;

	new Ajax.Updater('mkt_list', 'mkt4.php', {
		parameters: 'a=ajax_load_mkt_list&t='+n+'&'+pg+'&'+Form.serialize(document.f_m_h),
		evalScripts: true
		});
}
</script>
{/literal}
<form onsubmit="list_sel(0,find.value);return false;" name=f_m_h>
<input type=hidden name=branch_id value="{$branch_id|default:$smarty.request.branch_id}">
{if $sessioninfo.branch_id==1}
<table>
<tr>
<th><h3>Branch :</h3></th>
<td><select name="branch_id" onchange="do_refresh_branch(1);">
{foreach item="curr_Branch" from=$branches}
<option value={$curr_Branch.id} {if $curr_Branch.id==$branch_id or $smarty.request.branch_id==$curr_Branch.id}selected{/if}>{$curr_Branch.code}</option>
{/foreach}
</td>
</select>
</tr>
</table>
<br>
{/if}

<div class=tab style="height:25px;white-space:nowrap;">
&nbsp;&nbsp;&nbsp;
<a href="javascript:list_sel(1)" id=lst1 class=active>Saved Sales Target</a>
<a id=lst0>Find <input name=find> <input type=submit value="Go"></a>
</div>
</form>

<div id=mkt_list style="border:1px solid #000">
</div>
{include file=footer.tpl}

<script>
list_sel(1);
</script>
