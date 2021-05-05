<h1>{$smarty.request.po_no} has been used</h1>

<div style="border:1px solid #f0f0f0;height:200px;overflow-x:hidden;overflow-y:auto;">
<table width="100%">
	<tr bgcolor="#ffee99">
	    <th>DO NO</th>
	    <th>PO No</th>
	    <th>Deliver To</th>
	    <th>Amount</th>
	    <th>DO Date</th>
	</tr>
	{foreach from=$used_do item=r}
	    <tr>
	        <td>
	            <a href="do.php?a=view&id={$r.id}&branch_id={$r.branch_id}" target="_blank">
                {if $r.approved}
					{if $r.do_no}
						{$r.do_no}
					{else}
						{$r.branch_prefix}{$r.id|string_format:"%05d"}(DD)
					{/if}
					<br>
					<font class="small" color=#009900>
					{$r.branch_prefix}{$r.id|string_format:"%05d"}(PD)
					</font>
				{elseif $r.status<1}
				    {if $r.do_no}
				        {$r.do_no}
				        <br>
						<font class="small" color=#009900>
						    {$r.branch_prefix}{$r.id|string_format:"%05d"}(DD)
						</font>
					{else}
					    {$r.branch_prefix}{$r.id|string_format:"%05d"}(DD)
				    {/if}
				{elseif $r.status eq '1'}
				    {if $r.do_no}
				        {$r.do_no}
				        <br>
						<font class="small" color=#009900>
						    {$r.branch_prefix}{$r.id|string_format:"%05d"}(PD)
						</font>
					{else}
					    {$r.branch_prefix}{$r.id|string_format:"%05d"}(PD)
				    {/if}
				{elseif $r.status>1}
					{if $r.do_no}
				        {$r.do_no}
				        <br>
						<font class="small" color=#009900>
						    {$r.branch_prefix}{$r.id|string_format:"%05d"}(PD)
						</font>
					{else}
					    {$r.branch_prefix}{$r.id|string_format:"%05d"}(PD)
				    {/if}
				{/if}
				</a>
			</td>
	        <td>{$r.po_no}</td>
	        <td>
	            {if $r.do_type eq 'credit_sales'}
				    {assign var=debtor_id value=$do_list[i].debtor_id}
				    Debtor: {$debtor.$debtor_id.code}
				{else}
					{if $r.do_branch_id}
						{$r.branch_name_2}
					{elseif $r.open_info.name}
						{$r.open_info.name}
					{/if}
					{foreach from=$r.d_branch.name item=pn name=pn}
						{if $smarty.foreach.pn.iteration>1} ,{/if}
						{$pn}
					{/foreach}
				{/if}
	        </td>
	        <td class="r">{$r.total_amount|number_format:2}</td>
	        <td align="center">{$r.do_date}</td>
	    </tr>
	{/foreach}
</table>
</div>

<p align="center">
	<input type="button" value="Continue Anyway" onClick="continue_create_do_from_po();" />
	<input type="button" value="Close" onClick="default_curtain_clicked();" />
</p>
