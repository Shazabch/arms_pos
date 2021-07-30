{*
1/29/2021 12:45 PM William
- Enhanced to add Member no, Phone to search result table
*}

{include file=header.tpl}
{literal}
<style>
#result {
	width:100%;
	margin:0 auto;
	margin-top:1px;
	align: left;
}
</style>
{/literal}
<script>
var php_self = '{$smarty.server.PHP_SELF}';
{literal}
function fsubmit()
{
	var scan_code = document.f_a.member_no.value;
	if(scan_code == '') return false;

	document.getElementById("result").style.display = "";
	document.getElementById("result").innerHTML = "{/literal}{$LNG.LOADING_MSG}{literal}";
	
	document.f_a.a.value = "check_member";
	document.f_a.submit();
}

function check_member(event){
    if (event == undefined) event = window.event;
	if(event.keyCode==13){  // enter
		fsubmit();
	}else{
	    return false;
	}
}

</script>
{/literal}

<!-- Replacement Iten Popup -->
<!-- BreadCrumbs -->
<div class="breadcrumb-header justify-content-between mt-3 mb-2 ">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">{$LNG.MEMBER_ENQUIRY}</h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white ">
		<li class="breadcrumb-item">
			<a href="home.php">{$LNG.DASHBOARD}</a>
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
			<form name="f_a" method="post" onSubmit="return false;">
				<input type="hidden" name="a" />
				<div class="card-body">
					<div class="pd-10 pd-sm-20">
						<div class="row row-xs">
							<div class="col-md-4">
								<label>{$LNG.SCAN_OR_ENTER} {$LNG.MEMBER_NO}/{$LNG.MEMBER_NAME}/{$LNG.NRIC}/{$LNG.PHONE}</label>
							</div>
							<div class="col-md-5 mg-t-10 mg-md-t-0">
								<input class="form-control" name="member_no" value="{$form.member_no}" onKeyPress="check_member(event);">
							</div>
							<div class="col-md-2 mt-4 mt-xl-0">
								<input type="button" class="btn btn-main-primary btn-block" value="{$LNG.ENTER}" onclick="fsubmit();">
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
<!-- / Search form -->

	<div id="result">
	{if $member_list}
	<!--Table-->
	<div class="col-xl-12 ">
		<div class="card">
			<div class="card-body">
				<div class="table-responsive">
					<table class="table table-hover mb-0 text-md-nowrap">
						<thead>
							<tr>
								<th></th>
								<th>{$LNG.MEMBER_NO}/{$LNG.NRIC}/{$LNG.NAME}</th>
								<th>{$LNG.PHONE}</th>
							</tr>
						</thead>
						<tbody>
							{foreach from=$member_list item=r}
								<tr>
									<td>
										<a href="member_enquiry.php?a=get_member_info&nric={$r.nric}"><i class="fas fa-eye"></i></a>
									</td>
									<td>
										{if $r.card_no}
											{$r.card_no}<br>
										{/if}
										{if $r.nric}
											{$r.nric}<br>
										{/if}
										{if $r.name}
											{$r.name}
										{/if}
									</td>
									<td>{$r.phone_3}</td>
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
	</div>

<script>
	document.f_a.member_no.focus();
</script>
{include file="footer.tpl"}