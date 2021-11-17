{*
2/24/2010 11:04:11 AM Andy
- add print function

4/15/2010 2:03:54 PM Andy
- Add receipt able to filter by SKU

4/19/2010 11:48:43 AM Andy
- add to show pos time and date

4/20/2010 10:16:11 AM Andy
- add skip department control on sku items search

5/18/2011 3:51:35 PM Andy
- Add pass document FORM name when include the autocomplete templates

11/19/2012 10:36 AM Andy
- Fix not to group same payment type.

3/7/2014 5:34 PM Justin
- Enhanced to have new feature that print prefix receipt no if found config set.

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
option.bg{
	font-weight:bold;
	padding-left:10px;
}

option.bg_item{
	padding-left:20px;
}
.receipt_header{
  background: yellow;
}
.receipt_header2{
  background: #ffffcc;
}
{/literal}
</style>

<script>
{literal}
function toggle_receipt(id,ele){
  if(ele.src.indexOf('expand')>0){
    $('tbody_pos_'+id).show();
    ele.src = '/ui/collapse.gif';
  }else{
    $('tbody_pos_'+id).hide();
    ele.src = '/ui/expand.gif';
  }
}

function print_report(){
  document.f_a.target = '_blank';
  document.f_a.method = 'get';
  document.f_a['is_print'].value = '1';
  document.f_a['show_report'].click();
  
  document.f_a.target = '';
  document.f_a.method = 'post';
  document.f_a['is_print'].value = 0;
  return false;
}

function form_submit(){
	var sel = $('sku_code_list');
	for(var i=0; i<sel.length; i++){
		sel.options[i].selected = true;
	}
	return true;
}

function toggle_filter_sku(){
	var checked = document.f_a['filter_sku'].checked;
	if(checked) $('div_sku_items').show();
	else    $('div_sku_items').hide();
}
{/literal}
</script>
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
<iframe name="fprint" width="1" height="1" style="visibility:hidden;"></iframe>

<div class="card mx-3">
  <div class="card-body">
    <form method=post class=form name="f_a" onSubmit="return form_submit();">
      <input type="hidden" name="is_print" />
      <input type=hidden name=report_title value="{$report_title}">
      
      <div class="row">
        <div class="col-md-3">
          <b class="form-label">From</b> 
       <div class="form-inline">
        <input class="form-control" size=16 type=text name=date_from value="{$smarty.request.date_from}" id="date_from">
        &nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
       </div>
        </div>
        
       <div class="col-md-3">
        <b class="form-label">To</b> 
       <div class="form-inline">
        <input class="form-control" size=16 type=text name=date_to value="{$smarty.request.date_to}" id="date_to">
        &nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
       </div>
       </div>
        
        
        {if $BRANCH_CODE eq 'HQ'}
              <div class="col-md-3">
                <b class="form-label">Branch</b>
            <select class="form-control" name="branch_id">
                <option value="">-- All --</option>
                {foreach from=$branches key=bid item=b}
                    {if !$branch_group.have_group.$bid}
                      <option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code} - {$b.description}</option>
                {/if}
                {/foreach}
                {if $branch_group.header}
                <optgroup label="Branches Group">
                  {foreach from=$branch_group.header key=bgid item=bg}
                      <option class="bg" value="{$bgid*-1}"{if $smarty.request.branch_id eq ($bgid*-1)}selected {/if}>{$bg.code}</option>
                      {foreach from=$branch_group.items.$bgid item=r}
                          <option class="bg_item" value="{$r.branch_id}" {if $smarty.request.branch_id eq $r.branch_id}selected {/if}>{$r.code} - {$r.description}</option>
                      {/foreach}
                  {/foreach}
                {/if}
                </optgroup>
            </select>
              </div>
        {/if}
  
        <div class="col-md-3">
          <b class="form-label">Card No</b> 
        <input class="form-control" type="text" name="member_no" value="{$smarty.request.member_no}" />
        </div>
        
      </div>
     <div class="form-label form-inline">
      <input type="checkbox" name="filter_sku" align="absmiddle" onChange="toggle_filter_sku();" {if $smarty.request.filter_sku}checked {/if} /> <b>&nbsp;Filter Receipt with SKU</b>
     </div>
      <div id="div_sku_items" style="border:1px solid #cfcfcf; background: #efefef;display:none;">
      {include file='sku_items_autocomplete_multiple_add2.tpl' skip_dept_filter=1 parent_form='document.f_a'}
      </div>
      <p>
      <input type=hidden name=submit value=1>
      <button class="btn btn-primary mt-2" name=show_report>{#SHOW_REPORT#}</button>
      {if $sessioninfo.privilege.EXPORT_EXCEL}
      <button class="btn btn-info mt-2" name=output_excel>{#OUTPUT_EXCEL#}</button>
      {/if}
      <button class="btn btn-primary mt-2" onClick="return print_report();">Print</button>
      <div class="alert alert-primary rounded" style="max-width: 300px;">
        <b>Note:</b> Report Maximum Shown 1 Year
      </div>
      </p>
      </form>
  </div>
</div>
<script>toggle_filter_sku()</script>
{/if}
{if !$pos}
  {if $smarty.request.submit && !$err}-- No data --{/if}
{else}
<div class="breadcrumb-header justify-content-between">
  <div class="my-auto">
      <div class="d-flex">
          <h4 class="content-title mb-0 my-auto ml-4 text-primary">
            {$report_title}
{*Date Sales: from {$smarty.request.date_from} to {$smarty.request.date_to} 
Membership No: {$member_info.card_no} {$member_info.name}*}
          </h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
      </div>
  </div>
</div>
{assign var=row_counter value=0}
<div class="card mx-3">
  <div class="card-body">
    <div class="table-responsive">
      <table width="100%" class="report_table">
        <thead class="bg-gray-100">
          <tr class="header">
            <th>Receipt No</th>
            <th>Counter</th>
            <th>Date</th>
            <th>Branch</th>
            <th colspan="3">Amount</th>
          </tr>
        </thead>
        {foreach from=$pos key=bid item=b}
          {foreach from=$b key=date item=c}
            {foreach from=$c key=cid item=p}
              {foreach from=$p key=pid item=r}
                {assign var=row_counter value=$row_counter+1}
                {assign var=pos_amt value=0}
                <tr class="receipt_header">
                  <td>{receipt_no_prefix_format branch_id=$r.branch_id counter_id=$r.counter_id receipt_no=$r.receipt_no}
                    {if !$no_header_footer}
                    <img src="/ui/expand.gif" align="absmiddle" title="Toggle Receipt Details" class="clickable" onClick="toggle_receipt('{$row_counter}', this)" />
                    {/if}
                  </td>
                  <td>{$r.network_name}</td>
                  <td>{if $r.pos_time|date_format:'%Y-%m-%d' ne $r.date}{$r.date} <span class="small" style="color:blue;">({$r.pos_time})</span>{else}{$r.pos_time}{/if}</td>
                  <td>{$branches.$bid.code}</td>
                  <td colspan="3" class="r">{$r.amount|number_format:2}</td>
                </tr>
                <tbody class="fs-08" id="tbody_pos_{$row_counter}" style="display:none;">
                  <tr class="receipt_header2">
                    <th rowspan="2">ARMS Code</th>
                    <th rowspan="2">Mcode</th>
                    <th rowspan="2">Description</th>
                    <th rowspan="2">Qty</th>
                    <th colspan="3">Price</th>
                  </tr>
                  <tr class="receipt_header2">
                    <th>Actual</th>
                    <th>Discount</th>
                    <th>Selling</th>
                  </tr>
                  {foreach from=$pos_items.$bid.$date.$cid.$pid item=pi}
                    <tr class="{if is_array($smarty.request.sku_code_list)}{if in_array($pi.sku_item_code, $smarty.request.sku_code_list)}highlight_row{/if}{/if}">
                      <td>{$pi.sku_item_code}</td>
                      <td>{$pi.mcode}</td>
                      <td>{$pi.sku_description}</td>
                      <td class="r">{$pi.qty}</td>
                      <td class="r">{$pi.price|number_format:2}</td>
                      <td class="r">{$pi.discount|number_format:2}</td>
                      <td class="r">{$pi.price-$pi.discount|number_format:2}</td>
                      {assign var=pos_amt value=$pos_amt+$pi.price-$pi.discount}
                    </tr>
                  {/foreach}
                  <tr>
                    <td colspan="4">&nbsp;</td>
                    <td colspan="2">Total</td>
                    <td class="r">{$pos_amt|number_format:2}</td>
                  </tr>
                  {if $pos_payment.$bid.$date.$cid.$pid.rounding}
                    <tr>
                      <td colspan="4">&nbsp;</td>
                      <td colspan="2">Rounding</td>
                      <td class="r">{$pos_payment.$bid.$date.$cid.$pid.rounding.amount|number_format:2}</td>
                    </tr>
                  {/if}
                  {foreach from=$pos_payment.$bid.$date.$cid.$pid.pp item=py}
                    {assign var=py_type value=$py.type}
                    {if $py_type ne 'rounding'}
                      <tr>
                        <td colspan="4">&nbsp;</td>
                        <td colspan="2">{$py.type} {if $py.remark}({$py.remark}){/if}</td>
                        <td class="r">{$py.amount|number_format:2}</td>
                      </tr>
                    {/if}
                  {/foreach}
                  <tr>
                    <td colspan="4">&nbsp;</td>
                    <td colspan="2">Change</td>
                    <td class="r">{$r.amount_change|number_format:2}</td>
                  </tr>
                  <tr style="border-left:1px solid white !important;">
                    <td colspan="7" style="border-right:none;">&nbsp;</td>
                  </tr>
                </tbody>
              {/foreach}
            {/foreach}
          {/foreach}
        {/foreach}
        <tr class="header">
          <th colspan="6" class="r">Total</th>
          <th class="r">{$total.selling|number_format:2}</th>
        </tr>
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
    document.myForm = document.f_a;
</script>
{/literal}
{/if}

{include file=footer.tpl}

