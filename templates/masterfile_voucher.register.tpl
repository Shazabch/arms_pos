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
	border:1px solid #aaa;
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

<h1>{$PAGE_TITLE}</h1>

{if $err}
The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li><font color="red"> {$e} </font></li>
{/foreach}
</ul>
{/if}

{if $suc}
<ul class=err>
{foreach from=$suc item=s}
<li><font color="green"> {$s} </font></li>
{/foreach}
</ul>
{/if}

<p>
<form name="f_a" onsubmit='return check_form();'>
    <input type="hidden" name="id" id="id" value="">
	<input type="hidden" name="branch_id" id="branch_id" value="">

	<div class="stdframe">
	<table>
		<tr>
	        <td width="150px"><b>Assigned Branch</b></td>
	        <td>
				<select name=branch_id>
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
				    <th>Value ({$config.arms_currency.symbol})</th>
				    <th>&nbsp;</th>
				</tr>
				{if $voucher}
					{foreach from=$voucher item=value}
					<tr>
						<td align="center" class="value">{$value.voucher_value}</td>
						<td>
							{assign var=vv value=$value.voucher_value|return_voucher_value}
							{if $config.voucher_auto_generate}
								No. of voucher <input type="text" name="no_code[{$vv}]" maxlength="3" size="3" onchange="this.value=int(this.value);" value="{$smarty.request.no_code.$vv}"> 
							{else}
								From <input type="text" name="from_code[{$vv}]"  maxlength="7" onchange="this.value=int(this.value);" value="{$smarty.request.from_code.$vv}" >
								To <input type="text" name="to_code[{$vv}]" maxlength="7" onchange="this.value=int(this.value);" value="{$smarty.request.to_code.$vv}">
							{/if}
							(Maximum: {$config.voucher_generate_limit} pcs)
						</td>
					</tr>
					{/foreach}
				{else}
					{foreach from=$config.voucher_value_prefix item=value}
					<tr>
						<td align="center" class="value">{$value}</td>
						<td>
							{assign var=vv value=$value|return_voucher_value}
							{if $config.voucher_auto_generate}
								No. of voucher <input type="text" name="no_code[{$vv}]" maxlength="3" size="3" onchange="this.value=int(this.value);" value="{$smarty.request.no_code.$vv}"> 
							{else}
								From <input type="text" name="from_code[{$vv}]"  maxlength="7" onchange="this.value=int(this.value);" value="{$smarty.request.from_code.$vv}" >
								To <input type="text" name="to_code[{$vv}]" maxlength="7" onchange="this.value=int(this.value);" value="{$smarty.request.to_code.$vv}">
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
		    <td><b>Interbranch</b></td>
		    <td><input type="checkbox" name="interbranch" value=1 {if $smarty.request.interbranch} checked {/if} >(Allowed)</td>
		</tr>
*}
	</table>
    {if !$config.voucher_auto_generate}<font color="red">*</font> <b>Noted:</b> Only 7 digits numberic is allow to be enter.{/if}
    </div>
    <p>
		<button class="btn btn-primary" name=a value="register_form">Register</button>
	</p>
</form>
</p>

{include file=footer.tpl}

<script>
</script>
