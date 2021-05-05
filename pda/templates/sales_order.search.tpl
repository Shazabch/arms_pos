{*
3/2/2011 2:21:48 PM Andy
- Change edit icon image.

10/10/2011 4:46:32 PM Justin
- Modified the edit image to use gif instead of png extension.

04/11/2020 3:29PM Rayleen
- Modified page style/layout. 
	-Add h1 in titles and modified breadcrumbs (Dasboard>SubMenu) and link to module menu page
	-Remove class small in table and added cellspacing and cellpadding

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields
*}

{include file='header.tpl'}

<script>
{literal}
function check_form(){
	if(document.f_a['order_no'].value=='') return false;
	
	return true;
}
{/literal}
</script>

<h1>
Open By Order No
</h1>
<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="home.php?a=menu&id={$module_name|lower|replace:' ':'_'}">{$module_name}</a></span>
<div style="margin-bottom:10px;"></div>
<div class="stdframe" style="background:#fff">
<form name="f_a" method="post" onSubmit="return check_form();">
<p>
	DO No.
	<input type="text" name="order_no" class="txt-width-50" onChange="mi(this);" value="{$smarty.request.order_no}" />
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

{if $so_list}
	<table width="100%" border="1" cellspacing="0" cellpadding="4">
	    <tr>
	        <th>&nbsp;</th>
	        <th>Order No.</th>
	        <th>To</th>
	    </tr>
	    {foreach from=$so_list item=so}
	        <tr>
	            <td width="20"><a href="{$smarty.server.PHP_SELF}?a=change_so&id={$so.id}&branch_id={$so.branch_id}"><img src="/ui/ed.gif" border="0" title="Open" /></a></td>
	            <td>SO#{$so.id}</td>
	            <td>{$so.debtor_code} - {$so.debtor_desc}</td>
	        </tr>
	    {/foreach}
	</table>
{/if}
</div>
<script>
{literal}
document.f_a['order_no'].select();
{/literal}
</script>
{include file='footer.tpl'}
