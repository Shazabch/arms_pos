
{foreach from=$col_data.list key=col_value item=next_col_data}

	{if $next_col_data.list}
		{include file="custom_report.report_table.row_data.tpl" col_data=$next_col_data show_title_only=$show_title_only data_info=$data_info.data.$col_value}
	{else}
	
		{foreach from=$data.params.report_fields.data item=field_info}
			{assign var=col_name value=$field_info.col_name}
			
			{if $show_title_only}
				{assign var=field_label value="`$field_info.field_formula`(`$report_fields_list[$field_info.field_type].label`)"}
				{if $field_info.field_label}
					{assign var=field_label value=$field_info.field_label}
				{/if}
				<th>{$field_label|htmlentities}</th>				
			{else}				
				{include file="custom_report.report_table.row_value.tpl" output_value=$data_info.data.$col_value.$col_name}
			{/if}
		{/foreach}
	{/if}
{/foreach}
