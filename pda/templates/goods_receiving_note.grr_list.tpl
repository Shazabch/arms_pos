{*
3/2/2011 2:21:48 PM Andy
- Change edit icon image.

10/4/2011 5:58:11 PM Justin
- Modified the "lock" icon to use gif instead of png.
- Added show/hide link for GRR list (hide by default).

04/11/2020 10:21PM Rayleen
- Modified page style/layout. 
	-Add h1/h3 in titles
	-Remove class small and added cellspacing and cellpadding in GRN list

11/04/2020 2:59PM Rayleen
- Modified page style/layout. 
	-Add Modules menu in breadcrumbs (Dashboard>SubMenu) and link to module menu page

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields
*}

{include file='header.tpl'}

<script>
{literal}
function check_form(){
	if(document.f_a['grr_id'].value==0) return false;
	
	return true;
}

function toggle_grr_list(){

	if($('#grr_list').get(0).style.display == "none") $('#grr_list').get(0).style.display = "";
	else $('#grr_list').get(0).style.display = "none";
}
{/literal}
</script>
<h1>
Search GRR No. 
</h1>
<span class="breadcrumbs"><a href="home.php">Dashboard </a> > <a href="home.php?a=menu&id={$module_name|lower}">{$module_name}</a></span>
<div style="margin-top:10px;"></div>
<div class="stdframe" style="background:#fff">
{if $grr_list}
	<form name="f_a" method="post" onSubmit="return check_form();">
		<p >
			GRR No.
			<input type="text" name="find_grr" class="txt-width-50" value="{$smarty.request.find_grr}" />
			<input type="hidden" name="a" value="show_grr_list" />
			<input type="submit" class="btn btn-primary" value="Enter" />
			<br />
			<span style="color:red;">
				{if $err}
					<ul>
					{foreach from=$err item=e}
						<li>{$e}</li>
					{/foreach}
					</ul>
				{/if}
			</span>
		<br />
		<a onclick="toggle_grr_list();" style="cursor:pointer;">Show/Hide GRR List</a>
		</p>
	</form>
	<div id="grr_list" {if !$smarty.request.find_grr}style="display:none;"{/if}>
		<table width="100%" border="0" cellspacing="0" cellpadding="4">
			{if !$config.use_grn_future}
				{assign var=colspan value=2}
			{/if}
			<tr>
				<th>&nbsp;</th>
				<th>GRR No.</th>
				<th colspan="{$colspan}">Vendor</th>
			</tr>
			{foreach from=$grr_list item=grr}
				{if $grr_id ne $grr.grr_id}
					{assign var=grr_id value=$grr.grr_id}
					<tr {if !$config.use_grn_future}style="font-weight:bold;"{/if}>
						{if $config.use_grn_future}
							<td>
								{assign var=have_inv value=0}
								{assign var=have_do value=0}
								{assign var=have_oth value=0}
								{foreach from=$grr_list item=tmp_grr}
									{if $tmp_grr.grr_id eq $grr.grr_id}
										{if $tmp_grr.type eq 'INVOICE'}
											{assign var=have_inv value=1}
										{elseif $tmp_grr.type eq 'DO'}
											{assign var=have_do value=1}
										{elseif $tmp_grr.type eq 'OTHER'}
											{assign var=have_oth value=1}
										{/if}
									{/if}
								{/foreach}
								{if $grr.status}
									<img src="../ui/lock.gif" border="0" title="GRR is being used"></a>
								{elseif !$have_inv && !$have_do && !$have_oth}
									<img src="../ui/lock.gif" border="0" title="GRR does not contain Invoice, DO or Other"></a>
								{else}
									<a href="{$smarty.server.PHP_SELF}?a=change_grr&grr_id={$grr.grr_id}&grr_item_id={$grr.grr_item_id}&branch_id={$grr.branch_id}&find_grr={$smarty.request.find_grr}"><img src="../ui/add_form.gif" border="0" title="Create GRN for this GRR"></a>
								{/if}
							</td>
						{/if}
						<td colspan="{$colspan}">GRR#{$grr.grr_id}</td>
						<td colspan="{$colspan}">{$grr.vendor}</td>
					</tr>
				{/if}
				{if !$config.use_grn_future}
					<tr class="small">
						<td width=10>
							{if $grr.grn_used}
								<img src="../ui/lock.gif" border=0 title="GRR is used"></a>
							{else}
								<a href="{$smarty.server.PHP_SELF}?a=change_grr&grr_id={$grr.grr_id}&grr_item_id={$grr.grr_item_id}&branch_id={$grr.branch_id}&find_grr={$smarty.request.find_grr}"><img src="../ui/add_form.gif" border="0" title="Create GRN for this GRR"></a>
							{/if}
						</td>
						<td>{$grr.type}</td>
						<td>{$grr.doc_no}</td>
						<td>Remark: {$grr.remark|default:"-"}</td>
					</tr>
				{/if}
			{/foreach}
		</table>
	</div>
{else}
	<br /><i> <img src="../ui/bananaman.gif" align="absmiddle"> Horray! There are no GRR at the moment.</i>
{/if}
</div>
<script>
{if $grr_list}
{literal}
document.f_a['find_grr'].select();
{/literal}
{/if}
</script>
{include file='footer.tpl'}
