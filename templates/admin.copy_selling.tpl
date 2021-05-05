{*
8/6/2010 12:17:28 PM yinsee
- add resync reminder

12/9/2010 3:42:24 PM Andy
- Add department filter. (base on user department privilege)

3/22/2013 12:27 PM Justin
- Enhanced to allow user add additional selling price.
*}
{include file=header.tpl}

<h1>{$PAGE_TITLE}</h1>
<p style="color:blue">Note: You need to copy selling price before setup counter. If counter is already setup, please resync master file.</p>
<form name=f1 method=post onsubmit="return confirm('Are you sure?')">
<input type=hidden name="a" value="copy_selling">
<b>From</b> {dropdown values=$branches key=id value=code name=from_branch}
<b>To</b> {dropdown values=$branches key=id value=code name=to_branch}
&nbsp;&nbsp;&nbsp;&nbsp;
<b>Department</b> {dropdown values=$depts key=id value=description name=dept_id all='-- All --'}
&nbsp;&nbsp;&nbsp;&nbsp;<input type=checkbox name="clear" {if $smarty.request.clear}checked {/if} /> Clear existing selling price
{if $config.masterfile_branch_enable_additional_sp}
&nbsp;&nbsp;&nbsp;&nbsp;<input type=checkbox name="clear" {if $smarty.request.clear}checked {/if} /> Add additional selling price
{/if}
&nbsp;&nbsp;&nbsp;&nbsp;<input type=submit value="Copy" />

{if $msg}
<p>{$msg}</p>
{/if}
{include file=footer.tpl}
