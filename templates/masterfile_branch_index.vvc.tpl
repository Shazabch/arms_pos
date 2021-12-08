<!-- start payment vocher maintenance div -->

<div class="blur"><div class="shadow"><div class="content" >



<form method=post name=f_u target=_irs style="padding:10px;">
<div id=tmsg style="padding:10 0 10 0px;"></div>
<input type=hidden name=a value="vvc_keyin">
<input type=hidden name=branch_id id=branch_id value="">
<input type=hidden name=branch id=branch value="">
<h4 align=center class="form-label">Payment Voucher Code Maintenance<br>
({$branch_name})</h4>


<table id=tbl_vvc border=0 cellspacing=1 cellpadding=2>

{section name=n loop=$config.payment_voucher_no_banker}
{assign var=n value=$smarty.section.n.iteration-1}
<tr align=center><td colspan=2><hr noshade size=1></td></tr>

<!--tr>
<th align=left>Bank Code {$n}</th>
<td>
<input id=bank_code_{$bid}_{$n} name="vvc[bank_code][{$n}]" size=25 maxlength=1 onchange="uc(this);" value="{$vvc.bank_code.$n}">
</td>
</tr-->

<!--tr>
<th align=left>Bank Name {$n}</th>
<td>
<input id=bank_name_{$bid}_{$n} name="vvc[bank_name][{$n}]" size=25 onchange="uc(this);" value="{$vvc.bank_name.$n}">
</td>
</tr-->

<tr>
<th align=left class="form-label">Banker {$n+1}</th>
<td>
<select class="form-control" id=bank_code_{$bid}_{$n} name="vvc[bank_id][{$n}]">
{foreach key=key item=item from=$banker}
<option value={$item.id} {if $vvc.bank_id.$n eq $item.id} selected{/if}>{$item.description}</option>
{/foreach}
</select>
</td>
</tr>

<tr>
<th align=left class="form-label">Banker Code {$n+1}</th>
<td>
<input class="form-control" id=banker_code_{$bid}_{$n} name="vvc[banker_code][{$n}]" size=25 maxlength=8 onchange="uc(this);" value="{$vvc.banker_code.$n}">
</td>
</tr>
{/section}

</table>

<p align=center>
{if $sessioninfo.privilege.MST_VENDOR}
<input class="btn btn-primary mt-2" type=button value="Save" onclick="vvc_keyin();">
{/if}
<input class="btn btn-danger mt-2" type=button value="Close" onclick="f_u.reset(); hidediv('vvc_div');">
</p>

</form>
</div></div></div>
<!-- end of div -->
