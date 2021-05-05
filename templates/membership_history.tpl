{*
12/7/2009 11:04:22 AM Andy
- edit sales_details function

8/4/2010 1:40:12 PM Alex
- add branch_id for trans_detail function

10/29/2010 11:11:04 AM Justin
- Added the type on ajax paramater to allow display history when it is either REDEEM or CANCELED.

5/9/2011 12:32:55 PM yinsee
- add Favourite Product tab (request by tommy)

2/29/2012 4:51:43 PM Justin
- Added to pickup branch id while view sales details.

6/22/2012 2:07:00 PM Andy
- Add to show "Auto Redemption" History at membership points history.

9/3/2012 3:38 PM Justin
- Enhanced to show error message return from date validation.
- Enhanced to switch to update tab while found user is doing update for card renewal.

9/13/2012 3:59 PM Justin
- Enhanced to mark * for points while point is not up to date.
- Enhanced to have a link to allow user click and mark member points recalculate when user has privilege.

9/25/2012 12:50 PM Justin
- Enhanced to only allow user recalculate points while is system admin.

11/20/2012 5:32 PM Justin
- Bug fixed of the indicator of points up to date is wrong.

1/17/2013 2:34 PM Andy
- Show member used and remaining quota information.

2/6/2013 11:38 AM Justin
- Enhanced to show link to access principal card instead of 0 for "Points Accumulated" while it is supplementary card.
- Enahnced the link to show new page.

5/24/2013 9:45 AM Justin
- Assigned CSS to build up div border.

10/25/2019 2:40 PM William
- Enahnced to add mobile profile photo.

11/7/2019 5:28 PM William
- Add checking config "membership_mobile_settings" for "Mobile Profile Photo".

11/26/2019 10:10 AM William
- Enhanced to show "Member Type", "Gender", "Birthday", and "Remark" to Member Points & History screen.

1/18/2021 11:55 AM William
- Enhanced to show "Patient Medical Record" when config "membership_pmr" is active.

1/26/2020 10:24 AM William
- Enhanced to use $config.membership_pmr_name as label name of membership_pmr.
*}

{include file=header.tpl}

{if $config.membership_enable_staff_card and $form.staff_type}
	{assign var=show_quota_info value=1}
{/if}

{literal}
<style>
.div_upd{
	padding:5px;
	border:1px solid black;
}
</style>
{/literal}
<script type="text/javascript">
var LOADING = '<img src="/ui/clock.gif" />';
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
function sales_details(date, card_no, type, bid, ord_date){
    curtain(true);
    center_div('div_sales_details');

    $('div_sales_details').show()
	$('div_sales_content').update(LOADING+' Please wait...');
	if(type == 'AUTO_REDEEM'){
		new Ajax.Updater('div_sales_content',phpself+'?a=ajax_get_auto_redemption_history'+URLEncode('&date='+date+'&card_no='+card_no+'&bid='+bid+'&type='+type+'&ord_date='+ord_date),
		{
		    method: 'post'
		});
	}else if(type=='REDEEM' || type=='CANCELED'){
        new Ajax.Updater('div_sales_content',phpself+'?a=ajax_get_redemption_history&date='+date+'&card_no='+card_no+'&bid='+bid+'&type='+type,
		{
		    method: 'post'
		});
	}else{
        new Ajax.Updater('div_sales_content','counter_collection.php?a=sales_details&date='+date+'&card_no='+card_no+'&branch_id='+bid,
		{
		    method: 'post'
		});
	}
	
}
function trans_detail(counter_id,cashier_id,date,pos_id,branch_id)
{
	curtain(true);
	center_div('div_item_details');
	
    $('div_item_details').show();
	$('div_item_content').update(LOADING+' Please wait...');

	new Ajax.Updater('div_item_content','counter_collection.php',
	{
	    method: 'post',
	    parameters:{
			a: 'item_details',
			branch_id: branch_id,
			counter_id: counter_id,
			pos_id: pos_id,
			cashier_id: cashier_id,
			date: date
		}
	});
}
function curtain_clicked()
{
	curtain(false);
	hidediv('div_sales_details');
	hidediv('div_item_details');
}

function show_quota_info(date, card_no, bid){
	curtain(true);
    center_div('div_sales_details');

    $('div_sales_details').show()
	$('div_sales_content').update(LOADING+' Please wait...');
	
	new Ajax.Updater('div_sales_content','counter_collection.php?a=sales_details&date='+date+'&card_no='+card_no+'&branch_id='+bid+'&type=special-used_quota',
	{
	    method: 'post'
	});
}

