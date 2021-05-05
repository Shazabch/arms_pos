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
</style>
{/literal}

<body onload="start_print();">

<h1>Batch Code: {$batch_code}
	<span class="title_right">Date: {$smarty.now|date_format:"%Y-%m-%d"}</span>
</h1>

{if $do}
	{assign var=arr_count value=$do|@count}
	{assign var=count value=$arr_count*2}
	<h2>Picking List by Item</h2>
	<table class="report_table" width="100%">
		<tr class="header">
			<th>No.</th>
			<th>ARMS Code</th>
			<th>MCode<br />ArtNo<br />{$config.link_code_name|default:'Link Code'}</th>
			<th>SKU Description</th>
			<th>Location</th>
			<th>UOM</th>
			<th>UOM Qty</th>
			<th>Qty in PCS</th>			
		</tr>
		{assign var=item_no value=0}
		{foreach from=$group_item item=grp_item key=sku_item}
		{assign var=item_no value=$item_no+1}	
		<tr>
			<td>{$item_no}</td>
			<td>{$grp_item.arms_code}</td>
			<td>
				{$grp_item.mcode|default:'-'}<br />
				{$grp_item.artno|default:'-'}<br />
				{$grp_item.old_code|default:'-'}
			</td>
			<td>{$grp_item.description}</td>
			<td>{$grp_item.location}</td>
			<td>
				{assign var=dup value=null}
				{foreach from=$grp_item.uom_fra item=fraction}
				{assign var="fra" value="_"|explode:$fraction}
					{if $fra[1] eq 1 && $grp_item.pcs ne 0}					
						{if $dup ne $fra[0]}
							{$fra[0]}<br>
						{/if}
						{assign var=key value=1}					
					{else}
						{$fra[0]}<br>
						{assign var=key value=0}					
					{/if}
					{assign var=dup value=$fra[0]}
				{/foreach}
				{if $key eq 0}
				{if $grp_item.pcs ne 0}PCS{/if}
				{/if}
			</td>
			<td class="text_right">
				{assign var=dup_ctn value=null}
				{foreach from=$grp_item.uom_ctn item=uom_ctn}
					{if $dup_ctn ne $uom_ctn}
						{if $uom_ctn ne 0}{$uom_ctn}</br>{/if}
					{/if}
					{assign var=dup_ctn value=$uom_ctn}
				{/foreach}
				{if $grp_item.pcs eq 0}{else}{$grp_item.pcs}{/if}
			</td>
			<td class="text_right">
				{$grp_item.total_qty}
			</td>
		</tr>				
		{/foreach}
		<tr class="text_right">
			<th colspan='7'>Total</th>
			<td>{$grand_total.total}</td>
		</tr>
	</table>
{if $checkout eq 1}<h5>* this batch has been checkout.</h5>{/if}
{/if}
