{*
7/10/2009 4:51:36 PM Andy
- add closing stock

1/6/2010 4:31:39 PM Andy
- Add config 'ci_use_split_artno' to check whether split artno

2/8/2010 3:16:38 PM Andy
- Add consignment over invoice column in monthly report

6/8/2010 10:11:36 AM Andy
- CN/DN Swap

1/26/2011 2:56:35 PM Andy
- Add search sku and directly goto the page.

11/22/2011 3:12:37 PM Andy
- Add Qty In, Qty Out, Posted Adj and Adj Amount.
- Show price type total at top panel list.

4/2/2012 12:23:23 PM Andy
- Add column "Open Qty" and "Open Amt" at summary panel.

30/8/2012 4:37:42PM Drkoay
- Add 4 customize column. if $config.monthly_report_additional_qty_sold=1

5/8/2015 11:16 AM Andy
- Change the word "Lost (DN)" to "Lost (Inv)".
*}

{if $export_info or $smarty.request.read_only}
	{assign var=read_only value=1}
{else}
	{assign var=read_only value=0}
{/if}

{if $config.monthly_report_additional_qty_sold}
	{assign var="rowspan" value="2"}
	{assign var="colspan" value="5"}
{/if}

<table class="report_table" width="100%" id="table_p">
<tr class="header">
    <th rowspan="{$rowspan|default:1}">{$current_page_discount_code}</th>
    {if $config.ci_use_split_artno}
	    <th rowspan="{$rowspan|default:1}">Code</th>
	    <th rowspan="{$rowspan|default:1}">Size</th>
    {else}
        <th rowspan="{$rowspan|default:1}">Art No.</th>
    {/if}
    <th rowspan="{$rowspan|default:1}">Description</th>
{*
    <th >Open Qty</th>
    <th >Adj</th>
    <th >Qty In</th>
*}
    <th rowspan="{$rowspan|default:1}">Open Qty</th>
    <th rowspan="{$rowspan|default:1}">Qty In</th>
    <th rowspan="{$rowspan|default:1}">Qty Out</th>
    <th rowspan="{$rowspan|default:1}">Posted<br>Adj</th>
    <th rowspan="{$rowspan|default:1}">Bal</th>
    <th rowspan="{$rowspan|default:1}">Adj</th>
    <th rowspan="{$rowspan|default:1}">Lost (Inv)</th>
    <th rowspan="{$rowspan|default:1}">Over (CN)</th>
    <th colspan="{$colspan|default:1}">Qty Sold</th>
    <th rowspan="{$rowspan|default:1}">Price</th>
    <th rowspan="{$rowspan|default:1}">Sold Amount</th>
</tr>
{if $config.monthly_report_additional_qty_sold}
<tr class="header">
	<th width="80">Default</th>
	<th style="background: #b1fef4;text-align: center;" class="keyin" id="qty1_sold" width="80">{$additional_column.qty1_sold}</th>
	<th style="background: #b1fef4;text-align: center;" class="keyin" id="qty2_sold" width="80">{$additional_column.qty2_sold}</th>
	<th style="background: #b1fef4;text-align: center;" class="keyin" id="qty3_sold" width="80">{$additional_column.qty3_sold}</th>
	<th style="background: #b1fef4;text-align: center;" class="keyin" id="qty4_sold" width="80">{$additional_column.qty4_sold}</th>
</tr>
{/if}

