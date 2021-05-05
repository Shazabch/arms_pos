{*
10/29/2010 5:35:26 PM Alex
- add show cost privilege
- change use template

11/2/2010 1:05:22 PM Alex
- remove show cost privilege checking

4/20/2015 5:16 PM Justin
- Enhanced to have GST information.

5/7/2018 4:16 PM Justin
- Enhanced to have foreign currency feature.

12/5/2018 11:07 AM Justin
- Bug fixed on row span issue for Items Not in SKU.
*}

<table class=tb cellpadding=4 cellspacing=0 border=0>
	{if $is_under_gst}
		{assign var=dept_colspan value=4}
		{assign var=colspan value=4}
		{assign var=rowspan value=1}
		{assign var=xtra_colspan value=3}
	{else}
		{assign var=dept_colspan value=2}
		{assign var=colspan value=2}
		{assign var=rowspan value=2}
		{assign var=xtra_colspan value=1}
	{/if}
	
	{if $have_fc_list}
		{assign var=colspan value=$colspan+1}
		{assign var=xtra_colspan value=$xtra_colspan+1}
	{/if}
	
	<tr bgcolor=#ffee99>
		<th rowspan="2">&nbsp;</th>
		{foreach from=$dept key=dept_id item=d}
			{assign var=tmp_colspan value=$dept_colspan}
			{if isset($have_fc_list.$dept_id)}
				{assign var=tmp_colspan value=$tmp_colspan+1}
			{/if}
		    <th colspan="{$tmp_colspan}">{$d}</th>
		{/foreach}
    	<th colspan="{$xtra_colspan}" bgcolor=#ffff00>Items Not in SKU</th>
		<th colspan="{$colspan}">Total</th>
	</tr>
	<tr bgcolor=#ffee99>
		{foreach from=$dept key=dept_id item=d}
		    <th>Qty</td>
			{if isset($have_fc_list.$dept_id)}
				<th>Frgn. Amt</th>
			{/if}
			<th>Amt</th>
			{if $is_under_gst}
				<th>GST</th>
				<th>Amt Incl. GST</th>
			{/if}
		{/foreach}
		
		{if $have_fc_list}
			<th bgcolor=#ffff00>Frgn. Amt</th>
		{/if}
		<th bgcolor=#ffff00>Amt</th>			
		{if $is_under_gst}
			<th bgcolor=#ffff00>GST</th>
			<th bgcolor=#ffff00>Amt Incl. GST</th>
		{/if}
		
	    <th>Qty</th>
		{if $have_fc_list}
			<th>Frgn. Amt</th>
		{/if}
		<th>Amt</th>
		{if $is_under_gst}
			<th>GST</th>
			<th>Amt Incl. GST</th>
		{/if}
	</tr>
	{foreach from=$tb key=v item=r}
	    <tr>
			<td nowrap>
			<a href="goods_return_advice.summary_by_dept.php?from={$smarty.request.from}&to={$smarty.request.to}&branch_id={$smarty.request.branch_id}&department_id={$smarty.request.department_id}&returned={$smarty.request.returned}&vendor_id={$v}">
				{$vendor.$v}
			</a>&nbsp;
			</td>
		{foreach from=$dept key=did item=d}
			<td align=right>{$r.$did.qty|ifzero:"&nbsp;"}</td>
			{if isset($have_fc_list.$did)}
				<td align=right>{$r.$did.foreign_amt|number_format:2|ifzero:"-"}</td>
			{/if}
			<td align=right {if isset($have_fc_list.$did)}class="converted_base_amt"{/if}>{$r.$did.amt|number_format:2|ifzero:"&nbsp;"}{if isset($have_fc_list.$did) && $r.$did.amt}*{/if}</td>
			{if $is_under_gst}
				<td align=right>{$r.$did.gst|number_format:2|ifzero:"&nbsp;"}</td>
				<td align=right {if isset($have_fc_list.$did)}class="converted_base_amt"{/if}>{$r.$did.gst_amt|number_format:2|ifzero:"&nbsp;"}{if isset($have_fc_list.$did) && $r.$did.gst_amt}*{/if}</td>
			{/if}
		{/foreach}
		{if $have_fc_list}
			<td align=right bgcolor=#ffff77>{$tb.$v.foreign_extra|number_format:2|ifzero:"-"}</td>
		{/if}
		<td align=right bgcolor=#ffff77 {if $have_fc_list}class="converted_base_amt"{/if}>{$tb.$v.extra|number_format:2|ifzero:"&nbsp;"}{if $have_fc_list && $tb.$v.extra}*{/if}</td>
		{if $is_under_gst}
			<td align=right bgcolor=#ffff77>{$tb.$v.extra_gst|number_format:2|ifzero:"&nbsp;"}</td>
			<td align=right bgcolor=#ffff77 {if $have_fc_list}class="converted_base_amt"{/if}>{$tb.$v.extra_gst_amt|number_format:2|ifzero:"&nbsp;"}{if $have_fc_list && $tb.$v.extra_gst_amt}*{/if}</td>
		{/if}
		<td align=right>{$vendor_total.$v.qty|ifzero:"&nbsp;"}</td>
		{if $have_fc_list}
			<td align=right>{$vendor_total.$v.foreign_amt|number_format:2|ifzero:"-"}</td>
		{/if}
		<td align=right {if $have_fc_list}class="converted_base_amt"{/if}>{$vendor_total.$v.amt|number_format:2|ifzero:"&nbsp;"}{if $have_fc_list && $vendor_total.$v.amt}*{/if}</td>
		{if $is_under_gst}
			<td align=right>{$vendor_total.$v.gst|number_format:2|ifzero:"&nbsp;"}</td>
			<td align=right {if $have_fc_list}class="converted_base_amt"{/if}>{$vendor_total.$v.gst_amt|number_format:2|ifzero:"&nbsp;"}{if $have_fc_list && $vendor_total.$v.gst_amt}*{/if}</td>
		{/if}
		</tr>
	{/foreach}
	<tr>
		<td><b>Total</b></td>
		{foreach from=$dept key=did item=d}
			<td align=right>{$dept_total.$did.qty|ifzero:"&nbsp;"}</td>
			{if isset($have_fc_list.$did)}
				<td align=right>{$dept_total.$did.foreign_amt|number_format:2|ifzero:"-"}</td>
			{/if}
			<td align=right {if isset($have_fc_list.$did)}class="converted_base_amt"{/if}>{$dept_total.$did.amt|number_format:2|ifzero:"&nbsp;"}{if isset($have_fc_list.$did) && $dept_total.$did.amt}*{/if}</td>
			{if $is_under_gst}
				<td align=right>{$dept_total.$did.gst|number_format:2|ifzero:"&nbsp;"}</td>
				<td align=right {if isset($have_fc_list.$did)}class="converted_base_amt"{/if}>{$dept_total.$did.gst_amt|number_format:2|ifzero:"&nbsp;"}{if isset($have_fc_list.$did) && $dept_total.$did.gst_amt}*{/if}</td>
			{/if}
		{/foreach}
		{if $have_fc_list}
			<td align=right bgcolor=#ffff77>{$extra_total.foreign_amt|number_format:2|ifzero:"-"}</td>
		{/if}
		<td align=right bgcolor=#ffff77 {if $have_fc_list}class="converted_base_amt"{/if}>{$extra_total.amt|number_format:2|ifzero:"&nbsp;"}{if $have_fc_list && $extra_total.amt}*{/if}</td>
		{if $is_under_gst}
			<td align=right bgcolor=#ffff77>{$extra_total.gst|number_format:2|ifzero:"&nbsp;"}</td>
			<td align=right bgcolor=#ffff77 {if $have_fc_list}class="converted_base_amt"{/if}>{$extra_total.gst_amt|number_format:2|ifzero:"&nbsp;"}{if $have_fc_list && $extra_total.gst_amt}*{/if}</td>
		{/if}
		<td align=right>{$final_total.qty|ifzero:"&nbsp;"}</td>
		{if $have_fc_list}
			<td align=right>{$final_total.foreign_amt|number_format:2|ifzero:"-"}</td>
		{/if}
		<td align=right {if $have_fc_list}class="converted_base_amt"{/if}>{$final_total.amt|number_format:2|ifzero:"&nbsp;"}{if $have_fc_list && $final_total.amt}*{/if}</td>
		{if $is_under_gst}
			<td align=right>{$final_total.gst|number_format:2|ifzero:"&nbsp;"}</td>
			<td align=right {if $have_fc_list}class="converted_base_amt"{/if}>{$final_total.gst_amt|number_format:2|ifzero:"&nbsp;"}{if $have_fc_list && $final_total.gst_amt}*{/if}</td>
		{/if}
	</tr>
</table>

