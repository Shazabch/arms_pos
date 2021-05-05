<table class=tb border=0 cellspacing=0 cellpadding=2 id=table_sheet>
<tr bgcolor=#ffee99>
<th colspan=3 rowspan=4>Date</th>
<th colspan=2>Whole Store Contribution</th>
<th colspan=2>Target</th>
<th colspan=3 rowspan=3>Sales Achievement</th>
<th class="border">&nbsp;</th>

{*if $smarty.request.ajax*}
{if $subcat}
<input type=hidden name=id value={$cat.id}>
<th colspan=2 width=200 bgcolor=#ccdd88>{$cat.description}</th>
<th colspan=3 width=300 bgcolor=#ccdd88>Contribution</th>
<th class="small_border">&nbsp;</th>
<th colspan=3 width=300 bgcolor=#ccdd88>Total Line Target</th>
<th class="border">&nbsp;</th>
{section name=sc loop=$subcat start=1 step=1}
<th colspan=9 rowspan=2 bgcolor={cycle name=deptcolor1 values="#88ddcc,#ccdd88"}>&nbsp;</th>
<th class="border" rowspan=2>&nbsp;</th>
{/section}
{/if}
{*/if*}
</tr>


<tr bgcolor=#ffee99>
<th colspan=2>100%</th>
{assign var=total value=$list.total_target+$list.adjustment}
<td id="total_target" class=display colspan=2 title="t_t,,">
{$total|default:"&nbsp;"|number_format:2}
</td>
<th class="border">&nbsp;</th>

{*if $smarty.request.ajax*}
{if $subcat}
<td colspan=2 bgcolor=#ccdd88>&nbsp;</td>
<td id="total_line_con" colspan=3 class="display con_row_normal con_row_sales con_table" title="l_c,,">
{$line[$cat.id]|default:"&nbsp;"|number_format:2}%
</td>
<th class="small_border">&nbsp;</th>
<td id="subline_con" colspan=3 class="display dept_group">
{$line[$cat.id]*$total/100|default:"&nbsp;"|number_format:2}
</td>
<th class="border">&nbsp;</th>
{*/if*}
{/if}
</tr>

<tr bgcolor=#ffee99>
<th rowspan=2>Normal<br>Forecast</th>
<th rowspan=2>Sales<br>Target</th>
<th colspan=2>Additional</th>
<th class="border">&nbsp;</th>

{*if $smarty.request.ajax*}
{section name=sc loop=$subcat}
{assign var=s value=$smarty.section.sc.iteration-1}
{assign var=y value=0}
{assign var=temp_id value=$subcat.$s.id}
{capture assign=curr_bg}{cycle name=deptcolor3 values="#ccdd88,#88ddcc"}{/capture}
<th bgcolor={$curr_bg}><font color=red>{$subcat[sc].description}</font></th>
<td class="display dept_group" id="total_dept_amt_{$temp_id}" title="{$temp_id}">
{$line[$cat.id]*$total/100*$dept.$temp_id.$y.total_contribute/100|default:"&nbsp;"|number_format:2}
</td>
<th colspan=2 bgcolor={$curr_bg}>Additional</th>
<th bgcolor={$curr_bg}>Contribution</th>
<th class="small_border">&nbsp;</th>
<th colspan=3 bgcolor={$curr_bg}>Actual Sales</th>
<th class="border">&nbsp;</th>
{/section}
{*/if*}
</tr>


<tr bgcolor=#ffee99>
<th>Amount</th>
<th>%</th>
<th>Amount</th>
<th>%</th>
<th>Variant</th>
<th class="border">&nbsp;</th>


