{*
12/21/2020 9:51 AM William
- Enhanced to add new checking "disable_row_merge" and "disable_row_total" for report settings.
*}
{assign var=row_key value=$row_data.next_key}

{foreach from=$row_data.list key=row_value item=next_row_data name=frow}
	{if !$tr_alrdy_show or ($next_row_data.span eq 1 and !$smarty.foreach.frow.first)}
		<tr>
	{/if}
	
	{assign var=output_value value=$row_value}
	{if $output_value}
		{if $report_fields_list.$row_key.modifier eq 'num'}
			{assign var=output_value value=$output_value|number_format}
		{elseif $report_fields_list.$row_key.modifier eq 'num2'}
			{assign var=output_value value=$output_value|number_format:2}
		{/if}
	{/if}
	{if !$data.params.report_settings.disable_row_merge}
	<td rowspan="{$next_row_data.span}" {if $data.params.report_settings.disable_row_merge}nowrap{/if} {if $report_fields_list.$row_key.align}align="{ $report_fields_list.$row_key.align}"{/if}>{$output_value}</td>
	{else}
		{if $next_row_data.span > 1}
			<td style="padding: 0;height: 100%;" rowspan="{$next_row_data.span}" {if $report_fields_list.$row_key.align}align="{ $report_fields_list.$row_key.align}"{/if}>
				<table style="width: 100%;height:100%;border-collapse: collapse;">
				{assign var=tr_number value=0}
				{section name=tr_number loop=$next_row_data.span}
					{assign var=tr_number value=$tr_number+1}
					<tr><td {if $data.params.report_settings.disable_row_merge}nowrap{/if} style="{if !$no_header_footer}border: none !important;{/if}{if $tr_number neq $next_row_data.span && !$no_header_footer}border-bottom: 1px solid #d5d5d5 !important;{/if}">{$output_value}</td></tr>
				{/section}
				</table>
			</td>
		{else}
			<td rowspan="{$next_row_data.span}" {if $data.params.report_settings.disable_row_merge}nowrap{/if} {if $report_fields_list.$row_key.align}align="{ $report_fields_list.$row_key.align}"{/if}>{$output_value}</td>
		{/if}
	{/if}
	
	{if $next_row_data.list}
		{include file="custom_report.report_table.row.tpl" row_data=$next_row_data tr_alrdy_show=1 data_info=$data_info.data.$row_value row_total=$row_total.data.$row_value}
		
	{else}
		{* start show data by column *}		
		{include file="custom_report.report_table.row_data.tpl" col_data=$data.col_info.data_list data_info=$data_info.data.$row_value}
		
		{* Row Total *}
		{if !$data.params.report_settings.disable_row_total}
			{foreach from=$data.params.report_fields.data item=field_info}
				{assign var=col_name value=$field_info.col_name}
							
				{include file="custom_report.report_table.row_value.tpl" output_value=$row_total.data.$row_value.$col_name}
			{/foreach}
		{/if}
	{/if}
	
	
	
	{if !$tr_alrdy_show or ($next_row_data.span eq 1)}
		</tr>
	{/if}
{/foreach}
