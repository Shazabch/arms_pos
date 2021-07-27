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
	if(document.f_a['do_no'].value=='') return false;
	
	return true;
}
{/literal}
</script>
<!-- BreadCrumbs -->
<div class="breadcrumb-header justify-content-between mt-3 mb-2 ">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">{$LNG.OPEN_DO_PICKING_VERIFICATION}</h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white ">
		<li class="breadcrumb-item">
			<a href="home.php">{$LNG.DASHBOARD}</a>
		</li>
		<li class="breadcrumb-item">
			<a href="home.php?a=menu&id=do">{$LNG.DO}</a>
		</li>
	</ol>
</nav>
<!-- /BreadCrumbs -->
<!-- Error Message -->
{if $err}
	{foreach from=$err item=e}
	<div class="alert alert-danger mg-b-0 " role="alert">
		<button aria-label="Close" class="close" data-dismiss="alert" type="button">
			<span aria-hidden="true">&times;</span>
		</button>
		{$e}
	</div>
    {/foreach}
{/if}
<!-- /Error Message -->

<!-- Search form -->
<div class="row mt-2 ">
	<div class="col-lg-12 col-md-12">
		<div class="card">
			<form name="f_a" method="post" onSubmit="return check_form();">
				<div class="card-body">
					<div class="pd-10 pd-sm-20">
						<div class="row row-xs">
							<div class="col-md-2">
								<label>{$LNG.DO_NO}</label>
							</div>
							<div class="col-md-5 mg-t-10 mg-md-t-0">
								<input class="form-control" type="text"  name="do_no" class="txt-width-50" value="{$smarty.request.do_no}">
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
<div class="col-xl-12 ">
	<div class="card">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-hover mb-0 text-md-nowrap">
					<thead>
						<tr>
							<th></th>
					        <th>{$LNG.DO_NO}</th>
					        <th>{$LNG.DELIVER_TO}</th>
					        <th>{$LNG.TYPE}</th>
						</tr>
					</thead>
					<tbody>
						{foreach from=$do_list item=do}
					        <tr>
					            <td>
									<a href="do.picking_verification.php?a=scan_item&id={$do.id}&branch_id={$do.branch_id}"><img src="/ui/ed.gif" title="{$LNG.OPEN}" /></a>
								</td>
					            <td>{$LNG.DO}#{$do.id}</td>
					            <td>
								{if $do.do_type eq 'open'}
								    {$do.open_info.name}
								{elseif $do.do_type eq 'credit_sales'}
								    {$LNG.DEBTOR}: {$do.debtor_code}
								{else}
									{if $do.deliver_branch}
										<font color="red">{$LNG.DO_CONSIST_OF_MULTI_BRANCHES}, <br />{$LNG.DISABLED_FOR_EDITING}</font>
									{else}
										{$do.do_branch_code}
									{/if}
								{/if}
								</td>
					            <td>
				                    {if $do.do_type eq 'open'}{$LNG.CASH_SALES}
									{elseif $do.do_type eq 'credit_sales'}{$LNG.CREDIT_SALES}
									{else}{$LNG.TRANSFER} {/if}
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