{*if $smarty.request.ajax*}
{section name=sc loop=$subcat}
{assign var=s value=$smarty.section.sc.iteration-1}
{assign var=y value=0}
{assign var=temp_id value=$subcat.$s.id}
{capture assign=curr_bg}{cycle name=deptcolor4 values="#ccdd88,#88ddcc"}{/capture}
<th bgcolor={$curr_bg}>Normal</th>
<th bgcolor={$curr_bg}>Target</th>
<th bgcolor={$curr_bg}>Amount</th>
<th bgcolor={$curr_bg}>%</th>
<td id="con_dept_{$temp_id}" class="dept_group keyin con_row_normal con_table" title="t_c,0,{$temp_id}" {if $mkt_review_privilege.MKT_REVIEW_EDIT.$branch_id}onclick="do_edit(this)"{/if}>
{$dept.$temp_id.$y.total_contribute|default:"&nbsp;"|number_format:2}%</td>
<th class="small_border">&nbsp;</th>
<th bgcolor={$curr_bg}>Amount</th>
<th bgcolor={$curr_bg}>%</th>
<th bgcolor={$curr_bg}>variant</th>
<th class="border">&nbsp;</th>
{/section}
<div id=reload>
{include file=mkt_review.reload.tpl}
</div>
{*/if*}

</tr>

{assign var=t_normal value=0}
{assign var=t_sales value=0}
{assign var=t_amount value=0}
{assign var=t_pct value=0}
{assign var=t_s_a value=0}
{assign var=t_sa_pct value=0}
{assign var=t_variant value=0}

{assign var=t_w_normal value=0}
{assign var=t_w_sales value=0}
{assign var=t_w_amount value=0}
{assign var=t_w_pct value=0}
{assign var=t_w_s_a value=0}
{assign var=t_w_sa_pct value=0}
{assign var=t_w_variant value=0}

{assign var=w value=0}
{section name=loopi loop=$i start=1 step=1}
{assign var=n value=$smarty.section.loopi.iteration}

{assign var=t_normal value=$t_normal+$list.$n.normal_forecast}
{assign var=t_w_sales value=$t_w_sales+$list.$n.sales_target}
{assign var=t_w_s_a value=$t_w_s_a+$list.$n.sales_achieve}

{assign var=t_w_normal value=$t_w_normal+$list.$n.normal_forecast}
{assign var=t_sales value=$t_sales+$list.$n.sales_target}
{assign var=t_s_a value=$t_s_a+$list.$n.sales_achieve}

{assign var=amount value=$list.$n.sales_target-$list.$n.normal_forecast}
{assign var=t_amount value=$t_amount+$amount}

{assign var=t_w_amount value=$t_w_amount+$amount}

{if $list.$n.normal_forecast>0}
{assign var=pct value=$amount*100/$list.$n.normal_forecast}
{else}
{assign var=pct value=0}
{/if}

{if $t_w_normal>0}
{assign var=t_w_pct value=$t_w_amount*100/$t_w_normal}
{else}
{assign var=t_w_pct value=0}
{/if}

{if $t_w_sales>0}
{assign var=t_w_sa_pct value=$t_w_s_a*100/$t_w_sales}
{else}
{assign var=t_w_sa_pct value=0}
{/if}

{if $list.$n.sales_target>0}
{assign var=s_a_pct value=$list.$n.sales_achieve/$list.$n.sales_target*100}
{else}
{assign var=s_a_pct value=0}
{/if}

{assign var=s_a_variant value=$list.$n.sales_achieve-$list.$n.sales_target}
{assign var=t_variant value=$t_variant+$s_a_variant}

{assign var=t_w_variant value=$t_w_variant+$s_a_variant}


<tr id=tr_{$n}>
<th align=left {if $list.$n.promote_status eq '1'}class="promote"{/if}> {$showday[$n]}</td>
<th align=left {if $list.$n.promote_status eq '1'}class="promote"{/if}> {$m} {$n} </td>
<th align=left {if $list.$n.promote_status eq '1'}class="promote"{/if}> {$year}</td>

<td id=normal_{$n} title="n,{$n}," {if $mkt_review_privilege.MKT_REVIEW_EDIT.$branch_id}onclick="do_edit(this)"{/if} class="keyin master_week_row con_row_normal con_table">
{$list.$n.normal_forecast|default:"&nbsp;"|number_format:2}
</td>

<td id=sales_{$n} title="s,{$n}," {if $mkt_review_privilege.MKT_REVIEW_EDIT.$branch_id}onclick="do_edit(this)"{/if} class="keyin master_week_row con_row_sales con_table">
{$list.$n.sales_target|default:$list.$n.normal_forecast|number_format:2}
</td>

