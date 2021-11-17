{*
5/3/2018 1:13 PM Andy
- Added Foreign Currency feature.
*}
{include file=header.tpl}
{literal}
<script>
function do_print(){
	window.print();
}

function zoom_vendor(vendor_id){
	document.location = '/vendor_po.summary.php?'+Form.serialize(document.f1)+'&vendor_id='+vendor_id;
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
		<form name="f1" class="noprint" action="{$smarty.server.PHP_SELF}" method=get style="padding:5px;white-space:nowrap;">
			<input type="hidden" name="a" value="show">
			<p>
			<div class="row">
				<div class="col-md-3">
					<b class="form-label">Date From</b> 
				<div class="form-inline">
					<input class="form-control" type="text" name="from" value="{$smarty.request.from}" id="added1" readonly="1" size=15 />&nbsp; <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date"/> 
				</div>
				</div>
		
				<div class="col-md-3">
					<b class="form-label">To</b> 
				<div class="form-inline">
					<input class="form-control" type="text" name="to" value="{$smarty.request.to}" id="added2" readonly="1" size=15 /> &nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date"/> 
				</div>
				</div>
		
				{if $BRANCH_CODE eq 'HQ'}
					<div class="col-md-3">
						<b class="form-label">Branch</b>
					<select class="form-control" name=branch_id>
					<option value="">-- All --</option>
					{section name=i loop=$branch}
					<option value="{$branch[i].id}" {if $smarty.request.branch_id eq $branch[i].id}selected{assign var=_br value=`$branch[i].code`}{/if}>{$branch[i].code}</option>
					{/section}
					</select>
					</div>
					
		
					<div class="col-md-3">
						<b class="form-label">Vendor</b>
					<select class="form-control" name=vendor_id id="vendor_id">
					<option value="">-- All --</option>
					{section name=i loop=$vendor}
					<option value="{$vendor[i].id}" {if $smarty.request.vendor_id eq $vendor[i].id}selected{assign var=_vd value=`$vendor[i].description`}{/if}>{$vendor[i].description}</option>
					{/section}
					</select> 
					</div>
				{/if}
				<div class="col-md-3 px-2">
					<input class="btn btn-primary mt-3 ml-1" type="submit" value="Refresh">
				<input class="btn btn-info mt-3 ml-1" type="button" onclick="do_print()" value="Print">
				</div>
			</div>
			</p>
		</form>
	</div>
</div>
<br>

{if $smarty.request.a eq 'show'}
	{php}
		show_report();
	{/php}
{/if}

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

{literal}
<script type="text/javascript">


    Calendar.setup({
        inputField     :    "added1",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });

    Calendar.setup({
        inputField     :    "added2",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });

</script>
{/literal}

{include file=footer.tpl}
