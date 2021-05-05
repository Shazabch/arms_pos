{*
12/21/2020 9:40 AM William
- Enhanced to add new checking "disable_row_total" setting.
*}
<ul>
	{if $data.is_preview}
		<li>Preview mode will only show limited data, for real data please save the report and view from report section.</li>
		<li>Preview mode will auto select date and will not filter branch/department/sku/category/vendor.</li>
	{/if}
	
	{if $data.got_avg_selling}
		<li>({$report_fields_list.unit_price.label}) and ({$report_fields_list.normal_selling.label}) is using average calculation and may have variance due to rounding issue.</li>
	{/if}
</ul>

<h2>{$data.report_title_str}</h2>

{if !$data.data}
	* No Data *
{else}
{if !$data.params.report_settings.disable_row_total}
{capture assign=html_header_first_line_closing}
	<th colspan="{$data.col_span_multiply}" rowspan="{count var=$data.col_info.key_list}">
		Total
	</th>
{/capture}
{/if}


<table class="report_table" width="100%">
	<tr class="header">
		{* Row Title *}
		{foreach from=$data.row_info.key_list item=row_key}
			<th rowspan="{count var=$data.col_info.key_list offset=1}">
				{$report_fields_list.$row_key.label}
			</th>
		{/foreach}
		
		{* Column *}
		{foreach from=$data.col_info.key_list item=col_key name=fcol}
			{if !$smarty.foreach.fcol.first}
				<tr class="header">
			{/if}
			
			{include file="custom_report.report_table.column.tpl" col_data=$data.col_info.data_list}
			
			{if $smarty.foreach.fcol.first}	
				{$html_header_first_line_closing}
			{/if}
			</tr>
		{foreachelse}
			{$html_header_first_line_closing}
			</tr>
		{/foreach}
		
		
		
		{* Data Title *}
		<tr class="header">
			{include file="custom_report.report_table.row_data.tpl" col_data=$data.col_info.data_list show_title_only=1}
			{if !$data.params.report_settings.disable_row_total}
			{foreach from=$data.params.report_fields.data item=field_info}
				{assign var=field_label value="`$field_info.field_formula`(`$report_fields_list[$field_info.field_type].label`)"}
				{if $field_info.field_label}
					{assign var=field_label value=$field_info.field_label}
				{/if}
				<th>{$field_label|htmlentities}</th>
			{/foreach}
			{/if}
		</tr>
	

	{* Row *}
	{include file="custom_report.report_table.row.tpl" row_data=$data.row_info.data_list data_info=$data.data row_total=$data.total.row}
	
	{* Final Total *}
	<tr class="header">
		<th colspan="{count var=$data.row_info.key_list}">Total</th>
		
		{include file="custom_report.report_table.row_data.tpl" col_data=$data.col_info.data_list data_info=$data.total.col}
		
		{* Grand Total *}
		{if !$data.params.report_settings.disable_row_total}
		{foreach from=$data.params.report_fields.data item=field_info}
			{assign var=col_name value=$field_info.col_name}
			{include file="custom_report.report_table.row_value.tpl" output_value=$data.total.total.$col_name}
		{/foreach}
		{/if}
	</tr>
</table>

{/if}