<td id=amount_{$n} class="{if $amount<0}negative{elseif $amount==0}zero{else}positive{/if} master_week_row">
{$amount|default:"&nbsp;"|number_format:2}
</td>

<td id=pct_{$n}  class="{if $pct<0}negative{elseif $pct==0}zero{else}positive{/if}">
{$pct|default:"&nbsp;"|number_format:2}%
</td>

<td id=s_a_{$n} title="s_a,{$n}," {if $mkt_review_privilege.MKT_REVIEW_EDIT.$branch_id}onclick="do_edit(this)"{/if} class="optional master_week_row">
{$list.$n.sales_achieve|default:"&nbsp;"|number_format:2}
</td>
<td id=sa_pct_{$n}  class="{if $s_a_pct<0}negative{elseif $s_a_pct==0}zero{else}positive{/if}">
{$s_a_pct|default:"&nbsp;"|number_format:2}%
</td>

<td id=variant_{$n} class="{if $s_a_variant<0}negative{elseif $s_a_variant==0}zero{else}positive{/if} master_week_row">
{$s_a_variant|default:"&nbsp;"|number_format:2}
</td>
<th class="border">&nbsp;</th>


{*if $smarty.request.ajax*}
{section name=sc loop=$subcat}
{assign var=s value=$smarty.section.sc.iteration-1}
{assign var=y value=0}
{assign var=temp_id value=$subcat.$s.id}

<td id="con_normal_{$temp_id}" title=",{$n},{$temp_id}" class="{if $con_normal<0} negative {elseif $con_normal==0}zero{else}positive{/if} con_row_normal con_week_normal con_row_sales con_table">&nbsp;</td>

<td id="con_sales_{$temp_id}" title=",{$n},{$temp_id}" class="{if $con_sales<0} negative {elseif $con_sales==0}zero{else}positive{/if} con_row_normal con_row_sales con_week_sales con_table sales_amt_group con_week_normal con_sales_amt">&nbsp;</td>

<td id="con_amt_{$temp_id}" title=",{$n},{$temp_id}" class="{if $con_amt<0} negative {elseif $con_amt==0}zero{else}positive{/if} con_row_normal con_week_normal con_row_sales con_week_sales con_table">&nbsp;</td>

<td id="con_pct_{$temp_id}" title=",{$n},{$temp_id}" class="{if $con_pct<0} negative {elseif $con_pct==0}zero{else}positive{/if} con_row_normal con_row_sales con_table">&nbsp;</td>

<td id="con_dept_{$temp_id}" class="optional con_row_normal con_row_sales con_table con_week_normal" title="t_c,{$n},{$temp_id}" {if $mkt_review_privilege.MKT_REVIEW_EDIT.$branch_id}onclick="do_edit(this)"{/if}>
{$dept.$temp_id.$n.total_contribute|default:$dept.$temp_id.$y.total_contribute|default:"&nbsp;"|number_format:2}%
</td>

<th class="small_border">&nbsp;</th>

<td id="con_dept_amt_{$temp_id}" class="optional con_row_sales con_table con_week_normal sales_amt_group con_sales_amt con_week_sales" title="a_s_a,{$n},{$temp_id}" {*if $mkt_review_privilege.MKT_REVIEW_EDIT.$branch_id}onclick="do_edit(this)"{/if*}>
{$real_sales.$temp_id.$n.amt|default:$dept.$temp_id.$n.sales_amount|default:"&nbsp;"|number_format:2}
</td>
<!--$dept.$temp_id.$n.sales_amount-->

<td id="con_s_a_pct_{$temp_id}" title=",{$n},{$temp_id}" class="{if $con_s_a_pct<0} negative {elseif $con_s_a_pct==0}zero{else}positive{/if} con_row_sales con_table sales_amt_group">&nbsp;</td>

<td id="con_variant_{$temp_id}" title=",{$n},{$temp_id}" class="{if $con_variant<0} negative {elseif $con_variant==0}zero{else}positive{/if} con_row_sales con_week_sales con_table sales_amt_group con_sales_amt">&nbsp;</td>

<th class="border">&nbsp;</th>
{/section}
{*/if*}

</tr>


