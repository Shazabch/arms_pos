<div class="blur"><div class="shadow"><div class="content">
<div class="small" style="position:absolute; right:10; text-align:right;"><a onclick="curtain_clicked();" accesskey="C"><img src="ui/closewin.png" border="0" align="absmiddle" style="pointer:cursor;"></a></div>
<form method="post" id="f_a" name="f_a" onsubmit="return TAX_LISTING_MODULE.ajax_update_tax();">
	<input type="hidden" name="id" value="{$form.id}">
	<input type="hidden" name="a" value="update">
	
	<div id="bmsg" style="padding:10 0 10 0px;">{if $form.id}Edit{else}Create New{/if} Tax</div>
	<table width="100%" class="report_table">
		<tr>
			<td width="120"><b>Tax Code</b></td>
			<td><input onBlur="uc(this)" name="code" size="30" value="{$form.code}" maxlength="30"> <img src="ui/rq.gif" align="absbottom" title="Required Field"></td>
		</tr>
		<tr>
			<td><b>Description</b></td>
			<td><input name="description" value="{$form.description}" size="40"> <img src="ui/rq.gif" align="absbottom" title="Required Field"></td>
		</tr>
		<tr>
			<td><b>Rate</b></td>
			<td><input name="rate" value="{$form.rate}" class="r" size="5"> %</td>
		</tr>
		<tr>
			<td><b>Indicator</b></td>
			<td><input name="indicator_receipt" value="{$form.indicator_receipt}" class="r" size="10"></td>
		</tr>
		<tr>
			<td><b>Tax Apply To</b></td>
			<td>
				<label><input type="checkbox" value="arms_fnb" {if in_array("arms_fnb", $form.tax_apply_to)}checked{/if} name="tax_apply_to[]" /> ARMS Fnb</label>
				<label><input type="checkbox" value="retail" {if in_array("retail", $form.tax_apply_to)}checked{/if} name="tax_apply_to[]" disabled /> Retail</label>
				 <img src="ui/rq.gif" align="absbottom" title="Required Field">
			</td>
		</tr>
		
	</table>
	<p align="center">
		<input type="button" value="{if $form.id}Update{else}Save{/if}" onClick="TAX_LISTING_MODULE.ajax_update_tax();" />
		{if $form.id}<input type="button" value="Restore" onclick="TAX_LISTING_MODULE.open('{$form.id}', 1);" />{/if}
		<input type="button" value="Close" onclick="curtain_clicked();" />
	</p>
</form>
</div></div></div>