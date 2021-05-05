{*
6/23/2020 04:16 PM Sheila
- Updated button css
*}

{include file=header.tpl}
<script>
</script>
{literal}
<style>
.sh{
    background-color:#ff9;
}

.stdframe.active{
 	background-color:#fea;
	border: 1px solid #f93;
}

td.xc{
	border-bottom: 1px dashed #aaa;
}

.input_no_border input, .input_no_border select{
	border:1px solid #999;
	background: #fff;
	font-size: 10px;
	padding:2px;
}
</style>
<script>
function do_upload(){
	if($('file').value){
		document.f_a.submit();
	}
	else{
		alert('Please upload your file to create DO');
		return;
	}
}

</script>
{/literal}

<h1>Delivery Order (Import File)</h1>

<form name="f_a" method=post ENCTYPE="multipart/form-data">
<input type="hidden" name="MAX_FILE_SIZE" value="2048000">

<input type=hidden name=a value="upload_file">

<div class="stdframe" style="background:#fff">
<h4>General Information</h4>

{if $errm.top}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.top item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

<table border=0 cellspacing=0 cellpadding=4>
<tr>
<td><b>DO Date</b></td>
<td>
<input name="do_date" value="{$smarty.now|date_format:"%d/%m/%Y"}" size=10><br>
dd/mm/yyyy
</td>
</tr>

<tr>
<td valign=top><b>Delivery Branch</b></td>
<td>
<select name="do_branch_id">
	{foreach item="curr_Branch" from=$branch}
	<option value={$curr_Branch.id} {if $curr_Branch.id==$branch_id or $smarty.request.branch_id==$curr_Branch.id}selected{/if}>{$curr_Branch.code}</option>
	{/foreach}
	</select>
</td>
</tr>

<tr>
<th align=left>Imported File</th>
<td><input name=files id=file type=file class="files" size=50></td>
</tr>
</table>
</div>

</form>

<p align=center>
<input name=bsubmit class="btn btn-primary" type=button value="Upload" onclick="do_upload()" >

<input class="btn btn-error" type=button value="Close" onclick="document.location='/do.php'">
</p>

{include file=footer.tpl}

<script>

</script>
