{include file='header.tpl'}


<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var PH_ASSIGN_HOME = {
	initialize: function(){		
		
	}
};
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

<div>
	<a href="?a=open">
		<img src="ui/new.png" title="New" align="absmiddle" border="0" /> Add New Year
	</a> 
</div>
<br />

<table class="report_table" width="100%">
	<tr class="header">
		<th>&nbsp;</th>
		<th>Year</th>
		
		{foreach from=$appCore->monthsList key=m item=m_label}
			<th>{$m_label}</th>
		{/foreach}
	</tr>
	
	<tbody id="tbody_year_list">
		{if $y_ph_list}
			{foreach from=$y_ph_list key=ph_year_id item=ph_year}
				<tr>
					<td>
						<a href="?a=open&id={$ph_year_id}">
							<img src="ui/ed.png" title="Edit" border="0" />
						</a>
					</td>
					
					<td>{$ph_year.y}</td>
					
					{foreach from=$appCore->monthsList key=m item=m_label}
						<td>
							{if $ph_year.ph_by_month.$m}
								{foreach from=$ph_year.ph_by_month.$m key=ph_id item=ph_data}
									{$ph_data.ph_code}<br />
								{/foreach}
							{/if}
						</td>
					{/foreach}
				</tr>
			{/foreach}
		{else}
			<tr>
				<td colspan="13">* No Data *</td>
			</tr>
		{/if}
	</tbody>
</table>

<script>PH_ASSIGN_HOME.initialize();</script>
{include file='footer.tpl'}