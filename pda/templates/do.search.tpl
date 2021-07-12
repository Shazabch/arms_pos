{*
3/2/2011 2:21:48 PM Andy
- Change edit icon image.

10/11/2011 10:42:12 AM Justin
- Modified the edit image to use gif instead of png extension.

3/20/2014 2:41 PM Justin
- Enhanced to have access to DO Checklist.

5/22/2014 11:46 AM Justin
- Enhanced to remove the function that always change DO No into numberic.

8/3/2017 2:03 PM Justin
- Enhanced to disable user for editting Transfer DO that contains multiple delivery branch.

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields
*}

{include file='header.tpl'}

<script>
{literal}
function check_form(){
	if(document.f_a['do_no'].value=='')  return false;
	
	return true;
}
{/literal}
</script>
<!-- BreadCrumbs -->
<div class="breadcrumb-header justify-content-between mt-3 mb-2 animated fadeInDown">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">Open DO {if $is_checklist}Checklist{/if}</h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white animated fadeInDown">
		<li class="breadcrumb-item">
			<a href="home.php">Dashboard</a>
		</li>
		<li class="breadcrumb-item">
			<a href="home.php?a=menu&id=do">DO</a>
		</li>
	</ol>
</nav>
<!-- /BreadCrumbs -->

<!-- Error Message -->
{if $err}
	{foreach from=$err item=e}
	<div class="alert alert-danger mg-b-0 animated fadeInDown" role="alert">
		<button aria-label="Close" class="close" data-dismiss="alert" type="button">
			<span aria-hidden="true">&times;</span>
		</button>
		{$e}
	</div>
    {/foreach}
{/if}
<!-- /Error Message -->

<!-- Search form -->
<div class="row mt-2 animated fadeInDown">
	<div class="col-lg-12 col-md-12">
		<div class="card">
			<form name="f_a" method="post" onSubmit="return check_form();">
				<div class="card-body">
					<div class="pd-10 pd-sm-20">
						<div class="row row-xs">
							<div class="col-md-2">
								<label>DO No.</label>
							</div>
							<div class="col-md-5 mg-t-10 mg-md-t-0">
								<input class="form-control" type="text" name="do_no" class="txt-width-50" value="{$smarty.request.do_no}">
							</div>
							<div class="col-md-2 mt-4 mt-xl-0">
								<input type="submit" class="btn btn-main-primary btn-block" value="Enter">
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<!-- / Search form -->

{if $do_list}
<!--Table-->
<div class="col-xl-12 animated fadeInDown">
	<div class="card">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-hover mb-0 text-md-nowrap">
					<thead>
						<tr>
							<th></th>
							<th>DO No.</th>
							<th>Deliver To</th>
							<th>Type</th>
						</tr>
					</thead>
					<tbody>
						{foreach from=$do_list item=do}
							<tr>
		            			<td>
									{if $do.do_type eq 'transfer' && $do.deliver_branch}
										{* do nth because cannot let user to edit multiple branches DO *}
									{else}
										<a href="do.php?a={if $is_checklist}scan_checklist_item{else}change_do{/if}&id={$do.id}&branch_id={$do.branch_id}" data-toggle="tooltip" title="Open"><i class="fas fa-pen"></i></a>
									{/if}
								</td>
					            <td>DO#{$do.id}</td>
					            <td>
									{if $do.do_type eq 'open'}
									    {$do.open_info.name}
									{elseif $do.do_type eq 'credit_sales'}
									    Debtor: {$do.debtor_code}
									{else}
										{if $do.deliver_branch}
											<font color="red">DO consist of Multiple branches, <br />disabled for editing</font>
										{else}
											{$do.do_branch_code}
										{/if}
									{/if}
								</td>
					            <td>
				                    {if $do.do_type eq 'open'}Cash Sales
									{elseif $do.do_type eq 'credit_sales'}Credit Sales
									{else}Transfer {/if}
								</td>
					        </tr>
						{/foreach}
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<!-- /Table -->
{/if}
<script>
{literal}
document.f_a['do_no'].select();
{/literal}
</script>
{include file='footer.tpl'}