function profile_image_clicked(){
	var profile_image_url = $('inp_profile_image_url').value;
	
	// No image
	if(!profile_image_url)	return;
	show_sku_image_div(profile_image_url);
}
{/literal}
</script>
{literal}
<style>
#div_sales_details,#div_item_details{
	border: 3px solid rgb(0, 0, 0);
	padding: 10px;
 	background:rgb(255, 255, 255) none repeat scroll 0% 0%;
	position:absolute;
	z-index:10000;
}

#div_profile_image{
	border: 3px outset black;
	float: right;
	background-color: #fff;
	padding: 3px;
}
</style>
{/literal}

<!-- Transaction Details-->
<div id="div_sales_details" style="display:none;width:600px;height:400px;">
<div style="float:right;"><img onclick="curtain_clicked()" src="/ui/closewin.png" /></div>
<div id="div_sales_content">
</div>
</div>
<!-- End of Transaction Details-->
<!-- Item Details -->
<div id="div_item_details" style="display:none;width:600px;height:400px;">
<div style="float:right;"><img onclick="hidediv('div_item_details');" src="/ui/closewin.png" /></div>
<div id="div_item_content">
</div>
</div>
<!-- End of Item Details-->

{if $msg}<script>alert('{$msg}');</script>{/if}
{if $smarty.request.msg}<script>alert('{$smarty.request.msg}');</script>{/if}
<h1>{$config.membership_cardname} History</h1>
<p>
{if $config.membership_mobile_settings}
<table align="right">
	<tr><td align="center">
		<div id="div_profile_image">
		<div>Profile Photo</div>
		<input type="hidden" id="inp_profile_image_url" name="profile_image_url" value="{$form.profile_image_url}" />
		<img {if $form.profile_image_url}src="thumb.php?img={$form.profile_image_url|urlencode}&h=100&w=100"{/if} onClick="profile_image_clicked()" width="100" height="100" style="cursor:pointer;" title="Click to view full size photo" />
		</div>
	</td></tr>
</table>
{/if}
<table><tr><td>
<table  cellspacing=0 cellpadding=4>
<tr><td><b>Name</b></td><td>{$form.designation} {$form.name}</td></tr>
<tr><td><b>NRIC</b></td><td>{$form.nric}</td></tr>

{if $show_quota_info}
	<tr>
		<td><b>Quota Balance</b></td>
		<td>
			{$form.quota_balance|default:0|number_format:2} / {$form.staff_quota_info.quota_value|default:0|number_format:2}
		</td>
	</tr>
	<tr>
		<td><b>Quota Update</b></td>
		<td>
			{$form.quota_last_update}
		</td>
	</tr>
{/if}

<tr>
	<td><b>Points Accumulated</b></td>
	<td>
		{if !$form.parent_nric}
			{$form.points} <span id="span_points_{$form.nric}" style="color:red; font-size:16px; font-weight:bold;{if !$form.points_changed} display:none;{/if}">*</span>
			<!--input type="button" onclick="set_changed_member_points('{$form.nric}');" value="Recalculate" /-->
			{if $sessioninfo.level >= 9999}
				<a onclick="set_changed_member_points('{$form.nric}');" style="cursor:pointer;"><img src="/ui/icons/arrow_refresh.png" border=0 width="18" title="Recalculate Member Point {if $show_quota_info}and Quota Balance {/if}for {$form.name}"></a>
			{/if}
		{else}
			<a href="membership.php?t=history&a=i&nric={$form.parent_nric}"  target="_blank">Refer to Principal Card</a>
		{/if}
	</td>
</tr>
{if $form.points_update>0}
<tr><td><b>Points Update</b></td><td>{$form.points_update|date_format:$config.dat_format}</td></tr>
{/if}
<tr><td><b>Current {$config.membership_cardname} Number</b></b></td><td>{$form.card_no}</td></tr>
<tr><td><b>Issue Branch</b></td><td>{$form.branch_code}</td></tr>
<tr><td><b>Issue Date</b></td><td>{$form.issue_date|date_format:$config.dat_format}</td></tr>
<tr><td><b>Next Expiry Date</b></td><td>{$form.next_expiry_date|date_format:$config.dat_format}</td></tr>
{if $config.membership_type}
<tr>
	<td><b>Member Type</b></td>
	<td>
	{foreach from=$config.membership_type key=member_type item=mtype_desc}
		{if $form.member_type eq $member_type}
			{$mtype_desc}
		{/if}
	{/foreach}
	</td>
