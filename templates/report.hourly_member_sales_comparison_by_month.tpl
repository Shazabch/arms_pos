{*
06/30/2020 02:25 PM Sheila
- Updated button css.
*}

{include file=header.tpl}
{if !$no_header_footer}
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
{literal}
.c1 { background:#ff9; }
.c2 { background:none; }
{/literal}
</style>
{/if}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

{if $err}
<div class="alert alert-danger mx-3 rounded">
    The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul>
</div>
{/if}
{if !$no_header_footer}
<div class="card mx-3">
    <div class="card-body">
        <form method=post class=form>
            <input type=hidden name=report_title value="{$report_title}">

          <div class="row">
           <div class="col-md-3 mt-2">
            <b class="form-label">From</b> 
            <div class="form-inline">
             <input class="form-control" size=15 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
            <img class="ml-0 ml-md-2" align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
            </div>
           </div>
             
             <div class="col-md-3 mt-2">
                <b class="form-label">To</b> 
                <div class="form-inline">
                 <input class="form-control" size=15 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
                 <img class="ml-0 ml-md-2" align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
                </div>
             </div>
             
             
             <div class="col-md-3 mt-2">
                {if $BRANCH_CODE eq 'HQ'}
                <b class="form-label">Branch</b> 
                <select class="form-control" name="branch_id">
                        <option value="">-- All --</option>
                        {foreach from=$branches item=b}
                            {if !$branch_group.have_group[$b.id]}
                            <option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
                            {/if}
                        {/foreach}
                        {if $branch_group.header}
                            <optgroup label="Branch Group">
                                {foreach from=$branch_group.header item=r}
                                    {capture assign=bgid}bg,{$r.id}{/capture}
                                    <option value="bg,{$r.id}" {if $smarty.request.branch_id eq $bgid}selected {/if}>{$r.code}</option>
                                {/foreach}
                            </optgroup>
                        {/if}
                    </select>
                {/if}
             </div>
             
             <div class="col-md-3 mt-2">
                <b class="form-label">By</b>
                <select class="form-control" name="view_type">
                <option value="month" {if $smarty.request.view_type eq 'month'}selected{/if}>Month</option>
                <option value="day" {if $smarty.request.view_type eq 'day'}selected{/if}>Day</option>
                </select>
             </div>
             </div>

            <input type=hidden name=submit value=1>
            <button class="btn btn-primary mt-2" name=show_report>{#SHOW_REPORT#}</button>
            {if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
            <button class="btn btn-info mt-2" name=output_excel>{#OUTPUT_EXCEL#}</button>
            {/if}
            <div class="alert alert-primary rounded mt-2" style="max-width: 300px;">
                <b>Note:</b> Report Maximum Shown 1 month if filter by days
            </div>
            </form>
    </div>
</div>
{/if}
{if !$table}
{if $smarty.request.submit && !$err}-- No data --{/if}
{else}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$report_title}

                <!--Branch: {$branch_name}
                Date: from {$smarty.request.date_from} to {$smarty.request.date_to}
                View by: {$smarty.request.view_type|capitalize}--></h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

{capture assign=hrcount}{count var=$hour}{/capture}
<div class="card mx-3">
    <div class="card-body">
        <div class="table-responsive">
            <table class="report_table table mb-0 text-md-nowrap  table-hover" width=100%>
                <thead class="bg-gray-100">
                    <tr class=header>
                        <th>MONTHS</th>
                        <!--<th>9:00AM</th>
                        <th>10:00AM</th>
                        <th>11:00AM</th>
                        <th>12:00PM</th>
                        <th>1:00PM</th>
                        <th>2:00PM</th>
                        <th>3:00PM</th>
                        <th>4:00PM</th>
                        <th>5:00PM</th>
                        <th>6:00PM</th>
                        <th>7:00PM</th>
                        <th>8:00PM</th>
                        <th>9:00PM</th>
                        <th>10:00PM</th>
                        <th>11:00PM</th>
                        <th>12:00PM</th>-->
                        
                        {foreach from=$hour item=h}
                        <th>{$h}</th>
                      {/foreach}
                        <th>Total</th>
                        <th>AVG Hour Amount</th>
                    </tr>
                </thead>
                {foreach from=$table key=k item=m}
                    {cycle values="c2,c1" assign=row_class}
                   <tbody class="fs-08">
                    <tr class="{$row_class}">
                        <!-- Month, Year and Total -->
                        <th>{$label[$k]}</th>
                        {*assign var=n value=8*}
                        {*section loop=16 name=x*}
                        <!-- {$n++} -->
                        {foreach from=$hour key=hr item=h}
                        <td class=r>{$m.$hr.total.amount|number_format:2|ifzero:"-"}</td>
                        {/foreach}
                        {*/section*}
                
                        <td class=r>{$m.total.total.amount|number_format:2|ifzero:"-"}</td>
                        <td class=r>{$m.total.total.amount/$hrcount|number_format:2|ifzero:"-"}</td>
                    </tr>
                    <tr class="{$row_class}">
                        <!-- Member % and Member Total -->
                        <td nowrap>Member%</td>
                        {*assign var=n value=8*}
                        {*section loop=16 name=x*}
                        <!-- {$n++} -->
                        {foreach from=$hour key=hr item=h}
                        <td class=r>{if $m.$hr.total.amount>0 and $m.$hr.MEMBER.amount>0}{$m.$hr.MEMBER.amount/$m.$hr.total.amount*100|number_format:2|ifzero:"-"}%{else}-{/if}</td>
                        {/foreach}
                        {*/section*}
                        <td class=r>{if $m.total.MEMBER.amount>0}{$m.total.MEMBER.amount/$m.total.total.amount*100|number_format:2|ifzero:"-"}%{else}-{/if}</td>
                        <td class=r>{$m.total.MEMBER.amount/$m.total.total.amount*100/$hrcount|number_format:2|ifzero:"-"}%</td>
                    </tr>
                    <tr class="{$row_class}">
                        <!-- Non-Member % and Non-Member Total -->
                        <td nowrap>Non-Member%</td>
                        {*assign var=n value=8*}
                        {*section loop=16 name=x*}
                        <!-- {$n++} -->
                        {foreach from=$hour key=hr item=h}
                        <td class=r>{if $m.$hr.total.amount>0 and $m.$hr.NON_MEMBER.amount>0}{$m.$hr.NON_MEMBER.amount/$m.$hr.total.amount*100|number_format:2|ifzero:"-"}%{else}-{/if}</td>
                        {/foreach}
                        {*/section*}
                        <td class=r>{$m.total.NON_MEMBER.amount/$m.total.total.amount*100|number_format:2|ifzero:"-"}%</td>
                        <td class=r>{$m.total.NON_MEMBER.amount/$m.total.total.amount*100/$hrcount|number_format:2|ifzero:"-"}%</td>
                    </tr>
                    <tr class="{$row_class}">
                        <!-- Transaction Count -->
                        <td nowrap>Transaction Count</td>
                        {*assign var=n value=8*}
                        {*section loop=16 name=x*}
                        <!-- {$n++} -->
                        {foreach from=$hour key=hr item=h}
                        <td class=r>{$m.$hr.total.transaction_count|number_format|ifzero:"-"}</td>
                        {/foreach}
                        {*/section*}
                        <td class=r>{$m.total.total.transaction_count|number_format|ifzero:"-"}</td>
                        <td class=r>{$m.total.total.transaction_count/$hrcount|number_format:2|ifzero:"-"}</td>
                    </tr>
                    <tr class="{$row_class}">
                         <!-- Buying Power -->
                        <td nowrap>Buying Power</td>
                        {*assign var=n value=8*}
                        {*section loop=16 name=x*}
                        <!-- {$n++} -->
                        {foreach from=$hour key=hr item=h}
                        <td class=r>{if $m.$hr.total.transaction_count>0}{$m.$hr.total.amount/$m.$hr.total.transaction_count|number_format:2|ifzero:"-"}{else}-{/if}</td>
                        {/foreach}
                        {*/section*}
                        <td class=r>{if $m.total.total.transaction_count>0}{$m.total.total.amount/$m.total.total.transaction_count|number_format:2|ifzero:"-"}{/if}</td>
                        <td class=r>{if $m.total.total.transaction_count>0}{$m.total.total.amount/$m.total.total.transaction_count/$hrcount|number_format:2|ifzero:"-"}{/if}</td>
                    </tr>
                   </tbody>
                {/foreach}
                
                </table>
        </div>
    </div>
</div>
{/if}
{if !$no_header_footer}
{literal}
<script type="text/javascript">


    Calendar.setup({
        inputField     :    "date_from",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });

    Calendar.setup({
        inputField     :    "date_to",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });

</script>
{/literal}
{/if}
{include file=footer.tpl}

