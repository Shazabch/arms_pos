<table class=tb border=0 cellspacing=0 cellpadding=2 id=table_annual width=100%>
<tr bgcolor=#ffee99>
<th rowspan=3>Month</th>
<th rowspan=2 colspan=2>Total Target</th>
<th rowspan=3>Actual<br>Sales</th>
<th rowspan=2 colspan=2>Variant</th>

{section name=k loop=$category}
{assign var=p value=$smarty.section.k.iteration}
{/section}
{assign var=p value=$p*3}
<th colspan="{$p}">Line Contribution</th>
</tr>

<tr bgcolor=#ffee99>
{section name=k loop=$category}
<th colspan=3>{$category[k].description}</th>
{/section}
</tr>


<tr bgcolor=#ffee99>
<th>Target</th>
<th>Adjustment</th>
<th>By Month</th>
<th>Accumulate</th>
{section name=k loop=$category}
{assign var=p value=$smarty.section.k.iteration}
<th width=50>%</th>
<th>Target<br>(RM)</th>
<th>Actual<br>(RM)</th>
{/section}
</tr>

{assign var=t_target value=0}
{assign var=t_target_2 value=0}
{assign var=t_forecast value=0}
{assign var=t_forecast_2 value=0}
{assign var=t_sales value=0}

{assign var=total_by_month value=0}
{assign var=accumulate value=0}

{section name=m loop=$arr_month start=$config.mkt_started_month-1}
{assign var=m value=$smarty.section.m.index+1}
{assign var=y value=$year}

{assign var=t_target value=$t_target+$line.$y.$m.total_target}

{if $line.$y.$m.adjustment}
{assign var=t_target_2 value=$t_target_2+$line.$y.$m.adjustment}
{else}
{*assign var=t_target_2 value=$t_target_2+$line.$y.$m.target*}
{/if}

{assign var=t_forecast value=$t_forecast+$line.$y.$m.total_forecast}
{assign var=t_forecast_2 value=$t_forecast_2+$line.$y.$m.forecast}
{assign var=t_sales value=$t_sales+$line.$y.$m.sales}
<tr>
<th align=left><a href=/mkt_review.php?a=search&month={$m}&year={$y}&branch_id={$branch_id}&original_y={$year}>{$arr_month[$m]}-{$y}</a></th>

<td id="total_target_{$m}" class={if !$approve}"keyin total adjust"{else}"display total adjust"{/if} title="t_t,{$m},,{$y}" {if !$approve and $mkt_annual_privilege.MKT_ANNUAL_EDIT.$branch_id}onclick="do_edit(this)"{/if}>
{$line.$y.$m.total_target|default:"&nbsp;"|number_format:2}</td>

<td id="adjustment_{$m}" title="adjust,{$m},,{$y}"  class="keyin total adjust"  {if $mkt_annual_privilege.MKT_ANNUAL_EDIT.$branch_id}onclick="do_edit(this);"{/if}>
{$line.$y.$m.adjustment|default:$line.$y.$m.target|number_format:2}</td>

<!--Actual Sales -->
<td id="sales_{$m}" class="{if $line.$y.$m.sales<$line.$y.$m.total_target}negative{elseif $line.$y.$m.sales==$line.$y.$m.total_target}zero{else}positive{/if} total adjust">
{$line.$y.$m.sales|default:"&nbsp;"|number_format:2}</td>

{assign var=target_p_adjust value=$line.$y.$m.total_target+$line.$y.$m.adjustment}
{assign var=b_month value=$target_p_adjust-$line.$y.$m.sales}

{assign var=accumulate value=$accumulate+$b_month}
<!--By Month-->
<td id="by_month_{$m}" class="{if $b_month<0}negative{elseif $b_month==0}zero{else}positive{/if} total adjust" title="t_f,{$m},,{$y}">
{$b_month|default:"&nbsp;"|number_format:2}
</td>

<!--Accumulate-->
<td id="accumulate_{$m}" class="{if $accumulate<0}negative{elseif $accumulate==0}zero{else}positive{/if} total adjust" title="t_f,{$m},,{$y}">
{$accumulate|default:"&nbsp;"|number_format:2}
</td>

<!--td class="{if $line.$y.$m.sales<$line.$y.$m.total_target}negative{elseif $line.$y.$m.sales==$line.$y.$m.total_target}zero{else}positive{/if}">
{$line.$y.$m.sales|default:"&nbsp;"|number_format:2}</td>

