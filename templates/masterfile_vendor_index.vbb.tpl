{*
2017-08-25 17:09 PM Qiu Ying
- Bug fixed on user still can tick the checkbox when they are not allow to edit the vendor details
*}

<!-- start payment vocher maintenance div -->

<div class="blur"><div class="shadow"><div class="content" style="margin: 20px;">

<div class=small style="position:absolute; right:10; text-align:right;"><a href="javascript:void(hidediv('vbb_div'))" ><img src=ui/closewin.png border=0 align=absmiddle></a></div>

<form method=post name=f_v target=_irs>
<div id=tmsg style="padding:10 0 10 0px;"></div>
<input type=hidden name=a value="vbb_keyin">
<input type=hidden name=vendor_id id=vendor_id value="">
<input type=hidden name=vendor id=vendor value="">
<h4 align=center class="form-label">Branch Block List<br>
({$vbb.vendor})</h4>


<table id=tbl_vvc border=0 cellspacing=1 cellpadding=2>
<tr>
<td>&nbsp;</td>
{section name=b loop=$branches}
<th width="50"><h4 class="form-label">{$branches[b].code}</h4></th>
{/section}
</tr>

{foreach from=$type key=tid item=tp}
	<tr>
		<th align=left class="form-label">{$tp}</th>
		{section name=b loop=$branches}
			{assign var=bid value=$branches[b].id}
			<td align='center'>
				<input type="checkbox" name="block[{$tid}][{$bid}]" value="on" {if $block.$tid.$bid}checked {/if} {if !$sessioninfo.privilege.MST_VENDOR}disabled{/if}>
			</td>
		{/section}
	</tr>
{/foreach}



</table>

<p align=center>
{if $sessioninfo.privilege.MST_VENDOR}
<input type=button class="btn btn-primary mt-2 mb-2" value="Save" onclick="vbb_keyin();">
{/if}
<input type=button class="btn btn-danger mt-2 mb-2" value="Close" onclick="f_v.reset(); hidediv('vbb_div');">
</p>

</form>
</div></div></div>
<!-- end of div -->
