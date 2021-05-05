<select name="sku_monitoring_group_id" onChange="sku_monitoring_group_changed();">
    <option value="">-- Please Select --</option>
    {assign var=last_dept_id value=''}
    {foreach from=$sku_m_group key=id item=r}
        {if $r.dept_id ne $last_dept_id}
            {if $last_dept_id}</optgroup>{/if}
            <optgroup label="{$r.dept_name}">
            {assign var=last_dept_id value=$r.dept_id}
        {/if}
        {assign var=padding value='&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'}
        <optgroup label="{$padding} {$r.group_name} ({$r.username})">
            {foreach from=$r.batch item=b}
                {capture assign=smg}{$id},{$b.year},{$b.month}{/capture}
                <option value="{$smg}" date_from="{$b.date}" {if $smg eq $smarty.request.sku_monitoring_group_id}selected {/if}>{$padding} {$b.month|str_month} {$b.year} ({$b.sku_count|number_format})</option>
            {/foreach}
        </optgroup>
    {/foreach}
    {if $last_dept_id}</optgroup>{/if}
</select>

<a href="javascript:void(view_batch_sku_details());"><img src="/ui/view.png" align="absmiddle" title="View SKU Details" border="0" /></a>