<td id="total_forecast_{$m}" class={if !$approve}"keyin forecast"{else}"display forecast"{/if} title="t_f,{$m},,{$y}"  {if !$approve and $mkt_annual_privilege.MKT_ANNUAL_EDIT.$branch_id}onclick="do_edit(this)"{/if}>
{$line.$y.$m.total_forecast|default:"&nbsp;"|number_format:2}
</td>

<td class="{if $line.$y.$m.forecast<0}negative{elseif $line.$y.$m.forecast==0}zero{else}positive{/if}">
{$line.$y.$m.forecast|default:"&nbsp;"|number_format:2}</td-->


{section name=k loop=$category}
{assign var=dept value=$category[k].id}
{assign var=amt value=$line.$y.$m.$dept.amt*$line.$y.$m.total_target/100}


<td id="line_contribute_{$category[k].id}_{$m}" class="keyin total" title="l_c,{$m},{$category[k].id},{$y}"  {if $mkt_annual_privilege.MKT_ANNUAL_EDIT.$branch_id}onclick="do_edit(this)"{/if}>
{$line.$y.$m.$dept.amt|default:"&nbsp;"|number_format:2}%</td>

<td id="line_amt_{$category[k].id}_{$m}" title=",{$m},{$category[k].id}" class="{if $amt<0}negative{elseif $amt==0}zero{else}positive{/if} total">
{$amt|number_format:2}</td>

<td class="{if $line.$y.$m.$dept.act<0}negative{elseif $line.$y.$m.$dept.act==0}zero{else}positive{/if}">
{$line.$y.$m.$dept.act|default:"&nbsp;"|number_format:2}</td>
{/section}
</tr>
{/section}


{section name=m loop=$arr_month max=$config.mkt_started_month-1}
{assign var=m value=$smarty.section.m.index+1}
{assign var=y value=$year+1}

{assign var=t_target value=$t_target+$line.$y.$m.total_target}

{if $line.$y.$m.adjustment}
{assign var=t_target_2 value=$t_target_2+$line.$y.$m.adjustment}
{else}
{*assign var=t_target_2 value=$t_target_2+$line.$y.$m.target*}
{/if}

{assign var=t_forecast value=$t_forecast+$line.$y.$m.total_forecast}
{assign var=t_forecast_2 value=$t_forecast_2+$line.$y.$m.forecast}
{assign var=t_sales value=$t_sales+$line.$y.$m.sales}
<tr>

<th align=left><a href=/mkt_review.php?a=search&month={$m}&year={$y}&branch_id={$branch_id}&original_y={$year}>{$arr_month[$m]}-{$y}</a></th>

<td id="total_target_{$m}" class={if !$approve}"keyin total adjust"{else}"display total adjust"{/if} title="t_t,{$m},,{$y}" {if !$approve and $mkt_annual_privilege.MKT_ANNUAL_EDIT.$branch_id}onclick="do_edit(this)"{/if}>
{$line.$y.$m.total_target|default:"&nbsp;"|number_format:2}</td>

<td id="adjustment_{$m}" title="adjust,{$m},,{$y}"  class="keyin total adjust"  {if $mkt_annual_privilege.MKT_ANNUAL_EDIT.$branch_id}onclick="do_edit(this);"{/if}>
{$line.$y.$m.adjustment|default:$line.$y.$m.target|number_format:2}</td>

<!--Actual Sales -->
<td id="sales_{$m}" class="{if $line.$y.$m.sales<$line.$y.$m.total_target}negative{elseif $line.$y.$m.sales==$line.$y.$m.total_target}zero{else}positive{/if} total adjust">
{$line.$y.$m.sales|default:"&nbsp;"|number_format:2}</td>

{assign var=target_p_adjust value=$line.$y.$m.total_target+$line.$y.$m.adjustment}
{assign var=b_month value=$target_p_adjust-$line.$y.$m.sales}

{assign var=accumulate value=$accumulate+$b_month}
<!--By Month-->
<td id="by_month_{$m}" class="{if $b_month<0}negative{elseif $b_month==0}zero{else}positive{/if} total adjust" title="t_f,{$m},,{$y}">
{$b_month|default:"&nbsp;"|number_format:2}
</td>

