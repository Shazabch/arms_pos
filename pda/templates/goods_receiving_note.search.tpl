{*
3/2/2011 2:21:48 PM Andy
- Change edit icon image.

10/4/2011 4:46:32 PM Justin
- Modified the edit image to use gif instead of png extension.

11/25/2015 9:20 AM Qiu Ying
- PDA GRN can search GRR

04/11/2020 10:02AM Rayleen
- Modified page style/layout. 
	-Add h1/h3 in titles
	-Remove class small and added cellspacing and cellpadding in GRN list

11/04/2020 3:02PM Rayleen
- Modified page style/layout. 
	-Add Modules menu in breadcrumbs (Dashboard>SubMenu) and link to module menu page
	
11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields
*}

{include file='header.tpl'}

<script>
{literal}
function check_form(){
	if(document.f_a['find_grn'].value=="") return false;
	
	return true;
}
{/literal}
</script>

<h1>Open By GRN No. / GRR No. </h1>
<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="home.php?a=menu&id={$module_name|lower}">{$module_name}</a></span>
<div style="margin-bottom:10px;"></div>
<div class="stdframe" style="background:#fff">
<form name="f_a" method="post" onSubmit="return check_form();">
<p >
	GRN No. / GRR No.
	<input type="text" name="find_grn" class="txt-width-50" value="{$smarty.request.find_grn}" />
	<input type="submit" class="btn btn-primary" value="Enter" />
	<br /></p>
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

{if $grn_list}
	<table width="100%" border="1" cellspacing="0" cellpadding="4">
	    <tr>
	        <th>&nbsp;</th>
	        <th>GRN No.</th>
			<th>GRR No.</th>
	        <th>Vendor</th>
	    </tr>
	    {foreach from=$grn_list item=grn}
	        <tr>
	            <td width="20"><a href="{$smarty.server.PHP_SELF}?a=change_grn&id={$grn.id}&branch_id={$grn.branch_id}&find_grn={$smarty.request.find_grn}"><img src="/ui/ed.gif" border="0" title="Open" /></a></td>
	            <td>GRN#{$grn.id}</td>
				<td>GRR#{$grn.grr_id}</td>
	            <td>{$grn.vendor_code} - {$grn.vendor_desc}</td>
	        </tr>
	    {/foreach}
	</table>
{/if}
</div>
<script>
{literal}
document.f_a['find_grn'].select();
{/literal}
</script>
{include file='footer.tpl'}
