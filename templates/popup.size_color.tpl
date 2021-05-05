{literal}
<style>
.input_matrix [alt="size"] { /* size */
	background-color:#9f9;
	font-weight:bold;
	border:1px solid black;
}
.input_matrix  [alt="color"] { /* color */
	text-align:center;
	background-color:#9f9;
	font-weight:bold;
	border:1px solid black;
}
.input_matrix td {
	border:1px solid black;
	width: 50;
}
.input_matrix [alt="data"] {    /* stock balance*/
	background-color:#eee;
	border:none;
}

.input_matrix [alt="no_data"] {    /*no data*/
	background-color:#fff;
	border:none;
}


</style>
{/literal}


<div id="div_size_color" style="background:#fff;border:3px solid #000;width:800px;height:480px;position:absolute; padding:10px; display:none;">

<div style="text-align:right"><img src=/ui/closewin.png onclick="curtain_promotion_clicked()"></div>
{if $BRANCH_CODE eq 'HQ'}
{* <b>Branch:</b> {dropdown id="select_branch" values=$branches selected=$smarty.request.branch_id key=id value=code onchange="size_color_matrix();" *}
{else}
<input type=hidden id="select_branch" value="{$sessioninfo.branch_id}">
{/if}
<!--input type="button" value="Show" class="small" onClick="show_sku_cost_history();" /-->
<div id="div_size_color_matrix" style="height:450px;overflow:auto;">





</div>
</div>
