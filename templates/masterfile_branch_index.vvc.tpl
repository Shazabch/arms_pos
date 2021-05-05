<!-- start payment vocher maintenance div -->

<div class="blur"><div class="shadow"><div class="content">

<div class=small style="position:absolute; right:20; text-align:right;"><a href="javascript:void(hidediv('vvc_div'))" ><img src=ui/closewin.png border=0 align=absmiddle></a></div>

<form method=post name=f_u target=_irs>
<div id=tmsg style="padding:10 0 10 0px;"></div>
<input type=hidden name=a value="vvc_keyin">
<input type=hidden name=branch_id id=branch_id value="">
<input type=hidden name=branch id=branch value="">
<h4 align=center>Payment Voucher Code Maintenance<br>
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
<th align=left>Banker {$n+1}</th>
<td>
<select id=bank_code_{$bid}_{$n} name="vvc[bank_id][{$n}]">
{foreach key=key item=item from=$banker}
<option value={$item.id} {if $vvc.bank_id.$n eq $item.id} selected{/if}>{$item.description}</option>
{/foreach}
</select>
</td>
</tr>

<tr>
<th align=left>Banker Code {$n+1}</th>
<td>
<input id=banker_code_{$bid}_{$n} name="vvc[banker_code][{$n}]" size=25 maxlength=8 onchange="uc(this);" value="{$vvc.banker_code.$n}">
</td>
</tr>
{/section}

</table>

<p align=center>
{if $sessioninfo.privilege.MST_VENDOR}
<input type=button value="Save" onclick="vvc_keyin();">
{/if}
<input type=button value="Close" onclick="f_u.reset(); hidediv('vvc_div');">
</p>

</form>
</div></div></div>
<!-- end of div -->
