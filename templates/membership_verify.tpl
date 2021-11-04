{include file=header.tpl}
{literal}
<script>
var popwin;

function do_verify(nric)
{
	popwin = window.open('membership.php?a=i&t=verify&nric='+nric, null, "width=700,height=500,status=no,scrollbars=yes,resizable=yes");
	maximize_window(popwin);
}

function do_approve(nric)
{
	y = document.f_d.Date_Year.value;
	m = document.f_d.Date_Month.value;
	d = document.f_d.Date_Day.value;
	b = document.f_d.branch_id.value;

	var jax = new Ajax.Updater(
		"unv",
		"membership.php",
		{
			method: 'get',
			parameters: 'a=v&t=verify&nric='+nric+'&dt='+y+m+d+"&branch_id="+b,
			onComplete: hide_row("r["+nric+"]")
		});
}


function hide_row(r)
{
	hidediv('ndiv');
	new Effect.Fade(r);
}

function reload_list(dt)
{
	if (!dt)
	{
		y = document.f_d.Date_Year.value;
		m = document.f_d.Date_Month.value;
		d = document.f_d.Date_Day.value;
		dt = y+m+d;
	}
	else
	{
		// change dmy select
		document.f_d.Date_Year.options[parseInt(dt/10000)-2000].selected = true;
		document.f_d.Date_Month.options[(parseInt(dt/100)%100)-1].selected = true;
		document.f_d.Date_Day.options[parseInt(dt%100)-1].selected = true;
	}
	b = document.f_d.branch_id.value;
	var jax = new Ajax.Updater(
		"udiv", "membership.php",
		{
			method: 'get',
			parameters: "t=verify&dt="+dt+"&branch_id="+b,
			onLoading: function() { $('unv').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...' },
		});
}


function reload_list_name()
{
	n = document.f_q.alphabate.value;
	document.f_q.search_ic.value = '';
	var jax = new Ajax.Updater(
		"udiv", "membership.php",
		{
			method: 'get',
			parameters: "t=verify&name="+n,
			onLoading: function() { $('unv').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...' },
		});
}



function reload_list_ic()
{
	n = document.f_q.search_ic.value;
	document.f_q.alphabate.selectedIndex = 0;
	var jax = new Ajax.Updater(
		"udiv", "membership.php",
		{
			method: 'get',
			parameters: "t=verify&nric="+n,
			onLoading: function() { $('unv').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...' },
		});
}
</script>

{/literal}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">Membership Verification</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>
<div class="card mx-3">
	<div class="card-body">
		<p>
			<form name=f_q onsubmit="return false">
			<div class="row">
				<div class="col">
					<b class="form-label">Sort by Name</b>
			<select class="form-control" name=alphabate onchange=reload_list_name()>
			<option value="">-- Select --</option>
			<option value="A">A</option>
			<option value="B">B</option>
			<option value="C">C</option>
			<option value="D">D</option>
			<option value="E">E</option>
			<option value="F">F</option>
			<option value="G">G</option>
			<option value="H">H</option>
			<option value="I">I</option>
			<option value="J">J</option>
			<option value="K">K</option>
			<option value="L">L</option>
			<option value="M">M</option>
			<option value="N">N</option>
			<option value="O">O</option>
			<option value="P">P</option>
			<option value="Q">Q</option>
			<option value="R">R</option>
			<option value="S">S</option>
			<option value="T">T</option>
			<option value="U">U</option>
			<option value="V">V</option>
			<option value="W">W</option>
			<option value="X">X</option>
			<option value="Y">Y</option>
			<option value="Z">Z</option>
			</select>
				</div>
			
				<div class="col">
					<b class="form-label">Search IC</b> 
					<div class="form-inline">
						<input class="form-control" name=search_ic> &nbsp;&nbsp; <input class="btn btn-primary" type=button value="Find" onclick="reload_list_ic()">
					</div>
				</div>
			</div>
			</form>
			<form name=f_d>
			<div class="row">
				<div class="col">
					<b class="form-label">Filter by Date</b> 
				{html_select_date day_value_format="%02d" month_format="%m" start_year=2000 all_extra="onchange=reload_list()"}
				</div>
				<div class="col">
					
						{if $BRANCH_CODE eq 'HQ'}
						<b class="form-label">Issue Branch</b> 
						<div class="form-inline">
						<select class="form-control" name=branch_id onchange=reload_list()>
						{section name=i loop=$branches}
						<option value={$branches[i].id} {if $smarty.request.branch_id == $branches[i].id}selected{/if}>{$branches[i].code}</option>
						{/section}
						</select>
						{else}
						<input type=hidden name=branch_id value="{$sessioninfo.branch_id}">
						{/if}
						&nbsp;&nbsp;<input class="btn btn-primary" type=button value="Reload" onclick="reload_list()">
					</div>
				</div>
			</div>
			</form>
			</p>
	</div>
</div>
<div id="udiv" class="stdframe">
<div class="card mx-3">
	<div class="card-body">
		{include file=membership_verify_table.tpl}
	</div>
</div>
</div>

<div class="ndiv" id="ndiv" style="position:absolute;left:150;top:100;display:none;">
<div class="blur"><div class="shadow"><div class="content">
<div id="detail_div"></div>
</div></div></div>
</div>

<script>
new Draggable('ndiv');
</script>

{include file=footer.tpl}
