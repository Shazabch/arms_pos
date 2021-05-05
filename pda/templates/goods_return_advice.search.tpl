{*

04/11/2020 3:10PM Rayleen
- Modified page style/layout. 
	-Add h1/h3 in titles
	-Remove class small in table and added cellspacing and cellpadding
	-Add Modules menu in breadcrumbs (Dashboard>SubMenu) and link to module menu page

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields
*}
{include file='header.tpl'}

<script>
{literal}
function check_form(){
	if(document.f_a['gra_no'].value=='') return false;
	
	return true;
}
{/literal}
</script>
<h1>
Open By GRA NO. 
</h1>
<span class="breadcrumbs"> <a href="home.php">Dashboard</a> > <a href="home.php?a=menu&id={$module_name|lower}">{$module_name}</a></span>
<div style="margin-bottom:10px;"></div>
<div class="stdframe" style="background:#fff">
<form name="f_a" method="post" onSubmit="return check_form();">
<p >
	GRA No.
	<input type="text" name="gra_no" class="txt-width-50" onChange="mi(this);" value="{$smarty.request.gra_no}" />
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

{if $gra_list}
	<table width="100%" border="1" cellspacing="0" cellpadding="4">
	    <tr>
	        <th>&nbsp;</th>
	        <th>GRA No.</th>
	        <th>Vendor</th>
	    </tr>
	    {foreach from=$gra_list item=gra}
	        <tr>
	            <td width="20"><a href="{$smarty.server.PHP_SELF}?a=change_gra&id={$gra.id}&branch_id={$gra.branch_id}"><img src="/ui/ed.gif" border="0" title="Open" /></a></td>
	            <td>GRA#{$gra.id}</td>
	            <td>{$gra.vendor_code} - {$gra.vendor_desc}</td>
	        </tr>
	    {/foreach}
	</table>
{/if}
</div>
<script>
{literal}
document.f_a['gra_no'].select();
{/literal}
</script>
{include file='footer.tpl'}
