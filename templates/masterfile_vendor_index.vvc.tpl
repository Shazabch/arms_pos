<!-- start payment vocher maintenance div -->

<div class="blur"><div class="shadow"><div class="content" style="margin: 20px;">

<div class="small mt-2 mr-2" style="position:absolute; right:10; text-align:right;"><a href="javascript:void(hidediv('vvc_div'))" ><img src=ui/closewin.png border=0 align=absmiddle></a></div>

<form method=post name=f_u target=_irs>
<div id=tmsg style="padding:10 0 10 0px;"></div>
<input type=hidden name=a value="vvc_keyin">
<input type=hidden name=vendor_id id=vendor_id value="">
<input type=hidden name=vendor id=vendor value="">
<h4 align=center class="form-label">Payment Voucher Code Maintenance<br>
({$vvc.vendor})</h4>


<table id=tbl_vvc border=0 cellspacing=1 cellpadding=2>
<tr>
<td>&nbsp;</td>
{section name=b loop=$branches}
<th><h4 class="form-label">{$branches[b].code}</h4></th>
{/section}
</tr>

{section name=n loop=$config.payment_voucher_no_acct_code}
{assign var=n value=$smarty.section.n.iteration-1}
<tr>
<th align=left class="form-label">Acct Code {$n+1}</th>
{section name=b loop=$branches}
{assign var=bid value=$branches[b].id}
<td>
<input class="" id=acct_code_{$bid} name="acct_code[{$bid}][]" size=10 maxlength=8 value="{$vvc.acct_code.$bid.$n}" onchange="uc(this);" {if !$sessioninfo.privilege.MST_VENDOR}disabled{/if}>
</td>
{/section}
</tr>
{/section}

<!--
{section name=n loop=$config.payment_voucher_no_banker}
{assign var=n value=$smarty.section.n.iteration}
<tr><td colspan={count offset=1 var=$branches}><hr noshade size=1></td></tr>
<tr>
<th align=left>Bank Code {$n}</th>
{section name=b loop=$branches}
{assign var=bid value=$branches[b].id}
<td>
<input id=bank_code_{$bid}_{$n} name="bank_code[{$bid}][{$n}]" size=10 maxlength=1 onchange="uc(this);" value="{$vvc.bank_code.$bid.$n}">
</td>
{/section}
</tr>

<tr>
<th align=left>Bank Name {$n}</th>
{section name=b loop=$branches}
{assign var=bid value=$branches[b].id}
<td>
<input id=bank_name_{$bid}_{$n} name="bank_name[{$bid}][{$n}]" size=10 value="{$vvc.bank_name.$bid.$n}" onchange="uc(this);">
</td>
{/section}
</tr>

<tr>
<th align=left>Banker Code {$n}</th>
{section name=b loop=$branches}
{assign var=bid value=$branches[b].id}
<td>
<input id=banker_code_{$bid}_{$n} name="banker_code[{$bid}][{$n}]" size=10 maxlength=8 value="{$vvc.banker_code.$bid.$n}" onchange="uc(this);">
</td>
{/section}
</tr>

{/section}
-->
</table>

<p align=center>
{if $sessioninfo.privilege.MST_VENDOR}
<input type=button class="btn btn-primary mt-2 mb-2" value="Save" onclick="vvc_keyin();">
{/if}
<input type=button class="btn btn-danger mt-2 mb-2" value="Close" onclick="f_u.reset(); hidediv('vvc_div');">
</p>

</form>
</div></div></div>
<!-- end of div -->
