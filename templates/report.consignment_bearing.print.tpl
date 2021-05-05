{*
7/15/2011 2:58:49 PM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.
*}
{if !$skip_header}
{include file='header.print.tpl'}

{literal}



<style>
.report_table , .report_table th, .report_table td{
	border: 1px solid black;
	border-collapse:collapse;
}

.r{
	text-align:right;
}

.bold{
	font-weight:bold;
}

.detail_width{
	width: 140px;
}


.summary_top{
	width: 95px;
}

.left{
	float: Left;
}

.right{
	float: Right;
}
</style>
{/literal}

<body onload="window.print()">
{/if}


<h2>{$report_title}</h2>


{if $table}
	{$report_cache}
{else}
	-- No Data --
{/if}