</tr>
{/if}
<tr>
	<td><b>Gender</b></td>
	<td>{if $form.gender eq 'M'}Male{elseif $form.gender eq 'F'}Female{/if}</td>
</tr>
<tr>
	<td><b>Birthday</b></td>
	<td>{$form.dob2|date_format:$config.dat_format|default:""}</td>
</tr>
<tr valign=top><td><b>Remark</b></td><td>{$form.remark|nl2br}</td></tr>
{if $config.membership_pmr}
<tr valign=top><td><b>{$config.membership_pmr_name}</b></td><td style="{if $form.pmr}border: 1px solid #ccc;{/if}"><span style="max-height: 100px; width: 100%; display: block; overflow: auto;">{$form.pmr|nl2br}</span></td></tr>
{/if}
</table>
</td><td>
<img src="{$form.card_type|string_format:$config.membership_cardimg}" hspace=40>
</td></tr></table>
</p>


<script>
var nric = '{$form.nric}';
{literal}

function list_sel(s,n)
{
	$$('.tabcontent').each(function(e) {
		e.style.display = 'none';
		e.className='tabcontent';
	});
	
	$$('.tab a').each(function(e)
	{
		e.className = '';
	});
	
	$('lst'+n).className = 'active';
	$(s).style.display = '';
	$(s).className='tabcontent active';
}
{/literal}
</script>

{if $err}
<div id=err><div class=errmsg><ul>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

<form name=f onsubmit="return false;">
<div class=tab style="height:25px;white-space:nowrap;">
&nbsp;&nbsp;&nbsp;
<a href="javascript:list_sel('point_history', 1)" id=lst1 class=active>{$config.membership_cardname} Point History</a>
{if $show_quota_info}
	<a href="javascript:list_sel('div_quota_history', 5)" id="lst5">Quota History</a>
{/if}
<a href="javascript:list_sel('renewal_history', 2)" id=lst2>{$config.membership_cardname} Renewal History</a>
<a href="javascript:list_sel('favourite',4)" id=lst4>Favourite Product</a>
{if $sessioninfo.privilege.MEMBERSHIP_TOPEDIT}
	<a href="javascript:list_sel('update',3)" id=lst3>Update</a>
{/if}
<a name=find_po id=lst0 style="visibility:hidden"><input name=promono></a>
</div>
</form>

<div id=point_history style="border:1px solid #000;display:none" class=tabcontent>
{include file=membership_history.point_history.tpl}
</div>

{if $show_quota_info}
	<div id="div_quota_history" style="border:1px solid #000;display:none" class="tabcontent">
		{include file="membership_history.quota_history.tpl"}
	</div>
{/if}

<div id=renewal_history style="border:1px solid #000;display:none" class=tabcontent>
{include file=membership_history.renewal_history.tpl}
</div>
<div id=update style="border:1px solid #000;display:none" class=tabcontent>
{include file=membership_history.update.tpl}
</div>
<div id=favourite style="border:1px solid #000;display:none" class=tabcontent>
{include file=membership_history.product_history.tpl}
</div>
{if !$tab}
	{assign var=tab_desc value="point_history"}
	{assign var=tab value=1}
{/if}
<script>
list_sel('{$tab_desc}','{$tab}');
</script>

{include file=footer.tpl}
<script type="text/javascript">
{literal}
function validate_newcard(el)
{
	uc(el);
	$('card_check').innerHTML = '<img src=/ui/clock.gif align=absmiddle>';
	$('submit_new').disabled = true;
	// check the card and return status
	var param = Form.serialize(document.fnew)+'&a=ajax_validate_card';
	
	new Ajax.Request('membership.php', {
		parameters: param,
		onComplete:function(m) {
			$('card_check').innerHTML = m.responseText.replace(/\\n/," "); 		
			// if status = OK, enable submit button
			if (m.responseText=='OK')
				$('submit_new').disabled = false;
			else
			{
				el.select();
				el.focus();
				alert(m.responseText.replace(/\\n/,"\n"));
			}
		}
	});
}

function validate_point(f)
{
	if (f.points.value == '')
	{
		alert('Please insert points');
		f.points.focus();
		return false;
	}
	if (f.remark.value == '')
	{
		alert('Please insert remark');
		f.remark.focus();
		return false;
	}
	return true;
}
{/literal}
</script>
