{*
11/8/2010 2:59:46 PM Justin
- Added date selection for Expiry Date field.
- Added point columns.
- Added JS to sum up all the points when members that being ticked.
*}

{include file=header.tpl}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{literal}
<script>

function recalc_total_points(val, points){
	if(val.checked == true){
		document.f_a.total_points.value = int(document.f_a.total_points.value) + int(points);
	}else{
		document.f_a.total_points.value = int(document.f_a.total_points.value) - int(points);
	}
	var total_points = addCommas(document.f_a.total_points.value);
	$("m_total_points").innerHTML = total_points;
}

function addCommas(nStr){
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
}

function init_calendar(sstr){

	Calendar.setup({
	    inputField     :    "expiry",     // id of the input field
	    ifFormat       :    "%Y-%m-%e",      // format of the input field
	    button         :    "img_expiry",  // trigger for the calendar (button ID)
	    align          :    "Bl",           // alignment (defaults to "Bl")
	    singleClick    :    true
	});
}

</script>
{/literal}

<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>
<div class="card mx-3">
	<div class="card-body">
		<div class=stdframe>
			<form action="{$smarty.server.PHP_SELF}" method=post>
			<div class="row">
				<input type=hidden name=a value=list>
			<div class="col-md-4">
				{if BRANCH_CODE eq 'HQ'}
			<b class="form-label">Apply Branch</b> 
			<select class="form-control" name=branch_id>
			<option value="">-- All --</option>
			{foreach from=$branch item=b}
			<option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected{assign var=_br value=`$b.code`}{/if}>{$b.code}</option>
			{/foreach}
			</select>
			{/if}
			</div>
			<div class="col-md-4">
				<b class="form-label">Membership Expiry Date</b>
			<div class="form-inline">
				<input class="form-control" name="expiry" id="expiry" size="30" value="{$smarty.request.expiry|default:$smarty.now|date_format:'%Y-%m-%d'}">
			&nbsp;<img align=absbottom src="ui/calendar.gif" id="img_expiry" style="cursor: pointer;" title="Select Date"/>&nbsp;
			</div>
			</div>
			<div class="col-md-4">
				<input class="btn btn-primary mt-4"  type=submit value="Retrieve">
			</div>
			</div>
			</form>
			</div>
			
	</div>
</div>
{if $smarty.request.a eq 'list'}
{if !$members}
<!-- no data -->
<p class="mx-3">-- no record found --</p>
{else}
<form name="f_a" action="{$smarty.server.PHP_SELF}" method=post>
<input type=hidden name=a value=terminate>
<p>{count var=$members} memberships expired on {$smarty.request.expiry}. Click here to <button>Terminate</button> the selected Member(s).</p>

<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table  class="tb table mb-0 text-md-nowrap  table-hover" >
				<thead class="bg-gray-100">
					<tr >
						<th>&nbsp;</th>
						<th>NRIC</th>
						<th>{$config.membership_cardname} No</th>
						<th>Name</th>
						<th>Issue Date</th>
						<th>Expiry Date</th>
						<th>Apply Branch</th>
						<th>Points</th>
					</tr>
				</thead>
				{assign var=n value=1}
				{foreach from=$members item=m}
				{assign var=total_points value=$total_points+$m.points}
				<tbody class="fs-08">
					<tr>
						<td><input type=checkbox name=nric[] value="{$m.nric}" onchange="recalc_total_points(this, '{$m.points|default:0}');" checked> {$n++}.</td>
						<td>{$m.nric|default:'&nbsp;'}</td>
						<td>{$m.card_no|default:'&nbsp;'}</td>
						<td>{$m.name|default:'&nbsp;'}</td>
						<td>{$m.issue_date|date_format:'%d/%m/%Y'|default:'&nbsp;'}</td>
						<td>{$m.next_expiry_date|date_format:'%d/%m/%Y'|default:'&nbsp;'}</td>
						<td>{$m.branch_code|default:'&nbsp;'}</td>
						<td align="right" {if $m.points}style="color:#f00;"{/if}>
							{$m.points|number_format|default:'-'}
						</td>
					</tr>
				</tbody>
				{/foreach}
				<input type="hidden" id="total_points" name="total_points" value="{$total_points|default:0}">
				<tr>
					<th align="right" colspan="7">Total</th>
					<th align="right" {if $total_points}style="color:#f00;"{/if} id="m_total_points">
						{$total_points|number_format|default:'-'}
					</th>
				</tr>
				</table>
		</div>
	</div>
</div>
{/if}
</form>
{/if}
<script>
init_calendar('');
</script>
{include file=footer.tpl}