{if $showday[$n] eq 'Sunday' or $smarty.section.loopi.last}
<!-- {$w++} -->
<tr class="weekly">
<th colspan=3 align=center>Weekly Total</th>
<td id=total_w_normal_{$w} class="{if $t_w_normal<0}negative{elseif $t_w_normal==0}zero{else}positive{/if} master_week_row">
{$t_w_normal|default:"&nbsp;"|number_format:2}
</td>

<td id=total_w_sales_{$w} class="{if $t_w_sales<0}negative{elseif $t_w_sales==0}zero{else}positive{/if} master_week_row">
{$t_w_sales|default:"&nbsp;"|number_format:2}
</td>

<td id=total_w_amount_{$w} class="{if $t_w_amount<0}negative{elseif $t_w_amount==0}zero{else}positive{/if} master_week_row">
{$t_w_amount|default:"&nbsp;"|number_format:2}
</td>

<td id=total_w_pct_{$w} class="{if $t_w_pct<0}negative{elseif $t_w_pct==0}zero{else}positive{/if} master_week_row">
{$t_w_pct|default:"&nbsp;"|number_format:2}%
</td>

<td id=total_w_s_a_{$w} class="{if $t_w_s_a<0}negative{elseif $t_w_s_a==0}zero{else}positive{/if} master_week_row">
{$t_w_s_a|default:"&nbsp;"|number_format:2}
</td>

<td id=total_w_sa_pct_{$w} class="{if $t_w_sa_pct<0}negative{elseif $t_w_sa_pct==0}zero{else}positive{/if} master_week_row">
{$t_w_sa_pct|default:"&nbsp;"|number_format:2}%
</td>

<td id=total_w_variant_{$w} class="{if $t_w_variant<0}negative{elseif $t_w_variant==0}zero{else}positive{/if} master_week_row">
{$t_w_variant|default:"&nbsp;"|number_format:2}
</td>
<th class="border">&nbsp;</th>


{*if $smarty.request.ajax*}
{section name=sc loop=$subcat}
{assign var=s value=$smarty.section.sc.iteration-1}
{assign var=temp_id value=$subcat.$s.id}
<td id=week_con_normal_{$temp_id} title="{$temp_id}" class="con_week_normal">&nbsp;</td>
<td id=week_con_sales_{$temp_id} title="{$temp_id}" class="con_week_sales">&nbsp;</td>
<td id=week_con_amount_{$temp_id} title="{$temp_id}" class="con_week_normal con_week_sales">&nbsp;</td>
<td id=week_con_pct_{$temp_id} title="{$temp_id}" class="con_week_normal con_week_sales">&nbsp;</td>

<td id=week_con_dept_{$temp_id} title="{$temp_id}" class="con_week_normal con_week_sales con_table">&nbsp;</td>

<th class="small_border">&nbsp;</th>
<td id=week_con_sa_amount_{$temp_id} title="{$temp_id}" class="con_week_normal con_sales_amt">&nbsp;</td>

<td id=week_con_sa_pct_{$temp_id} title="{$temp_id}" class="con_week_sales con_week_normal con_sales_amt">&nbsp;</td>

<td id=week_con_variant_{$temp_id} title="{$temp_id}" class="con_week_sales con_sales_amt">&nbsp;</td>
<th class="border">&nbsp;</th>
{/section}
{*/if*}
</tr>
{assign var=t_w_normal value=0}
{assign var=t_w_sales value=0}
{assign var=t_w_amount value=0}
{assign var=t_w_pct value=0}
{assign var=t_w_s_a value=0}
{assign var=t_w_sa_pct value=0}
{assign var=t_w_variant value=0}
{assign var=t_w_dept_con value=0}
{/if}
{/section}

{if $t_normal>0}
{assign var=t_pct value=$t_amount*100/$t_normal}
{else}
{assign var=t_pct value=0}
{/if}
{if $t_sales>0}
{assign var=t_sa_pct value=$t_s_a*100/$t_sales}
{else}
{assign var=t_sa_pct value=0}
{/if}
<tr class="monthly">
<th colspan=3 align=center>Monthly Total</td>
<th id=total_normal class="{if $t_normal<0} negative {elseif $t_normal==0}zero{else}positive{/if}">
{$t_normal|default:"&nbsp;"|number_format:2}
</td>