{foreach from=$table key=sid item=r name=row_num}
    	{assign var=n value=$smarty.foreach.row_num.iteration}
        <tr bgcolor="{cycle values='#f0f0f0,#ffffff'}" {if $smarty.request.highlight_sid eq $sid}class="highlight_row"{/if}>
            <td align=right>{$r.row_num}.</td>
            {if $config.ci_use_split_artno}
	            <td nowrap><b>{$r.artno_code}</b></td>
	            <td nowrap><b>{$r.artno_size}</b></td>
            {else}
                <td nowrap><b>{$r.art_no}</b></td>
            {/if}
            <td>{$r.description}</td>
			{*
			<td class="r"><b>{$r.open_qty}</b></td>
			<td class="r">{$r.adj}</td>
			<td class="r">{$r.qty_in}</td>
			*}
			<td class="r" style="font-weight:bold;">{$r.total_open_qty}
				<input type="hidden" id="opening_qty,inp,{$sid}" value="{$r.total_open_qty}" />
			</td>
			<td class="r">{$r.grn|number_format|ifzero:'-'}</td>
			<td class="r">{$r.qty_out|number_format|ifzero:'-'}</td>
			<td class="r">{$r.adj2|number_format|ifzero:'-'}</td>
			<td class="r" style="font-weight:bold;">{$r.open_bal|number_format|ifzero:'-'}
				<input type="hidden" id="open_bal,inp,{$sid}" value="{$r.open_bal}" />
			</td>
			<td class="keyin r" title="1,{$n}" id="adj,keyin,{$sid}">{$report_data.$sid.adj|number_format|ifzero:''}</td>
			<td class="keyin r" title="2,{$n}" id="lost,keyin,{$sid}">{$report_data.$sid.lost|number_format|ifzero:''}</td>
			<td class="keyin r" title="3,{$n}" id="over,keyin,{$sid}">{$report_data.$sid.over|number_format|ifzero:''}</td>
			<td class="keyin r" title="4,{$n}" id="qty,keyin,{$sid}">{$report_data.$sid.total|number_format|ifzero:''}</td>
	  
			{if $report_data.$sid.price}
				{assign var=row_price value=$report_data.$sid.price}
			{else}
				{assign var=row_price value=$r.price}
			{/if}
	  
			{if $config.monthly_report_additional_qty_sold}
			<td class="keyin r" title="5,{$n}" id="qty1,keyin,{$sid}">{$report_data.$sid.qty1|number_format|ifzero:''}</td>
			<td class="keyin r" title="6,{$n}" id="qty2,keyin,{$sid}">{$report_data.$sid.qty2|number_format|ifzero:''}</td>
			<td class="keyin r" title="7,{$n}" id="qty3,keyin,{$sid}">{$report_data.$sid.qty3|number_format|ifzero:''}</td>
			<td class="keyin r" title="8,{$n}" id="qty4,keyin,{$sid}">{$report_data.$sid.qty4|number_format|ifzero:''}</td>
			<td class="keyin r" title="9,{$n}" id="price,keyin,{$sid}">{$row_price|number_format:2}</td>
			{else}
			<td class="keyin r" title="5,{$n}" id="price,keyin,{$sid}">{$row_price|number_format:2}</td>
			{/if}
			<td class="r" id="price,total,{$sid}">
				{if $config.monthly_report_additional_qty_sold}
				{math assign="row_total" equation="x*(a+b+c+d+e)" x=$row_price a=$report_data.$sid.total|default:0 b=$report_data.$sid.qty1|default:0 c=$report_data.$sid.qty2|default:0 d=$report_data.$sid.qty3|default:0 e=$report_data.$sid.qty4|default:0}
				{else}
				{math assign="row_total" equation="x*a" x=$row_price a=$report_data.$sid.total|default:0}
				{/if}
				{$row_total}
				{*{$row_price*$report_data.$sid.total|number_format:2}*}
			</td>
        </tr>
{/foreach}
</table>

<script>

$('span,opening_qty,page_total').update('{$report_info.page_total.opening_qty|number_format}');
$('span,opening_amt,page_total').update('{$report_info.page_total.opening_amt|number_format:2}');
$('span,grn,page_total').update('{$report_info.page_total.grn|number_format}');
$('span,qty_out,page_total').update('{$report_info.page_total.qty_out|number_format}');
$('span,adj2,page_total').update('{$report_info.page_total.adj2|number_format}');
$('span,qty,page_total').update('{$report_info.page_total.qty|number_format}');
{if $config.monthly_report_additional_qty_sold}
$('span,qty1,page_total').update('{$report_info.page_total.qty1|number_format}');
$('span,qty2,page_total').update('{$report_info.page_total.qty2|number_format}');
$('span,qty3,page_total').update('{$report_info.page_total.qty3|number_format}');
$('span,qty4,page_total').update('{$report_info.page_total.qty4|number_format}');
{/if}
$('span,amount,page_total').update('{$report_info.page_total.amt|number_format:2}');
$('span,adj,page_total').update('{$report_info.page_total.adj|number_format}');
$('span,adj_amount,page_total').update('{$report_info.page_total.adj_amt|number_format:2}');
$('span,lost,page_total').update('{$report_info.page_total.lost|number_format}');
$('span,lost_amount,page_total').update('{$report_info.page_total.lost_amt|number_format:2}');
$('span,over,page_total').update('{$report_info.page_total.over|number_format}');
$('span,over_amount,page_total').update('{$report_info.page_total.over_amt|number_format:2}');
$('span,stock_closing,page_total').update('{$report_info.page_total.stock_closing|number_format:2}');

{if !$read_only}
{literal}
$$('#div_content th.keyin').each(function(ele){
    Event.observe(ele, 'click', function(event) {
	  	do_select(this,true);
	});
});

$$('#div_content td.keyin').each(function(ele){
    Event.observe(ele, 'click', function(event) {
	  	do_select(this);
	});
});

{/literal}
{/if}

</script>
