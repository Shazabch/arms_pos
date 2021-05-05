{*
3/01/2021 5:17 PM Rayleen
- New Module "User EForm Application"
- Add checking if user can generate QR
- Add checking if user can approve application

03/09/2021 2:37 PM Rayleen
- Remove "template" option
*}
{include file=header.tpl}
<script type="text/javascript">

{literal}

{/literal}
</script>

{if !$can_generate_qr}
	<h1>Sub-branch cannot generate QR.</h1>
{else}
	<h1>Generate QR Code</h1>

	<div class="stdframe" style="margin-bottom:20px;">
		<form method=post name=f_a>
			<input type=hidden name=qr value="1">
			<table id="top_form" width="30%">
				<tr>
					<td><b>Location</b></td>
					<td>
						<select name=default_branch_id onchange="uname_blur(newuser)">
						{section name=i loop=$branch}
						<option value={$branch[i].id} {if $smarty.request.default_branch_id == $branch[i].id}selected{/if}>{$branch[i].code}</option>
						{/section}
						</select>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<br>
						<p><input class="btn btn-primary" name=submitbtn type=submit value="Generate QR"></p>
					</td>
				</tr>
			</table>
		</form>
	</div>

	{if $qr_code}
	<div class="stdframe" style="margin-bottom:20px;">
		<table id="top_form" width="30%" >
			<tr>
				<td colspan="2"><b>QR Code</b></td>
				<td></td>
			</tr>
			<tr>
				<td colspan="2">
					<img src="../thumb.php?img={$qr_code|urlencode}&h=180&w=180"/>
					<br>
				</td>
				<td>
					<a type="button" download href="./thumb.php?img={$qr_code|urlencode}&h=250&w=250" class="btn btn-primary"> Download QR Code</a><br><br>
					<a type="button" href="{$qr_content}" target="_blank" class="btn btn-primary"> View Application Form</a>
				</td>
			</tr>
		</table>
		<br style="clear: both;">
	</div>
	{/if}
{/if}


{include file=footer.tpl}
<script>
</script>
