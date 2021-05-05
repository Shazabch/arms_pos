{*
3/2/2011 2:21:48 PM Andy
- Change edit icon image.

10/4/2011 5:58:11 PM Justin
- Modified the "edit" icon to use gif instead of png.

11/03/2020 5:21PM Rayleen
- Modified page style/layout. 
	-Add h1/h3 in titles
	-Remove class small and added cellspacing and cellpadding in GRR list

11/04/2020 2:44PM Rayleen
- Modified page style/layout
	-Add Modules menu in breadcrumbs (Dashboard>Module) and link to module menu page
	-Put submenu name as title

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields

*}

{include file='header.tpl'}

<script>
{literal}
function check_form(){
	if(document.f_a['find_grr'].value==0) return false;
	
	return true;
}
{/literal}

</script>
<h1>
Open by GRR No.
</h1>
<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="home.php?a=menu&id={$module_name|lower}">{$module_name}</a>
<div style="margin-bottom:10px;"></div>

<div class="stdframe" style="background:#fff;">
<form name="f_a" method="post" onSubmit="return check_form();">
<p >
	GRR No.
	<input type="text" name="find_grr" class="txt-width-50" value="{$smarty.request.find_grr}" /> <input type="submit" class="btn btn-primary" value="Enter" />
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
</div>
<br>
{if $grr_list}
	<table width="100%" cellspacing="0" cellpadding="4" border="1">
	    <tr>
	        <th>&nbsp;</th>
	        <th>GRR No.</th>
	        <th>Vendor</th>
	    </tr>
	    {foreach from=$grr_list item=grr}
	        <tr>
	            <td width="20"><a href="{$smarty.server.PHP_SELF}?a=change_grr&id={$grr.id}&branch_id={$grr.branch_id}&find_grr={$smarty.request.find_grr}"><img src="/ui/ed.gif" border="0" title="Open" /></a></td>
	            <td>GRR#{$grr.id}</td>
	            <td>{$grr.vendor_code} - {$grr.vendor_desc}</td>
	        </tr>
	    {/foreach}
	</table>
{/if}

<script>
{literal}
document.f_a['find_grr'].select();
{/literal}
</script>
{include file='footer.tpl'}
