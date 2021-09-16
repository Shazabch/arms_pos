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
<div class="container">
		<div class="breadcrumb-header justify-content-between">
			<div class="my-auto">
				<div class="d-flex">
					<h4 class="content-title mb-0 my-auto ml-4 text-primary">Generate QR Code</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
				</div>
			</div>
		</div>

	<div class="card mx-3">
		<div class="card-body">
			<div class="stdframe" style="margin-bottom:20px;">
				<form method=post name=f_a>
					<input type=hidden name=qr value="1">
					<table id="top_form" width="100%">
						<tr>
							<label>Location</label>
							<td>
								<select class="form-control select2" name="default_branch_id" onchange="uname_blur(newuser)">
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
		</div>
		</div>
			{if $qr_code}
		<div class="card mx-3">
			<div class="card-body">
		
			<div class="stdframe" style="margin-bottom:20px;">
			<div class="table-responsive">
				<table id="top_form" width="80%" >
					<tr>
						<td colspan="2"><label>QR code</label></td>
						<td></td>
					
					</tr>
					<tr>
						<td colspan="2" class="ml-4">
							<img src="../thumb.php?img={$qr_code|urlencode}&h=180&w=180"/ style="max-width: 125px; max-height: 125px;">
							<br>
						</td>
						<td>
							<a type="button" download href="./thumb.php?img={$qr_code|urlencode}&h=250&w=250" class="btn btn-primary "> Download QR Code</a><br><br>
							<a type="button" href="{$qr_content}" target="_blank" class="btn btn-secondary "> View Application Form</a>
						</td>
					</tr>
				</table>
			</div>
				<br style="clear: both;">
			</div>
			{/if}
		{/if}
		
		</div>
		</div>
	</div>
{include file=footer.tpl}
<script>
</script>
