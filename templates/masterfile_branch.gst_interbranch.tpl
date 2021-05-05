<form name="f_gst_interbranch">
	<input type="hidden" name="a" value="save_gst_interbranch" />
	
	<p>* Check the checkbox to enable GST interbranch for specified branch</p>
	<table width="100%" class="report_table" style="background:#fff;">
		<tr class="header">
			<th>&nbsp;</th>
			{foreach from=$branches key=bid item=b}
				<th>
					{$b.code}
					<input type="checkbox" onChange="toggle_gst_interbranch(this, 0, '{$bid}')" />
				</th>
			{/foreach}
		</tr>
		{foreach from=$branches key=bid item=b name=f_b1}
			<tr>
				<td>
					{$b.code}
					<input type="checkbox" onChange="toggle_gst_interbranch(this, '{$bid}', 0)" />
				</td>
				
				{foreach from=$branches key=bid2 item=b2 name=f_b2}
					{if $bid eq $bid2 or $smarty.foreach.f_b1.index > $smarty.foreach.f_b2.index}
						<td class="td_same_branch">&nbsp;</td>
					{else}
						<td align="center"><input type="checkbox" name="gst_interbranch[{$bid}][{$bid2}]" {if $gst_interbranch.$bid.$bid2}checked {/if} value="1" 
						class="chx_interbranch-{$bid} chx_interbranch2-{$bid2}" /></td>
					{/if}
				{/foreach}
			</tr>
		{/foreach}
	</table>
	
	<p align="center">
		<input type="button" value="Save" onClick="save_gst_interbranch();" id="btn_save_gst_interbranch" />
	</p>
</form>