<!--Accumulate-->
<td id="accumulate_{$m}" class="{if $accumulate<0}negative{elseif $accumulate==0}zero{else}positive{/if} total adjust" title="t_f,{$m},,{$y}">
{$accumulate|default:"&nbsp;"|number_format:2}
</td>

<!--td class="{if $line.$y.$m.sales<$line.$y.$m.total_target}negative{elseif $line.$y.$m.sales==$line.$y.$m.total_target}zero{else}positive{/if}">
{$line.$y.$m.sales|default:"&nbsp;"|number_format:2}</td>

<td id="total_forecast_{$m}" class={if !$approve}"keyin forecast"{else}"display forecast"{/if} title="t_f,{$m},,{$y}"  {if !$approve and $mkt_annual_privilege.MKT_ANNUAL_EDIT.$branch_id}onclick="do_edit(this)"{/if}>
{$line.$y.$m.total_forecast|default:"&nbsp;"|number_format:2}
</td>

<td class="{if $line.$y.$m.forecast<0}negative{elseif $line.$y.$m.forecast==0}zero{else}positive{/if}">
{$line.$y.$m.forecast|default:"&nbsp;"|number_format:2}</td-->


{section name=k loop=$category}
{assign var=dept value=$category[k].id}
{assign var=amt value=$line.$y.$m.$dept.amt*$line.$y.$m.total_target/100}


<td id="line_contribute_{$category[k].id}_{$m}" class="keyin total" title="l_c,{$m},{$category[k].id},{$y}"  {if $mkt_annual_privilege.MKT_ANNUAL_EDIT.$branch_id}onclick="do_edit(this)"{/if}>
{$line.$y.$m.$dept.amt|default:"&nbsp;"|number_format:2}%</td>

<td id="line_amt_{$category[k].id}_{$m}" title=",{$m},{$category[k].id}" class="{if $amt<0}negative{elseif $amt==0}zero{else}positive{/if} total">
{$amt|number_format:2}</td>

<td class="{if $line.$y.$m.$dept.act<0}negative{elseif $line.$y.$m.$dept.act==0}zero{else}positive{/if}">
{$line.$y.$m.$dept.act|default:"&nbsp;"|number_format:2}</td>
{/section}
</tr>
{/section}


<tr bgcolor=#ffee99>
<th>Total<br>(RM)</th>
<th id="t_target" class="{if $t_target<0}negative{elseif $t_target==0}zero{else}positive{/if}">
{$t_target|number_format:2}
</th>
<th id="t_adjust" class="{if $t_target_2<0}negative{elseif $t_target_2==0}zero{else}positive{/if}">
{$t_target_2|number_format:2}
</th>
<th class="{if $t_sales<0}negative{elseif $t_sales==0}zero{else}positive{/if}">
{$t_sales|number_format:2}
</th>
<!--th id="t_forecast" class="{if $t_forecast<0}negative{elseif $t_forecast==0}zero{else}positive{/if}">
{$t_forecast|number_format:2}
</th-->
{assign var=t_target_p_adjust value=$t_target+$t_target_2}
{assign var=t_b_month value=$t_target_p_adjust-$t_sales}

<th id="t_by_month" class="{if $t_b_month<0}negative{elseif $t_b_month==0}zero{else}positive{/if}">
{$t_b_month|number_format:2}
</th>

<!--th class="{if $t_forecast_2<0}negative{elseif $t_forecast_2==0}zero{else}positive{/if}">
{$t_forecast_2|number_format:2}
</th-->

<th class="{if $t_forecast_2<0}negative{elseif $t_forecast_2==0}zero{else}positive{/if}">
&nbsp;
</th>


{section name=k loop=$category}
{assign var=dept value=$category[k].id}


<th id="t_pct_{$dept}" class="{if $line.total_pct.$dept<0}negative{elseif $line.total_pct.$dept==0}zero{else}positive{/if}">
{$line.total_pct.$dept|number_format:2}%
</td>

<th id="t_amt_{$dept}" class="{if $line.total_amt.$dept<0}negative{elseif $line.total_amt.$dept==0}zero{else}positive{/if} total" title=",,{$dept}">
{$line.total_amt.$dept|number_format:2}
</td>

<th class="{if $line.total_act.$dept<0}negative{elseif $line.total_act.$dept==0}zero{else}positive{/if}">
{$line.total_act.$dept|default:"&nbsp;"|number_format:2}
</td>
{/section}
</tr>
</table>

<script>
</script>
