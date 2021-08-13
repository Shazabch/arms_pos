{*
10/18/2011 10:38:11 AM Justin
- Modified the form layout to fill under PDA screen.

10/18/2011 11:21:14 AM Alex
- change form same as backend form

1/24/2013 11:38 AM Fithri
- enhance to disable save/confirm buttons while user clicked on it

11/03/2020 5:15 PM Sheila
- Fixed title, table and form css

11/05/2020 11:49 AM Sheila
- Fixed breadcrumbs

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields
*}

{include file='header.tpl'}

<script>

var duration_valid = '{if $config.voucher_active_month_duration}{$config.voucher_active_month_duration}{else}3{/if}';

{literal}
function submit_form(){
	if(document.f_a['valid_from'].value==''){
		notify('error','{/literal}{$LNG.ENTER_DATE_START_ERR}{literal}','center')
		return false;
	}else if(document.f_a['valid_to'].value==''){
		notify('error','{/literal}{$LNG.ENTER_DATE_END_ERR}{literal}','center')
  		return false;
	}else if($.trim(document.f_a['codes'].value)==''){
		notify('error','{/literal}{$LNG.ENTER_CODE_ERR}{literal}','center')
  		return false;
	}
	document.f_a.submit_btn.disabled = true;
	document.f_a.submit();
}

function toggle_date_type(ele){
	var date_type=ele.value;

	if (date_type=='valid_duration'){
        $('#date_duration_id').show();
        $('#date_duration_id2').show();
		$('#inp_valid_duration').attr('disabled',false);

        $('#date_end_id').hide();
		$('#inp_valid_to').attr('disabled',true);
	}else{
        $('#date_duration_id').hide();
        $('#date_duration_id2').hide();
		$('#inp_valid_duration').attr('disabled',true);
		
		$('#date_end_id').show();
		$('#inp_valid_to').attr('disabled',false);
	}
}

function calculate_date_end(){
	
	var date_arr = $('#inp_valid_from').attr('value').split("-");
	var t = new Date(parseInt(date_arr[0]),(parseInt(date_arr[1])-1),parseInt(date_arr[2]));
	
	if ($('#rdo_end_id').val() == 'valid_to'){
		var duration=parseInt(duration_valid);
	}
	else{
		var duration=parseInt($('#inp_valid_duration').val());
	}

	t.setMonth(t.getMonth()+duration);

	var d = (t.getDate()).toString();
	var m = (t.getMonth()+1).toString();
	var y = t.getFullYear();

    if (d.length==1) d="0"+d;
    if (m.length==1) m="0"+m;

	$('#show_date_end').attr('value',y+'-'+m+'-'+d);
	$('#inp_valid_to').attr('value',y+'-'+m+'-'+d);
}

function toggle_all_check(obj){
	$("#branch_check_id .br_checkbox").each(function (index,ele){
		ele.checked=obj.checked;
	});
}
{/literal}
</script>

<!-- BreadCrumbs -->
<div class="breadcrumb-header justify-content-between mt-3 mb-2 ">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">{$LNG.VOUCHER_ACTIVATION}</h4>
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

{if $suc}
	<ul class=err>
		{foreach from=$suc item=s}
		<div class="alert alert-success mg-b-0 " role="alert">
			<button aria-label="Close" class="close" data-dismiss="alert" type="button">
				<span aria-hidden="true">&times;</span>
			</button>
				{$s}
		</div>
		{/foreach}
	</ul>
{/if}

