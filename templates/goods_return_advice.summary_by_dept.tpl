{*
11/1/2010 2:36:45 PM Alex
- fix link and print bugs if select all branch from goods

9/15/2011 2:48:11 PM Justin
- Modified the status to have more options.
- Modified the status as below:
  => Saved - for those saved in GRA.
  => Completed (Not Returned) - for those already printed out the checklsit and awaiting for return.
  => Returned - for those already confirmed to return.
  
02/01/2016 14:48 Edwin
- Modified status filter list from "Saved" to "Saved & Waiting Approval" and "ALL" also include waiting approval query rules

02/29/2016 0946 Edwin
- Bugs fixed on status filter in GRA summary 

6/9/2017 11:50 AM Justin
- Enhanced to have new status filter "Un-checkout".

5/7/2018 4:16 PM Justin
- Enhanced to have foreign currency feature.

12/12/2018 11:24 AM Justin
- Enhanced to add remark.

06/24/2020 4:22 PM Sheila
- Updated button css
*}
{include file=header.tpl}
{literal}
<script>
function zoom_dept(dept_id)
{
	document.location = '/goods_receiving_note.summary.php?'+Form.serialize(document.f1)+'&department_id='+dept_id;
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



        <form name=f1 class="noprint" action="{$smarty.server.PHP_SELF}" method="get">
            <p>
                <div class="card mx-3">
                    <div class="card-body">

           <div class="row">
           <div class="col-md-3">
            <b class="form-label">GRA Date From</b> 
            <div class="form-inline">
                <input class="form-control" type="text" name="from" value="{$smarty.request.from}" id="added1" readonly="1" size=22 /> 
            &nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date"/>
            </div>
           </div>
            
            <div class="col-md-3">
                <b class="form-label">To</b> 
            <div class="form-inline">
                <input class="form-control" type="text" name="to" value="{$smarty.request.to}" id="added2" readonly="1" size=22 /> 
            &nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date"/>
            </div>
            </div>
           </div>
            
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
          
            <p>
            <!--input type=hidden name=a value="list"-->
           <div class="row">
            <div class="col-md-3">
                {if $BRANCH_CODE eq 'HQ'}
            <b class="form-label">Branch</b>
            <select class="form-control" name=branch_id>
            <option value="">-- All --</option>
            {section name=i loop=$branch}
            <option value="{$branch[i].id}" {if $smarty.request.branch_id eq $branch[i].id}selected{assign var=_br value=`$branch[i].code`}{/if}>{$branch[i].code}</option>
            {/section}
            </select>
           
            {/if}
            </div>

           <div class="col-md-3">
            <b class="form-label">Department</b>
            <select class="form-control" name=department_id>
            <option value="">-- All --</option>
            {section name=i loop=$dept}
            <option value="{$dept[i].id}" {if $smarty.request.department_id eq $dept[i].id}selected{assign var=_dp value=`$dept[i].description`}{/if}>{$dept[i].description}</option>
            {/section}
            </select>
           </div>
         
            <div class="col-md-3">
                <b class="form-label">Status [<a href="javascript:void(alert('Un-checkout: includes Saved & Waiting Approval and Approved GRA.'));">?</a>]</b>
            {assign var=_st value='All'}
            <select class="form-control" name=returned>
            <option value="">-- All --</option>
            <option value="0" {if $smarty.request.returned eq '0'}selected{assign var=_st value='Saved & Waiting Approval'}{/if}>Saved & Waiting Approval</option>
            <option value="1" {if $smarty.request.returned eq '1'}selected{assign var=_st value='Approved'}{/if}>Approved</option>
            <option value="2" {if $smarty.request.returned eq '2'}selected{assign var=_st value='Completed'}{/if}>Completed</option>
            <option value="3" {if $smarty.request.returned eq '3'}selected{assign var=_st value='Un-checkout'}{/if}>Un-checkout</option>
            </select>
            </div>

            <div class="col-md-3">
                <b class="form-label">Vendor</b>
            <select class="form-control" name=vendor_id>
            <option value="">-- All --</option>
            {section name=i loop=$vendor}
            <option value="{$vendor[i].id}" {if $smarty.request.vendor_id eq $vendor[i].id}selected{assign var=_vd value=`$vendor[i].description`}{/if}>{$vendor[i].description}</option>
            {/section}
           </div>
            </select>
            </div>
            <input class="btn btn-primary mt-3 ml-3" type=submit value="Refresh">
            
            </p>
            
            <p>
               <div class="mt-4 ml-1">
                {if $config.foreign_currency}
                * {$LANG.BASE_CURRENCY_CONVERT_NOTICE}<br />
            {/if}
            * The Amount Is Rounding-Adjustment Exclusive.
               </div>
            </p>
            
            </form>
        </div>
    </div>

<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">
                Date: From {$smarty.request.from|default:"-"} to {$smarty.request.to|default:"-"}

Department: {$_dp|default:"All"}

Vendor: {$_vd|default:"All"}

Status: {$_st|default:"All"}
            </h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>
<!-- print dialog -->
<div id=print_dialog style="background:#fff;border:3px solid #000;width:250px;height:140px;position:absolute; padding:10px; display:none;">
<form name=f_prn method=get>
<img src=ui/print64.png hspace=10 align=left> <h3>Print Options</h3>
<input type=hidden name=a value="print">
<input type=hidden name=load value=1>
<input type=hidden name=id value="">
<input type=hidden name=branch_id value="">
<input type=checkbox name="print_gra" checked> GRA Note (A5)<Br>
<div><Br>
<input type=checkbox name="own_copy" checked> Own Copy<Br>
<input type=checkbox name="vendor_copy" checked> Vendor Copy<Br>
</div>
<p align=center><input type=button value="Print" onclick="print_ok()"> <input type=button value="Cancel" onclick="print_cancel()">
</p>
</form>
</div>

<iframe width=1 height=1 style="visibility:hidden" name=ifprint></iframe>

{php}
show_report();
{/php}

{include file=footer.tpl}
