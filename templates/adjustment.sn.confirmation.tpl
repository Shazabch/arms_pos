<br />
<table width="100%">
	<tr>
		<td colspan="2"><b><font color="#f00">You have below warning before confirm this Adjustment:</font></b></td>
	</tr>

	{foreach from=$sn_error key=err_type item=sn_list}
		{if $err_type eq "duplicate"}
			<!-- duplicated S/N -->
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td>
					<b><u>Duplicated S/N:</u></b><br />
					{foreach from=$sn_list key=dummy item=sn name=sn_arr}
						{$sn}{if !$smarty.foreach.sn_arr.last},{/if}
					{/foreach}
				</td>
			</tr>
		{elseif $err_type eq "invalid"}
			<!-- inactive S/N -->
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td>
					<b><u>Invalid S/N:</u></b><br />
					{foreach from=$sn_list key=dummy item=sn name=sn_arr}
						{$sn}{if !$smarty.foreach.sn_arr.last},{/if}
					{/foreach}
				</td>
			</tr>
		{elseif $err_type eq "inactive"}
			<!-- inactive S/N -->
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td>
					<b><u>Inactive S/N:</u></b><br />
					{foreach from=$sn_list key=dummy item=sn name=sn_arr}
						{$sn}{if !$smarty.foreach.sn_arr.last},{/if}
					{/foreach}
				</td>
			</tr>
		{elseif $err_type eq "sold"}
			<!-- sold S/N -->
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			<tr>
				<td>
					<b><u>S/N have been Sold:</u></b><br />
					{foreach from=$sn_list key=dummy item=sn name=sn_arr}
						{$sn}{if !$smarty.foreach.sn_arr.last},{/if}
					{/foreach}
				</td>
			</tr>
		{/if}
	{/foreach}
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td align="center">
			<b>Do you wish to ignore and proceed?</b><br />
			<input type="button" name="sn_ok_btn" value="Confirm" onclick="do_proceed('{$form_name}');" />
			<input type="button" name="sn_cancel_btn" value="Cancel" onclick="default_curtain_clicked();" />
		</td>
	</tr>
</table>