<th id=total_sales class="{if $t_sales<0} negative {elseif $t_sales==0}zero{else}positive{/if}">
{$t_sales|default:"&nbsp;"|number_format:2}
</td>

<th id=total_amount class="{if $t_amount<0} negative {elseif $t_amount==0}zero{else}positive{/if}">
{$t_amount|default:"&nbsp;"|number_format:2}
</td>

<th id=total_pct class="{if $t_pct<0} negative {elseif $t_pct==0}zero{else}positive{/if}">
{$t_pct|default:"&nbsp;"|number_format:2}%
</td>

<th id=total_s_a class="{if $t_s_a<0} negative {elseif $t_s_a==0}zero{else}positive{/if}">
{$t_s_a|default:"&nbsp;"|number_format:2}
</td>

<th id=total_sa_pct class="{if $t_sa_pct<0} negative {elseif $t_sa_pct==0}zero{else}positive{/if}">
{$t_sa_pct|default:"&nbsp;"|number_format:2}%
</td>

<th id=total_variant class="{if $t_variant<0} negative {elseif $t_variant==0}zero{else}positive{/if}">
{$t_variant|default:"&nbsp;"|number_format:2}
</td>
<th class="border">&nbsp;</th>


{*if $smarty.request.ajax*}
{section name=sc loop=$subcat}
{assign var=s value=$smarty.section.sc.iteration-1}
{assign var=temp_id value=$subcat.$s.id}
<td id=total_con_normal_{$temp_id} title="{$temp_id}" class="con_week_normal">&nbsp;</td>
<td id=total_con_sales_{$temp_id} title="{$temp_id}" class="con_week_sales">&nbsp;</td>
<td id=total_con_amount_{$temp_id} title="{$temp_id}" class="con_week_normal con_week_sales">&nbsp;</td>

<td id=total_con_pct_{$temp_id} title="{$temp_id}" class="con_week_normal con_week_sales">&nbsp;</td>

<td id=total_con_dept_{$temp_id} title="{$temp_id}" class="con_table con_week_normal con_week_sales">&nbsp;</td>

<th class="small_border">&nbsp;</th>
<td id=total_con_sa_amount_{$temp_id} title="{$temp_id}" class="con_week_normal con_sales_amt">&nbsp;</td>

<td id=total_con_sa_pct_{$temp_id} title="{$temp_id}" class="con_week_sales con_week_normal con_sales_amt">&nbsp;</td>

<td id=total_con_variant_{$temp_id} title="{$temp_id}" class="con_week_sales con_sales_amt">&nbsp;</td>
<th class="border">&nbsp;</th>
{/section}
{*/if*}
</tr>

<tr bgcolor=#ffee99>
<th colspan=3 align=center>&nbsp;</td>
<th>Normal<br>Forecast</th>
<th>Sales<br>Target</th>
<th>Additional<br>Amount</th>
<th>&nbsp;</th>
<th>S/A<br>Amount</th>
<th>&nbsp;</th>
<th>Variant</th>
<th class="border">&nbsp;</th>

{*if $smarty.request.ajax*}
{section name=sc loop=$subcat}
{assign var=s value=$smarty.section.sc.iteration-1}
{assign var=temp_id value=$subcat.$s.id}
{capture assign=curr_bg}{cycle name=deptcolor values="#ccdd88,#88ddcc"}{/capture}
<th  bgcolor={$curr_bg}>Normal</th>
<th bgcolor={$curr_bg}>Sales</th>
<th bgcolor={$curr_bg}>Amount</th>
<th bgcolor={$curr_bg}>&nbsp;</th>
<th bgcolor={$curr_bg}>&nbsp;</th>
<th class="small_border">&nbsp;</th>
<th bgcolor={$curr_bg}>&nbsp;</th>
<th bgcolor={$curr_bg}>&nbsp;</th>
<th bgcolor={$curr_bg}>Variant</th>
<th class="border">&nbsp;</th>
{/section}
{*/if*}
</tr>
</table>


<script>
update_con_table();
update_con_normal_week_row();
update_con_sales_week_row();
</script>
