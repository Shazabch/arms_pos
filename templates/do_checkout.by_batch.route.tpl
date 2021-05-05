{*
5/29/2018 11:50AM HockLee
- Mew template: show route information.
*}
{literal}
<style>
.text-right{
	text-align: right;
}

.text-center{
	text-align: center;
}

.width{
	width: 150px;
}
</style>
{/literal}

<br />

<form method=post name=f_a onSubmit="return false;">
<input type=hidden name=a value="save_vehicle">
<input type="hidden" name="id" value="{$form.id}" />

	<h3>{$route_area|@array_keys|@array_pop}</h3>
	<center>
	<table width="90%" border=1 class="report_table">
		<tr class="header">
			<th>No.</th>
			<th>Area</th>
			<th>DO No</th>
			<th>DO Date</th>
			<th>Deliver To</th>
		</tr>
		{assign var=item_no value=0}
		{foreach from=$route_area item=route}
		{foreach from=$route item=data}
		{assign var=item_no value=$item_no+1}

		<tr>
			<td class="text-center">{$item_no}</td>
			<td>{$data.area}</td>
			<td>
				{foreach from=$destination item=debtor_area key=route_id}
					{foreach from=$debtor_area item=value}				
						{if $value.debtor_area eq $data.area}
							{$value.do_no}
						{/if}
					{/foreach}
				{/foreach}
			</td>
			<td>
				{foreach from=$destination item=debtor_area key=route_id}
					{foreach from=$debtor_area item=value}				
						{if $value.debtor_area eq $data.area}
							{$value.do_date}
						{/if}
					{/foreach}
				{/foreach}
			</td>
			<td>
				{foreach from=$destination item=debtor_area key=route_id}
					{foreach from=$debtor_area item=value}				
						{if $value.debtor_area eq $data.area}
							{$value.debtor}
						{/if}
					{/foreach}
				{/foreach}
			</td>
		</tr>
		{/foreach}
		{/foreach}
	</table>
	</center>
	<br>
	{foreach from=$destination item=debtor_area key=route_id}
		{foreach from=$debtor_area item=value}
			{if !isset($value.error)}
				<span class="small" style="color:blue;">* Area name {$value.debtor_area} of {$value.do_no} is not match with the Area above. <br>Please go to Master Files > Debtor to correct it.</span>
			{/if}
		{/foreach}
	{/foreach}
</form>