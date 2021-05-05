{*
1/20/2017 2:47 PM Andy
- Enlarge the selling price font size.
- Add show stock balance if using main server.

2/6/2017 1:48 PM Andy
- Remove the word 'Selling'.
- Add bold for selling price.

4/26/2017 10:40 AM Khausalya
- Enhanced changes from RM to use config setting.

7/31/2018 4:33 PM Andy
- Enhanced to check "Not Allow Discount".
*}

<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name='robots' content='noindex, nofollow'>
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="cache-control" content="no-cache, must-revalidate">
<meta http-equiv="expires" content="0">
<meta http-equiv="last-modified" content="">
<meta http-equiv="refresh" content="30;URL=.">
<link rel="stylesheet" href="style/style.css?v=2" type="text/css">
<link rel="stylesheet" href="style/style2.css" type="text/css">

<title>{$branch_info.code} | {$PAGE_TITLE}</title>
</head>

{literal}
<style>
</style>

<script>
function check_submit(){
	document.getElementById("td_sku_result").innerHTML = "<div style='text-align:center;'><h1>Checking. . .</h1></div>";
	document.getElementById("td_stock").innerHTML = "&nbsp;";

}
</script>
{/literal}

<body onload="document.f_a.code.focus();">
	<div style="height:100%;">
		<form name="f_a" style="height:95%;" method="post" action=".#sku_result" onSubmit="check_submit();">
			<div style="margin:0 auto;width:95%;padding: 5px;height:100%;">
				<table align="center" border="0" width="100%" height="95%" cellspacing="10">
					<tr>
						<td colspan="3">Date: {$smarty.now|date_format:'%d/%m/%Y'}</td>
						<td width="25%">Time: {$smarty.now|date_format:'%r'}</td>
					</tr>
					
					<tr>
						<td colspan="3">&nbsp;</td>
						<td>Count: {$scan_counter|default:'0'}</td>
					</tr>
					
					<tr>
						<td colspan="4"><span class="large"><b>Scan product at here<b/></span></td>
					</tr>
					
					<tr>
						<td colspan="2"><input type="text" name="code" style="font-size:20px;width:100%;" /></td>
						{if $show_stock}
							<td align="center" class="large">STOCK</td>
							<td class="label large" id="td_stock">{if $sku.id}{$sku.stock_balance|default:0}{else}&nbsp;{/if}</td>
						{/if}
					</tr>
					
					<tr style="height:80%;max-height:80%;">
						<td colspan="4" class="label" id="td_sku_result">
							{if $sku}
								{if $sku.error}
									<div class="large" style="text-align:center;">{$smarty.request.code}<br />Item Not Found</div>
								{else}
									<A NAME="sku_result"></a>
									<table width="100%" height="100%" border="0">
										<tr>
											<td colspan="2" class="large">{$sku.description}</td>
										</tr>
										
										<tr>
											<td colspan="2">
												{if $sku.artno}
													{$sku.artno}
													{if $sku.mcode} / {/if}
												{/if}
												{if $sku.mcode}
													{$sku.mcode}
												{/if}
											</td>
										</tr>
										
										<tr>
											<td align="center">
												{if $sku.not_allow_disc}
													<div class="div_not_allow_discount">
														Not Allow Discount
													</div>
												{/if}
												
												{if $sku.photo}
													<img class="sku_photo" align="absmiddle" vspace="4" hspace="4" src="/thumb.php?w=150&h=150&cache=1&img={$sku.photo|urlencode}" border="0" />
												{/if}
											</td>
											<td align="right">												
												<div class="div_sp">
												<!-- got member price -->
												{if $sku.member_price>0 && $sku.member_price != $sku.price}
													{if $sku.member_price == $sku.non_member_price}<!-- member price same as non member price -->
														Normal Price {if $sku.is_under_gst}({$sku.output_gst.indicator_receipt}){/if}: <s>{$config.arms_currency.symbol}{$sku.default_price|number_format:2}</s><br>
														Today Price: <span class="span_sp">{$config.arms_currency.symbol}{$sku.member_price|number_format:2} ({$sku.member_discount}%)</span><br>
														{if $sku.is_under_gst}
															<span class="gst_info">Before GST : {$sku.member_price_before_gst|number_format:2}, GST : {$sku.member_gst_amt|number_format:2}</span><br />
														{/if}
														
														{if $sku.member_date_to}
															<span class="small">Valid till {$sku.member_date_to}</span><br>
														{/if}
													{else}
														{if $sku.non_member_price eq $sku.default_price}
															Price {if $sku.is_under_gst}({$sku.output_gst.indicator_receipt}){/if}: <span class="span_sp">{$config.arms_currency.symbol}{$sku.default_price|number_format:2}</span><br>
															{if $sku.is_under_gst}
																<span class="gst_info">Before GST : {$sku.default_price_before_gst|number_format:2}, GST : {$sku.default_gst_amt|number_format:2}</span><br />
															{/if}
														{else}
															Normal Price {if $sku.is_under_gst}({$sku.output_gst.indicator_receipt}){/if}: <s>{$config.arms_currency.symbol}{$sku.default_price|number_format:2}</s><br>
															Today Price: <span class="span_sp">{$config.arms_currency.symbol}{$sku.non_member_price|number_format:2} ({$sku.non_member_discount}%)</span><br>
															{if $sku.is_under_gst}
																<span class="gst_info">Before GST : {$sku.non_member_price_before_gst|number_format:2}, GST : {$sku.non_member_gst_amt|number_format:2}</span><br />
															{/if}
															{if $sku.non_member_date_to}
																<span class="small">Valid till {$sku.non_member_date_to}</span><br>
															{/if}
														{/if}
														Member Price: <span class="span_sp">{$config.arms_currency.symbol}{$sku.member_price|number_format:2} ({if $sku.member_limit}Limit {$sku.member_limit}, {/if}{$sku.member_discount}%)</span><br>
														{if $sku.is_under_gst}
															<span class="gst_info">Before GST : {$sku.member_price_before_gst|number_format:2}, GST : {$sku.member_gst_amt|number_format:2}</span><br />
														{/if}
														{if $sku.member_date_to}
															<span class="small">Valid till {$sku.member_date_to}</span><br>
														{/if}
													{/if}
													
												{else}
													{if $sku.disc}
														Normal Price {if $sku.is_under_gst}({$sku.output_gst.indicator_receipt}){/if}: <s>{$config.arms_currency.symbol}{$sku.default_price|number_format:2}</s><br>
														Today Price: <span class="span_sp">{$config.arms_currency.symbol}{$sku.price|number_format:2} ({$sku.disc}%)</span><br>
														{if $sku.is_under_gst}
															<span class="gst_info">Before GST : {$sku.price_before_gst|number_format:2}, GST : {$sku.gst_amt|number_format:2}</span><br />
														{/if}
													{else}
														Price {if $sku.is_under_gst}({$sku.output_gst.indicator_receipt}){/if}: <span class="span_sp">{$config.arms_currency.symbol}{$sku.default_price|number_format:2}</span><br>
														{if $sku.is_under_gst}
															<span class="gst_info">Before GST : {$sku.default_price_before_gst|number_format:2}, GST : {$sku.default_gst_amt|number_format:2}</span><br />
														{/if}
													{/if}
												{/if}
												
												{* if $sku.member_type_cat_discount}
													<br>
													{foreach from=$sku.member_type_cat_discount key=mtype item=r}
														{$mtype}: {$config.arms_currency.symbol}{$r.price|number_format:2} {if $r.discount}({$r.discount}%){/if}<br>
														{if $r.date_to}
															<span class="small">Valid till {$r.date_to}</span><br>
														{/if}
													{/foreach}
												{/if *}
												</div>
											</td>
										</tr>
									</table>
								{/if}
							{/if}
						</td>
					</tr>
					
					<tr>
						<td colspan="4">
							Relation: 
							{capture assign=ctn1_desc}{strip}
								{if $sku.ctn_1_uom_id ne $sku.packing_uom_id and $sku.ctn_1_uom_code}
									{$sku.ctn_1_uom_code}
								{/if}
							{/strip}{/capture}
							
							{capture assign=ctn2_desc}{strip}
								{if $sku.ctn_2_uom_id ne $sku.packing_uom_id and $sku.ctn_2_uom_code}
									{$sku.ctn_2_uom_code}
								{/if}
							{/strip}{/capture}
							{$ctn1_desc}
							{if $ctn1_desc and $ctn2_desc}, {/if}
							{$ctn2_desc}
						</td>
					</tr>
					
					<tr>
						<td style="width:25%;">
							Location: <br />
							{$sku.location}
						</td>
						
						<td style="width:25%;">
							Department: </br />
							{if $sku.cat_tree_info.1.code}
								{$sku.cat_tree_info.1.code} - 
							{/if}
							{$sku.cat_tree_info.1.description}
						</td>
						
						<td style="width:25%;">
							Category: </br />
							{if $sku.cat_tree_info.2.code}
								{$sku.cat_tree_info.2.code} - 
							{/if}
							{$sku.cat_tree_info.2.description}
						</td>
						
						<td style="width:25%;">
							Sub - Category: <br />
							{if $sku.cat_tree_info.3.code}
								{$sku.cat_tree_info.3.code} - 
							{/if}
							{$sku.cat_tree_info.3.description}
						</td>
					</tr>
					
				</table>
			</div>
		</form>
	</div>
</body>
</html>