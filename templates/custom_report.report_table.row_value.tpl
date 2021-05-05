{*
6/19/2020 2:15 PM William
- Enhanced to added new new number_format.

12/21/2020 3:18 PM William
- Enhanced to add nowrap when "disable_row_merge".
*}
{if $output_value}
	{if $report_fields_list[$field_info.field_type].modifier eq 'num'}
		{assign var=output_value value=$output_value|number_format}
	{elseif $report_fields_list[$field_info.field_type].modifier eq 'num2'}
		{assign var=output_value value=$output_value|number_format:2}
	{elseif $report_fields_list[$field_info.field_type].modifier eq 'config_global_cost'}
		{assign var=output_value value=$output_value|number_format:$config.global_cost_decimal_points|ifzero}
	{else}
		{assign var=output_value value=$output_value}
	{/if}
{/if}
<td {if $data.params.report_settings.disable_row_merge}nowrap{/if} {if $report_fields_list[$field_info.field_type].align}align="{$report_fields_list[$field_info.field_type].align}"{/if}>{$output_value}</td>