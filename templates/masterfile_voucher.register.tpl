{*
4/21/2011 11:40:24 AM Alex
- add remark for no config auto-generated to insert 7 digits code only
6/15/2011 3:29:34 PM Alex
- fix voucher generate float value bugs

12/09/2015 4:00 PM Qiu Ying
- get voucher value which set by user

4/19/2017 10:04 AM Khausalya 
- Enhanced changes from RM to use config setting. 

06/29/2020 10:37 AM Sheila
- Updated button css.
*}

{include file=header.tpl}
{literal}
<style>
.value {
	
}

</style>
{/literal}
<script>
var phpself = '{$smarty.server.PHP_SELF}';
var bid = '{$sessioninfo.branch_id}';
{literal}

function check_form(){
	if (confirm("Are you sure you want to generate the voucher?")){
		return true;
	}else{
        return false;
	}
}

{/literal}
</script>

<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>


{if $err}
<div class="alert alert-danger mx-3 rounded">
	The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li><font color="red"> {$e} </font></li>
{/foreach}
</ul>
</div>
{/if}

{if $suc}
<div class="alert alert-danger mx-3 rounded">
	<ul class=err>
		{foreach from=$suc item=s}
		<li><font color="green"> {$s} </font></li>
		{/foreach}
		</ul>
</div>
{/if}

<p>
<form name="f_a" onsubmit='return check_form();'>
    <input type="hidden" name="id" id="id" value="">
	<input type="hidden" name="branch_id" id="branch_id" value="">

	<div class="card mx-3">
		<div class="card-body">
			<div class="stdframe">
				<table>
					<tr>
						<td width="150px"><b class="form-label">Assigned Branch</b></td>
						<td>
							<select class="form-control" name=branch_id>
								{foreach from=$branches item=branch}
								<option value="{$branch.id}" {if $branch.id eq $smarty.request.branch_id}selected{/if}>{$branch.code}</option>
								{/foreach}
							</select>
						</td>
					</tr>
					<tr>
						<td colspan=2>
							<table>
							<tr>
								<th class="form-label">Value ({$config.arms_currency.symbol})</th>
								<th>&nbsp;</th>
							</tr>
							{if $voucher}
								{foreach from=$voucher item=value}
								<tr>
									<td align="center" class="form-control value">{$value.voucher_value}</td>
									<td>
										<div class="form-inline">
											{assign var=vv value=$value.voucher_value|return_voucher_value}
										{if $config.voucher_auto_generate}
											No. of voucher <input class="form-control" type="text" name="no_code[{$vv}]" maxlength="3" size="3" onchange="this.value=int(this.value);" value="{$smarty.request.no_code.$vv}"> 
										{else}
											&nbsp;&nbsp;From&nbsp; <input type="text" class="form-control" name="from_code[{$vv}]"  maxlength="7" onchange="this.value=int(this.value);" value="{$smarty.request.from_code.$vv}" >
											&nbsp;&nbsp;To&nbsp; <input type="text" class="form-control" name="to_code[{$vv}]" maxlength="7" onchange="this.value=int(this.value);" value="{$smarty.request.to_code.$vv}">
										{/if}
										&nbsp&nbsp;(Maximum: {$config.voucher_generate_limit} pcs)
										</div>
									</td>
								</tr>
								{/foreach}
							{else}
								{foreach from=$config.voucher_value_prefix item=value}
								<tr>
									<td align="center" class="value form-control">{$value}</td>
									<td>
										{assign var=vv value=$value|return_voucher_value}
										{if $config.voucher_auto_generate}
											No. of voucher <input type="text" name="no_code[{$vv}]" maxlength="3" size="3" onchange="this.value=int(this.value);" value="{$smarty.request.no_code.$vv}"> 
										{else}
											From <input class="form-control" type="text" name="from_code[{$vv}]"  maxlength="7" onchange="this.value=int(this.value);" value="{$smarty.request.from_code.$vv}" >
											To <input class="form-control" type="text" name="to_code[{$vv}]" maxlength="7" onchange="this.value=int(this.value);" value="{$smarty.request.to_code.$vv}">
										{/if}
										(Maximum: {$config.voucher_generate_limit} pcs)
									</td>
								</tr>
								{/foreach}
							{/if}
							</table>
						</td>
					</tr>
			{*
					<tr>
						<td><b class="form-label">Interbranch</b></td>
						<td><input type="checkbox" name="interbranch" value=1 {if $smarty.request.interbranch} checked {/if} >(Allowed)</td>
					</tr>
			*}
				</table>
				{if !$config.voucher_auto_generate}
				<div class="alert alert-primary	 mt-3">
						<b>Noted:</b> Only 7 digits numberic is allow to be enter.
				</div>
				{/if}
				</div>
				<p>
					<button class="btn btn-primary mt-2" name=a value="register_form">Register</button>
				</p>
		</div>
	</div>
</form>
</p>

{include file=footer.tpl}

<script>
</script>
