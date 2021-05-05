{*
12/21/2020 9:34 AM William
- Enhanced to add new checking for "disable_column_merge" setting.
*}
{* Column key match *}
{foreach from=$col_data.list key=col_value item=next_col_data}

	{if $col_data.next_key eq $col_key}
		{if $data.params.report_settings.disable_column_merge}
			{assign var=th_number value=0}
			{section name=th_number loop=$next_col_data.span}
				<th colspan="1" title="{$report_fields_list.$col_key.label}">{$col_value} ({$report_fields_list.$col_key.label})</th>
			{/section}
		{else}
			<th colspan="{$next_col_data.span}" title="{$report_fields_list.$col_key.label}">{$col_value} ({$report_fields_list.$col_key.label})</th>
		{/if}
	{else}
		{* not match, show next level of column *}
		{if $next_col_data.list}
			{include file="custom_report.report_table.column.tpl" col_data=$next_col_data}
		{/if}
	{/if}

{/foreach}