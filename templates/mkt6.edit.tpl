{include file=header.tpl}
{literal}
<style>

.st_block td, .st_block th {
	border-right:1px solid #ccc;
	border-bottom:1px solid #ccc;
}
.st_block th { background:#efffff; padding:4px; }
.st_block .lastrow th { background:#f00; color:#fff;}
.st_block .title { background:#e4efff; color:#00f;  }
.st_block input { border:1px solid #fff; margin:0;padding:0; }
.st_block input:hover { border:1px solid #00f; }
.st_block input.focused { border:1px solid #fec; background:#ffe; }
.st_block input[disabled] { color:#000;border:none;background:transparent; }
.st_block input[readonly] { color:#00f;}
.st_block {
	border-left:1px solid #ccc;
	border-top:1px solid #ccc;
	font-size:12px;
}
.files {
	width: 1;
}
</style>
<script>
var no_add=0;
function do_comment(n){
	document.f_a.elements['comment['+n+']'].value='';
	document.f_a.elements['comment['+n+']'].type='text';
	document.f_a.elements['comment['+n+']'].focus();
}

function do_no_comment(n){

	document.f_a.elements['comment['+n+']'].value='';
	document.f_a.elements['comment['+n+']'].type='hidden';
}

function do_save(){
	for(var i=1;i<no_add;i++){
		if(trim(document.f_a.elements['attachments['+i+']'].value)==""){
		    alert('Please provide name of each attachment');
        	return false;
		}
	}
	if (confirm('Upload these attachments?')){
		document.f_a.a.value='save';
		document.f_a.submit();
	}
}

function do_save_comment(){
	document.f_a.a.value='save_comment';
	document.f_a.submit();
}

function add_row(obj,n){
	if (obj!=undefined)
	{
		if (obj.alt != '' || obj.value=='') return;
		obj.alt='used';
	}
	var new_row = $('tbl_upload').insertRow(-1);
	no_add++;
	new_row.innerHTML='<td>Name: <input size=50 onblur="add_row(this,'+(n+1)+')" name=attachments['+n+']> File: <input name=files['+n+'] type=file class="files;"><input type=hidden name=filepath['+n+'] value=""></td>';

}
</script>
{/literal}



<h1>{$PAGE_TITLE}</h1>
{if $errm}<div id=err><div class=errmsg><ul><li>{$errm}</ul></div></div>{/if}

<form name=f_a method=post enctype='multipart/form-data'>
<input type=hidden name=a value=open>
<input type=hidden name=user_id value="{$mkt0.user_id}">
<input type=hidden name=mkt0_id value="{$mkt0.id}">
{assign var=branch_id value=$sessioninfo.branch_id}


<div class=stdframe style="background:#fff">
<table cellspacing=0 cellpadding=4 border=0 class=tl>
<tr>
	<td colspan=2 class=small>Created: {$mkt0.added}, Last Update: {$mkt0.last_update}</td>
</tr>
<tr>
	<th nowrap>Participating Branches</th>
	<td>
	{foreach from=$branches item=branch key=i}
	<img src=/ui/checked.gif> {$i}
	{/foreach}
	</td>
</tr><tr>
	<th>Promotion Title</th>
	<td>{$mkt0.title}</td>
</tr><tr>
	<th nowrap>Promotion Period</th>
	<td>
		{$mkt0.offer_from|date_format:'%d/%m/%Y'} to {$mkt0.offer_to|date_format:'%d/%m/%Y'}
	</td>
</tr><tr>
	<th nowrap>Submit Due Date</th>
	<td>
		{$mkt0.submit_due_date_4|date_format:'%d/%m/%Y'}
	</td>
</tr>
<tr>
	<th>Publish Date </th>
	<td>
{section name=x start=1 loop=6}
{assign var=x value=$smarty.section.x.iteration}
{if $mkt0.publish_dates[$x]!=''}
<img align=absbottom src="ui/calendar.gif" id="b_p_date[{$x}]" title="Select Date">{$mkt0.publish_dates[$x]}&nbsp;&nbsp;&nbsp;&nbsp;
{/if}
{/section}
	</td>
</tr>
<tr>
	<th nowrap>Attachments</th>
	<td>
		{foreach from=$mkt0.attachments.name key=idx item=fn}
		{if $fn}<img src=/ui/icons/attach.png align=absmiddle> <a href="javascript:void(window.open('{$image_path}{$mkt0.filepath[$idx]}'))">{$fn}</a> &nbsp;&nbsp; {/if}
		{/foreach}
	</td>
</tr><tr>
	<th nowrap>Promotion<br>Period Remark</th>
	<td>
		{$mkt0.remark|nl2br}
	</td>
</tr>
</table>
</div>
<br>

<h3>Comments List</h3>
{foreach from=$mkt6_attach key=f name=j item=m_a}
{assign var=n value=$smarty.foreach.j.iteration}
{assign var=count value=0}

<table width="100%" cellpadding=0 cellspacing=0 border=0 class=st_block>

<tr>
<th colspan=5 align=left>{$m_a.name|upper} &nbsp;&nbsp;  <img align=absmiddle src=ui/icons/attach.png border=0 onclick="javascript:void(window.open('mkt_attachments/{$m_a.mkt0_id}/{$m_a.file}'))">&nbsp;<a href="javascript:void(window.open('mkt_attachments/{$m_a.mkt0_id}/{$m_a.file}'))">View</a></td>
</tr>

<tr align=center>
<th width=30>No</td>
<th width=600>Comments</td>
<th width=100>By</td>
<th width=50>Date</td>
<th width=10>Status</td>
</tr>

{foreach name=i item="curr_mkt6" name=i  from=$form}
{if $curr_mkt6.attachment_id==$m_a.id}
{assign var=count value=$count+1}
<tr align=center>
<td>{$count}</td>
<td align=left>{if !$curr_mkt6.comment}Approved{else}{$curr_mkt6.comment}{/if}</td>
<td>{$curr_mkt6.user}</td>
<td>{$curr_mkt6.date}</td>
<td>{if $curr_mkt6.approval=='1'}<img src=/ui/approved.png border=0>{else}<img src=/ui/rejected.png border=0>{/if}</td>
</tr>
{/if}
{/foreach}
{if $count<'1'}
<tr>
<td colspan=5 align=center>No Record Found</td>
</tr>
{/if}

</table>
{if $sessioninfo.id!=$mkt0.user_id and $mkt6_privilege.MKT6_EDIT.$branch_id}
<P>
APPROVE<input type=radio name=approval[{$n}] value='1' onclick="do_no_comment('{$n}');">&nbsp;&nbsp;
FEEDBACK<input type=radio name=approval[{$n}] value='0' onclick="do_comment('{$n}');"> <input size=50 type=hidden name=comment[{$n}] value="">
<input type=hidden name=attachment_id[{$n}] value="{$m_a.id}">
</P>
{/if}
<br><br>
{/foreach}

{if !$mkt6_attach}
<table width='100%' class=st_block><td align=center>No Comments Records Found</td></table>
{/if}

{if $sessioninfo.id==$mkt0.user_id and $mkt6_privilege.MKT6_EDIT.$branch_id}
<h3>Uploading</h3>
<table width="100%" id=tbl_upload>
<script>
add_row(undefined,1);
</script>
</table>
<br><br>
{/if}

<p align=center>
{if $sessioninfo.id==$mkt0.user_id and $mkt6_privilege.MKT6_EDIT.$branch_id}
<input name=bsubmit type=button value="Upload" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_save()" >
{elseif $mkt6_attach and $mkt6_privilege.MKT6_EDIT.$branch_id}
<input name=bsubmit type=button value="Send" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_save_comment()" >
{/if}
<input type=button value="Close" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="document.location='/mkt6.php'">
</p>

{include file=footer.tpl}
<script>

</script>