<!-- row -->
<div class="row ">
	<div class="col-lg-12 col-md-12">
		<div class="card">
			<!-- Form -->
			<form name="f_a" method="post" onSubmit="return false;">
				<input type="hidden" name="a" value="activate_voucher" />
				<input type="hidden" name="branch_id" value="{$branch_id}" />
				<div class="card-body">
					<div class="pd-15 pd-sm-20">
						<div class="row row-xs mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.DATE_START}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<input class="form-control" type="text" id="inp_valid_from" name="valid_from" value="{$smarty.request.valid_from|default:$smarty.now|date_format:"%Y-%m-%d"}" onchange="calculate_date_end();" maxlength="10">
								<small class="help-block text-muted">(YYYY-MM-DD)</small>
							</div>
						</div>
						<div class="row row-xs mg-b-20">
							<div class="col-md-2">
								<select class="form-control w-75" name='rdo_end' id='rdo_end_id' onchange='calculate_date_end();toggle_date_type(this);'>
									<option value='valid_to'>{$LNG.DATE_END}</option>
			    					<option value='valid_duration'>{$LNG.DURATION}</option>
								</select>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<div id='date_end_id'>
									<input type="text" class="form-control" name="valid_to" value="{$smarty.request.valid_to|date_format:"%Y-%m-%d"}" id="inp_valid_to" maxlength="10" />
									<small class="help-block text-muted">(YYYY-MM-DD)</small>
								</div>
								<div id='date_duration_id'>
									<select class="form-control select2" name="valid_duration" id="inp_valid_duration" onchange="calculate_date_end();">
								        {section name=mon loop=13 start=1}
									        <option value="{$smarty.section.mon.index}">{$smarty.section.mon.index}</option>
					              		{/section}
								    </select>
								    {*
										<input type="text" name="valid_duration" value="{$smarty.request.valid_duration}" id="inp_valid_duration" size=12 />
									*}
								    <small>{$LNG.MONTHS}</small>
								</div>
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20" id="date_duration_id2">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.DATE_END}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<input class="form-control" type="text" id="show_date_end" readonly="1" size=12>
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.CODE}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<textarea class="form-control" name="codes" rows="10" cols="16">{$smarty.request.codes}</textarea>
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.ACTIVE_REMARK}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<select class="form-control select2" name="active_remark">
									{foreach from=$config.voucher_active_remark_prefix item=remark}
									    <option value="{$remark}" {if $smarty.request.active_remark eq $remark} selected {/if} >{$remark}</option>
									{/foreach}
								</select>
							</div>
						</div>
						<div class="row row-xs mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.INTER_BRANCH}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0" id="branch_check_id">
								<div class="checkbox mb-2">
									<div class="custom-checkbox custom-control">
										<input type="checkbox" class="custom-control-input ckbox" id="all_branch_id" onclick="toggle_all_check(this)">
										<label for="all_branch_id" class="custom-control-label mt-1">{$LNG.ALL}</label>
									</div>
								</div>
								{assign var=a value=$form.interbranch}
								
								{foreach from=$branches key=bid item=bcode}
									{if $bcode==$BRANCH_CODE}
									<div class="checkbox mb-2">
										<div class="custom-checkbox custom-control">
											<input type="checkbox" class="ckbox custom-control-input" id="checkbox-dis" checked disabled>
											<label for="checkbox-dis" class="custom-control-label mt-1">{$bcode}</label>
										</div>
									</div>
									{/if}
									<div class="checkbox mb-2" {if $bcode==$BRANCH_CODE} style="display:none;" {/if}>
										<div class="ckbox custom-checkbox custom-control">
											<input type="checkbox" {if $bcode==$BRANCH_CODE} style="display:none;" {else} class="custom-control-input br_checkbox"  {/if}  name="interbranch[{$bid}]" id="interbranch_{$bid}" value="{$bid}" {if $bcode==$BRANCH_CODE || $form.interbranch.$bid} checked {/if}>
											<label for="interbranch_{$bid}" class="custom-control-label mt-1">{$bcode}</label>
										</div>
									</div>
								{/foreach}
							</div>
						</div>
						<input type="button" class="btn btn-success btn-block-sm pd-x-30 mg-r-5 mg-t-5" name="submit_btn" value="{$LNG.ACTIVATE}" onclick="submit_form();">
					</div>
				</div>
			</form>
			<!-- / Form -->
		</div>
	</div>
</div>
<!-- /row -->
<script>
toggle_date_type($('#rdo_end_id'));
calculate_date_end();
</script>
{include file='footer.tpl'}
