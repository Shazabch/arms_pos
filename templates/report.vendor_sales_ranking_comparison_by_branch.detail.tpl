{*
10/14/2011 4:44:12 PM Justin
- Modified the Ctn and Pcs round up to base on config set.

8/7/2014 2:33 PM Justin
- Added new info "Cost Price" (need config).
*}

{assign var=show_type value='amount'}
{foreach from=$detail key=dept_id item=i name=d}
	{cycle values="d2,d1" assign=row_class}
	<tr class="vendor_child_{$vendor_id}" id="vendor_child1_{$vendor_id}_{$smarty.foreach.d.iteration}">
	    <td rowspan="{if $sessioninfo.privilege.SHOW_COST}3{else}2{/if}" class="{$row_class}"></td>
	    <td rowspan="{if $sessioninfo.privilege.SHOW_COST}3{else}2{/if}" colspan="2" class="{$row_class}">{$detail.$dept_id.department_name}</td>
		<td class="r"><b>Qty</b></td>
    	{foreach from=$label key=code item=r}
    		{assign var=temp value=''}
		    <td class="d3 r">{$detail.$dept_id.qty.$code|qty_nf|ifzero:'-'}</td>
		    {if $total.qty.$code >0}
		    	{assign var=temp value=$detail.$dept_id.qty.$code/$total.qty.$code}
		    {/if}
		    <td class="d5 r">{$temp*100|number_format:2|ifzero:'-':'%'}</td>
		{/foreach}
		<td class="d3 r">{$detail.$dept_id.qty.total|qty_nf|ifzero:'-'}</td>
		{if $total.qty.total > 0}
			{assign var=temp value=$detail.$dept_id.qty.total/$total.qty.total}
		{/if}
		<td class="d5 r">{$temp*100|number_format:2|ifzero:'-':'%'}</td>
	</tr>
	<tr id="vendor_child2_{$vendor_id}_{$smarty.foreach.d.iteration}">
		<td class="r"><b>S.P</b></td>
	    {foreach from=$label key=code item=r}
	    	{assign var=temp value=''}
		    <td class="d4 r">{$detail.$dept_id.$show_type.$code|number_format:2|ifzero:'-'}</td>
		    {if $total.$show_type.$code > 0}
		    	{assign var=temp value=$detail.$dept_id.$show_type.$code/$total.$show_type.$code}
		    {/if}
		    <td class="d6 r">{$temp*100|number_format:2|ifzero:'-':'%'}</td>
		{/foreach}
		<td class="d4 r">{$detail.$dept_id.$show_type.total|number_format:2|ifzero:'-'}</td>
		{if $total.$show_type.total}
			{assign var=temp value=$detail.$dept_id.$show_type.total/$total.$show_type.total}
		{/if}
		<td class="d6 r">{$temp*100|number_format:2|ifzero:'-':'%'}</td>
	</tr>
	{if $sessioninfo.privilege.SHOW_COST}
		<tr id="vendor_child3_{$vendor_id}_{$smarty.foreach.d.iteration}">
			<td class="r"><b>C.P</b></td>
			{foreach from=$label key=code item=r}
				{assign var=temp value=''}
				<td class="d4 r">{$detail.$dept_id.cost.$code|number_format:2|ifzero:'-'}</td>
				{if $total.cost.$code > 0}
					{assign var=temp value=$detail.$dept_id.cost.$code/$total.cost.$code}
				{/if}
				<td class="d6 r">{$temp*100|number_format:2|ifzero:'-':'%'}</td>
			{/foreach}
			<td class="d4 r">{$detail.$dept_id.cost.total|number_format:2|ifzero:'-'}</td>
			{if $total.cost.total}
				{assign var=temp value=$detail.$dept_id.cost.total/$total.cost.total}
			{/if}
			<td class="d6 r">{$temp*100|number_format:2|ifzero:'-':'%'}</td>
		</tr>
	{/if}
{/foreach}
