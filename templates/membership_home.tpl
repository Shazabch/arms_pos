{*
06/29/2020 02:151 PM Sheila
- Updated button css.
*}

{include file=header.tpl}
<div align=center>
<table class=body cellpadding=0 cellspacing=0 border=0><tr>
<td align=center width=400> <!--background="images/akad02.jpg"-->
	<div style="padding:20px">
		<div class="breadcrumb-header justify-content-between">
			<div class="my-auto">
				<div class="d-flex ml-5">
					<h4 class="content-title mb-0 my-auto ml-4 text-primary ml-2">{$config.membership_cardname} Membership<br><br>
					
					({if $smarty.request.t eq 'apply'}
	Application &amp; Renewal
	{elseif $smarty.request.t eq 'history'}
	Check Points &amp; History
	{elseif $smarty.request.t eq 'update'}
	Update Information
	{/if})
					</h4>
					
					<span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
				</div>
			</div>
		</div>
	
	<div class="card ml-2 ">
		<div class="card-body">
			<div class="stdframe">
				<b class="form-label">Please scan {$config.membership_cardname} or IC number</b><br><br>
				<form name=f_i method=post>
				<input type=hidden name=a value='i'>
				<input class="form-control" name=nric size=30 onBlur="uc(this)"><br><br>
				<input class="btn btn-primary" type=submit value="Enter">
				</div>
		</div>
	</div>
	</form>

	</div>
</td>
<!--td valign=top><img src=images/akad01.jpg></td-->
</tr></table>
</div>
<script>
document.f_i.nric.focus();
</script>
{include file=footer.tpl}
