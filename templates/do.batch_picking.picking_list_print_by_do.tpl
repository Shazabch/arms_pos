{*
5/18/2018 5:24PM HockLee
- Create Picking List by batch
*}

<link rel="stylesheet" href="/templates/default.css" type="text/css">
<link rel="stylesheet" media="print" href="/templates/print.css" type="text/css">

<script type="text/javascript">

var doc_no = '{$batch_code}';

{literal}
function start_print(){
	document.title = doc_no;
	window.print();
}

</script>

<style>
.title_right{ 
    float: right; 
}

.text_right{ 
    text-align: right; 
}

.text_center{ 
    text-align: center; 
}
</style>
{/literal}

<body onload="start_print();">

<h1>Batch Code: {$batch_code}
	<span class="title_right">Date: {$smarty.now|date_format:"%Y-%m-%d"}</span>
</h1>

{if $do}
	{assign var=arr_count value=$do|@count}
	{assign var=count value=$arr_count*2}	
	<h2>Picking List by DO</h2>
	<table class="report_table" width="100%">
		<tr class="header">
			<th>No.</th>
			<th>ARMS Code</th>
			<th>MCode<br />ArtNo<br />{$config.link_code_name|default:'Link Code'}</th>
			<th>SKU Description</th>
			<th>Location</th>
			<th>UOM</th>
			<th colspan='6'>DO</th>		
		</tr>
		{assign var=item_no value=0}
		{foreach from=$do_det item=do_item key=do_no}
			{foreach from=$do_item item=do_info key=uom}		
				{assign var=item_no value=$item_no+1}
				<tr>
					<td>{$item_no}</td>
					<td>{$do_info.sku_item_code}</td>
					<td>
						{$do_info.mcode|default:'-'}<br />
						{$do_info.artno|default:'-'}<br />
						{$do_info.link_code|default:'-'}
					</td>
					<td>{$do_info.description}</td>
					<td>{$do_info.location}</td>
					<td>{$do_info.uom_code}</td>
					<td>
						<table width="100%" class="report_table">					
							{assign var=do_count value=$do_info.do|@count}
							{assign var=cnt value=0}
							{assign var=row_no value=5}
							{section name=d_cnt start=0 loop=$do_count+1}
								{assign var=num value=$smarty.section.d_cnt.index}					
								{if $num%$row_no eq 1}
									{assign var=cnt value=$cnt+1}
								{/if}
							{/section}
							{assign var=cnt2 value=$cnt+1}
							{assign var=ind2 value=0}
							{assign var=ind_qty1 value=0}					
							{assign var=number value=0}					
							{section name=foo1 loop=$cnt}
							{assign var=number value=$number+1}
								<tr style="background-color:f0fa85;">
									{assign var=d_no value=0}
									{if $ind2 eq 0}
										{section name=aa start=0 loop=$do_count}
										{assign var=ind value=$smarty.section.aa.index}								
										{if $ind < $row_no}
										<th colspan='2' style="width:65px">{$do_info.do[$ind].do_no}</th>
										{assign var=d_no value=$d_no+1}
										{else}
										{assign var=ind2 value=$ind}
										{break}
										{/if}
										{/section}
									{else}								
										{section name=aa start=$ind2 loop=$do_count}
										{assign var=ind value=$smarty.section.aa.index}										
										{assign var=time value=$number*$row_no}								
										{if $ind < $time}
										<th colspan='2' style="width:65px">{$do_info.do[$ind].do_no}</th>
										{assign var=d_no value=$d_no+1}
										{else}
										{assign var=ind2 value=$ind}
										{/if}
										{/section}
									{/if}							

									<!-- N/A -->							
									{assign var=loop value=$row_no}
									{assign var=looop value=$loop-$d_no}
									{section name=foo start=0 loop=$looop}
									<th colspan='2' style="width:65px">N/A</th>
									{/section}
								</tr>
								<tr style="background-color:f0fa85;">
									{assign var=d_no value=0}

									{if $ind_unit eq 0}
										{section name=aa start=0 loop=$do_count}
										{assign var=ind value=$smarty.section.aa.index}								
										{if $ind < $row_no}
										<th>Ctn</th>
										<th>Pcs</th>
										{assign var=d_no value=$d_no+1}
										{else}
										{assign var=ind_unit value=$ind}
										{break}
										{/if}
										{/section}
									{else}								
										{section name=aa start=$ind_unit loop=$do_count}
										{assign var=ind value=$smarty.section.aa.index}										
										{assign var=time value=$number*$row_no}								
										{if $ind < $time}
										<th>Ctn</th>
										<th>Pcs</th>
										{assign var=d_no value=$d_no+1}
										{else}
										{assign var=ind_unit value=$ind}
										{/if}
										{/section}
									{/if}							

									<!-- N/A -->							
									{assign var=loop value=$row_no}
									{assign var=looop value=$loop-$d_no}
									{section name=foo start=0 loop=$looop}
									<th>Ctn</th>
									<th>Pcs</th>
									{/section}
								</tr>
								<tr class="text_right">
									{assign var=d_no value=0}
									{if $ind_qty1 eq 0}
										{section name=aa start=0 loop=$do_count}
										{assign var=ind value=$smarty.section.aa.index}								
										{if $ind < $row_no}
										<td>{$do_info.do[$ind].ctn}</td>
										<td>{$do_info.do[$ind].pcs}</td>
										{assign var=d_no value=$d_no+1}
										{else}
										{assign var=ind_qty1 value=$ind}
										{break}
										{/if}
										{/section}
									{else}		
										{section name=aa start=$ind_qty1 loop=$do_count}
										{assign var=ind value=$smarty.section.aa.index}										
										{assign var=time value=$number*$row_no}								
										{if $ind < $time}
										<td>{$do_info.do[$ind].ctn}</td>
										<td>{$do_info.do[$ind].pcs}</td>
										{assign var=d_no value=$d_no+1}
										{else}
										{assign var=ind_qty1 value=$ind}
										{/if}
										{/section}
									{/if}							

									<!-- N/A -->							
									{assign var=loop value=$row_no}
									{assign var=looop value=$loop-$d_no}
									{section name=foo start=0 loop=$looop}
									<th class="text_center">-</th>
									<th class="text_center">-</th>
									{/section}
								</tr>
							{/section}
						</table>
					</td>
				</tr>
			{/foreach}
		{/foreach}		
	</table>
{if $checkout eq 1}<h5>* this batch has been checkout.</h5>{/if}
{/if}
