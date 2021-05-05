{if !$skip_header}
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<link rel="stylesheet" type="text/css" href="templates/print.css">

<body onload="window.print()">
{/if}
{literal}
<style>
#tbl_sr td, #tbl_sr th {
	border-right:1px solid #ccc;
	border-bottom:1px solid #ccc;
}
#tbl_sr td.shaded {
	background:#eee;
	color:#aaa;
	font-size:10px;
	text-align:center;
}
.normal{
	color:black;
	text-align:center
}
.cr{
	color:red;
	text-align:center
}
.cb{
	color:blue;
	text-align:center
}
</style>
{/literal}

<div class=printarea>

<h1>{$PAGE_TITLE}</h1><div align=right>{$page}</div>

<h4>Date : <font color=blue>{if $month == 1}January / {$year}{/if}
{if $month == 2}February / {$year}{/if}
{if $month == 3}March / {$year}{/if}
{if $month == 4}April / {$year}{/if}
{if $month == 5}May / {$year}{/if}
{if $month == 6}June / {$year}{/if}
{if $month == 7}July / {$year}{/if}
{if $month == 8}August / {$year}{/if}
{if $month == 9}September / {$year}{/if}
{if $month == 10}October / {$year}{/if}
{if $month == 11}November / {$year}{/if}
{if $month == 12}December / {$year}{/if}
</font>
&nbsp;&nbsp;&nbsp;

Branch
{foreach item="curr_Branch" from=$BranchArray}
{if $curr_Branch.id==$branch} : <font color=blue>{$curr_Branch.code}{/if}</font>
{/foreach}

<br>
Department : <font color=blue size="1px">{$dept}</font>
</h4>

<table cellpadding=1 cellspacing=0 border=0 id=tbl_sr style="border:1px solid #000;padding:0px;" width=100%>
<tr bgcolor=#ffee99>
<th><b>No</th>
<th>Employee Name</th>
<th>Employee ID</th>

{section name=loopi loop=$i start=1 step=1}
<th>{$smarty.section.loopi.iteration}<br>{$showday[loopi][0]}</th>
{/section}
</tr>


{if $list}
{foreach name=j item="curr_list" from=$list}
{assign var=n value=$smarty.foreach.j.iteration}
{if (($n%2)==1)}<tr bgcolor=#ffffff>{else}<tr bgcolor=#eeeeee>{/if}
<th>{$smarty.foreach.j.iteration}</th>
<td>{$curr_list.employee_name}</td>
<td>{$curr_list.employee_id}</td>

{section name=loopi loop=$i start=1 step=1}
<td align=center>
{assign var=ii value=$smarty.section.loopi.iteration}
{assign var=arrayname value=estimate_day_record_$ii}
 {if $curr_list.$arrayname eq 'R'}<font color=red>{else}<font color=black>{/if}
{$curr_list.$arrayname|default:"&nbsp;"}
</td>
{/section}
</tr>

{if (($n%2)==1)}<tr bgcolor=#ffffff>{else}<tr bgcolor=#eeeeee>{/if}
<td>&nbsp;</td>
{if $department eq '%%'}
<td colspan=2>{$curr_list.department|default:"&nbsp;"} &nbsp;&nbsp; {$curr_list.brand|default:"&nbsp;"}</td>
{elseif $department eq 'Promoter (Hard line)' || $department eq 'Promoter (Soft line)' || $department eq 'Promoter (Supermarket line)'}
<td colspan=2>Brand : {$curr_list.brand|default:"&nbsp;"}</td>
{else}
<td colspan=2 class=shaded>Actual Attendance</td>
{/if}

{section name=loopi loop=$i start=1 step=1}
<td align=center>
{assign var=ii value=$smarty.section.loopi.iteration}
{assign var=arrayname value=day_record_$ii} {assign var=arrayEday value=estimate_day_record_$ii}
{if $curr_list.$arrayname eq $curr_list.$arrayEday && $curr_list.$arrayname eq 'R'}<font color=red> {elseif $curr_list.$arrayname eq $curr_list.$arrayEday}<font color=black>{else}<font color=blue>{/if}
{$curr_list.$arrayname|default:"&nbsp;"}
</td>
{/section}
</tr>
{/foreach}
{/if}
</table>
</div>

