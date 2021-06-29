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
<div class="breadcrumb-header justify-content-between mt-3 mb-2">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">Search GRR No.</h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white">
		<li class="breadcrumb-item">
			<a href="home.php">Dashboard</a>
		</li>
		<li class="breadcrumb-item">
			<a href="home.php?a=menu&id={$module_name|lower}">{$module_name}</a>
		</li>
	</ol>
</nav>
{if $grr_list}
<!-- Form -->
<div class="row mt-2">
	<div class="col-lg-12 col-md-12">
		<div class="card">
			<form  name="f_a" method="post" onSubmit="return check_form();">
				<div class="card-body">
					<div class="pd-10 pd-sm-20">
						<div class="row row-xs">
							<div class="col-md-2">
								<label>GRR No</label>
							</div>
							<div class="col-md-5 mg-t-10 mg-md-t-0">
								<input class="form-control" type="text" name="find_grr" class="txt-width-50" value="{$smarty.request.find_grr}">
							</div>
							<input type="hidden" name="a" value="show_grr_list" />
							<div class="col-md-2 mt-4 mt-xl-0">
								<input type="submit" class="btn btn-main-primary btn-block" value="Enter">
							</div>
						</div>
					</div>
					<!-- Error Message -->
					{if $err}
						{foreach from=$err item=e}
						<div class="alert alert-danger mg-b-0" role="alert">
							<button aria-label="Close" class="close" data-dismiss="alert" type="button">
								<span aria-hidden="true">&times;</span>
							</button>
							{$e}
						</div>
					    {/foreach}
					{/if}
					<!-- /Error Message -->
				</div>
			</form>
		</div>
	</div>
</div>
<!-- / Form -->
	<button class="btn btn-indigo btn-rounded btn-sm mb-2" onclick="toggle_grr_list();">Show/Hide GRR List</button>
<!-- Table -->
<div class="col-xl-12 p-0" id="grr_list" {if !$smarty.request.find_grr}style="display:none;"{/if}>
	<div class="card">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-hover mb-0 text-md-nowrap">
					<thead>
						{if !$config.use_grn_future}
							{assign var=colspan value=2}
						{/if}
						<tr>
							<th>&nbsp;</th>
							<th>GRR No.</th>
							<th colspan="{$colspan}">Vendor</th>
						</tr>
					</thead>
					<tbody>
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
												<img src="../ui/lock.gif" border="0" title="GRR is being used">
											{elseif !$have_inv && !$have_do && !$have_oth}
												<img src="../ui/lock.gif" border="0" title="GRR does not contain Invoice, DO or Other">
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
								<tr>
									<td>
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
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<!-- /Table -->
{else}
<div class="d-flex justify-content-center align-items-center">
	<div class="card mg-b-20 text-center">
		<div class="card-body h-100">
			<img src="../../assets/img/svgicons/no-data.svg" alt="" class="wd-35p">
			<h5 class="mg-b-10 mg-t-15 tx-18"><img src="../ui/bananaman.gif"> Horray!</h5>
			<a href="#" class="text-muted">There are no GRR at the moment.</a>
		</div>
	</div>
</div>
{/if}
<script>
{if $grr_list}
{literal}
document.f_a['find_grr'].select();
{/literal}
{/if}
</script>
{include file='footer.tpl'}
