{*
2/8/2013 3:28 PM Fithri
- Adjustment Summary is not tally with detail

7/13/2015 2:43 PM Joo Chia
- Add in filter selection for Adjustment Type
- Group date from/to, Branch, Department, Brand by span respectively

10/25/2019 1:21 PM William
- Fixed bug Brand filter cannot auto select selected brand when brand is "UN-BRANDED".
*}
{include file=header.tpl}
{literal}
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
<style>
.below_cost 
{
	font-weight:bold;
	color:red;
}
</style>

<script>
function do_print(){
	window.print();
}

function zoom_dept(dept_id){
	document.location = '/adjustment.summary.php?'+Form.serialize(document.f_d)+'&department_id='+dept_id;
}
</script>
{/literal}

<div class="noprint">
	<div class="breadcrumb-header justify-content-between">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
			</div>
		</div>
	</div>

</div>

<div class="card mx-3">
	<div class="card-body">
		<div class="noprint stdframe " >
			<form name="f_d">
			<input type=hidden name=a value="refresh">
			
		<div class="row">	
					
			<div class="col-md-6">
				<b class="form-label">Date From</b> 
			<div class="form-inline"><input class="form-control" type="text" name="from" value="{$form.from}" id="added1" readonly="1" size=12> <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date"></div>
			</div>
			
			<div class="col-md-6">
				<b class="form-label">To</b> 
			<div class="form-inline">
				<input class="form-control" type="text" name="to" value="{$form.to}" id="added2" readonly="1" size=12> <img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
			</div>
			</div>
			
			{if $BRANCH_CODE eq 'HQ'}
			
			<div class="col-md-6">
				<div class="form-group">
					<span>
						<b class="form-label">Branch</b>
							<select class="form-control select2" name="branch_id">
							{section name=i loop=$branch}
							<option value="{$branch[i].id}" {if $branch_id eq $branch[i].id}selected{/if}>{$branch[i].code}</option>
							{/section}
							</select>
					</span>
				</div>
			</div>
			{/if}
			
		<div class="col-md-6">
			<div class="form-group">
				<span>
					<b class="form-label">Department</b>
						<select class="form-control select2" name="department_id">
						<option value=''>-- All --</option>
						{section name=i loop=$dept}
						<option value="{$dept[i].id}" {if $smarty.request.department_id eq $dept[i].id}selected{/if}>{$dept[i].description}</option>
						{/section}
						</select>
				</span>
			</div>
		</div>
			
			<div class="col-md-6">
				<div class="form-group">
					<span>
						<b class="form-label">Brand</b>
							<select class="form-control select2" name="brand_id">
							<option value=''>-- All --</option>
							<option value=0 {if $smarty.request.brand_id eq '0'}selected{/if}>UN-BRANDED</option>
							{section name=i loop=$brand}
							<option value="{$brand[i].id}" {if $smarty.request.brand_id eq $brand[i].id}selected{/if}>{$brand[i].description}</option>
							{/section}
							</select>
					</span>
				</div>
			</div>
			
		<div class="col-md-6">
			<div class="form-group">
				<span>
					<b class="form-label">Adjustment Type</b>
						<select class="form-control" name="adjustment_type">
						<option value=''>-- All --</option>
						{section name=i loop=$adj_type_list}
						<option value="{$adj_type_list[i].adjustment_type}" {if $smarty.request.adjustment_type eq $adj_type_list[i].adjustment_type}selected{/if}>{$adj_type_list[i].adjustment_type}</option>
						{/section}
						</select>	
				</span>
			</div>
		</div>
		</div>
				
				
			<input type="button" class="btn btn-primary" onclick="form.submit()" value="Refresh">
			<input type="button" class="btn btn-info" onclick="do_print()" value="Print">
			</form>
			</div>
	</div>
</div>
<br>


<div class="card mx-3">
	<div class="card-body fs-08 ">
		{if $smarty.request.from ne ''}
{php}
show_report();
{/php}
{/if}

<div class="noscreen">
{include file=report_footer.landscape.tpl}
</div>

	</div>
</div>
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
