{*
04/11/2020 3:21PM Rayleen
- Modified page style/layout. 
	-Add h1 in titles
	-Remove class small in table and added cellspacing and cellpadding
	-Add Modules menu in breadcrumbs (Dashboard>SubMenu) and link to module menu page

11/05/2020 11:48 AM Sheila
- Fixed breadcrumbs

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields

*}

{include file='header.tpl'}

<script>
{literal}
function check_form(){
	if(document.f_a['find_adjustment'].value=='') return false;
	
	return true;
}
{/literal}
</script>

<h1>Open By Adjustment</h1>

<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="home.php?a=menu&id={$module_name|lower}">{$module_name}</a></span>
<div style="margin-bottom: 10px"></div>

<div class="stdframe" style="background:#fff">
<form name="f_a" method="post" onSubmit="return check_form();">
<p>
	Adjustment No.
	<input type="text" name="find_adjustment" class="txt-width-50" onChange="mi(this);" value="{$smarty.request.find_adjustment}" />
	<input type="submit" class="btn btn-primary" value="Enter" />
	<br />
</p>
	<span style="color:red;">
	    {if $err}
	        <ul>
	        {foreach from=$err item=e}
	            <li>{$e}</li>
	        {/foreach}
	        </ul>
	    {/if}
	</span>
</form>

{if $adj_list}
	<table width="100%" border="1" cellspacing="0" cellpadding="4">
	    <tr>
	        <th>&nbsp;</th>
	        <th>Adj No.</th>
	        <th>Date</th>
	        <th>Adj Type</th>
	    </tr>
	    {foreach from=$adj_list item=adj}
	        <tr>
	            <td width="20"><a href="{$smarty.server.PHP_SELF}?a=change_adjustment&id={$adj.id}&branch_id={$adj.branch_id}&find_adjustment={$smarty.request.find_adjustment}"><img src="/ui/ed.gif" border="0" title="Open" /></a></td>
	            <td>{$adj.report_prefix}{$adj.id|string_format:"%05d"}</td>
	            <td>{$adj.adjustment_date}</td>
	            <td>{$adj.adjustment_type}</td>
	        </tr>
	    {/foreach}
	</table>
{/if}
</div>
<script>
{literal}
document.f_a['find_adjustment'].select();
{/literal}
</script>
{include file='footer.tpl'}
