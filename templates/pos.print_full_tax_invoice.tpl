{*
05/05/2016 11:00 Edwin
- Enhanced on auto fill in member info in tax invoice remark based on membership number.

05/12/2016 17:30 Edwin
- Added "Search Last Receipt" based on branch

11/30/2016 4:20 PM Andy
- Enhanced to always have default tax invoice remark value. (Name, Address, BRN, GST Reg No)

9/4/2018 5:09 PM Andy
- Enhanced "Print Full Tax Invoice" to able to print non-gst transaction.
*}
{include file=header.tpl}
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<!-- the following script defines the Calendar.setup helper function, which makes adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
<script src="js/jquery-1.7.2.min.js"></script>
<script>
var php_self="{$smarty.server.PHP_SELF}";
var LOADING = '<img src=/ui/clock.gif align=absmiddle> ';
var full_tax_member_config = int('{$config.full_tax_invoice_use_member_info}');
{literal}
var JQ = {};
JQ = jQuery.noConflict(true);

JQ(document).ready(function(){
  JQ('#branch_id').trigger('change');
});

function load_receipt(t) {
  var ref_no = JQ('#ref_no').val();
  var branch_id = JQ('#branch_id').val();
  
  JQ('#branch_id').val(JQ('#branch_id').data('default_branch_id'));
  if (t == 'last_receipt') {
    JQ('#ref_no').val("");
  }
  
  if ((t == "search" && ref_no != "") || (t == "last_receipt" && branch_id != "")) {
    JQ('#counter').attr("data-selected","");
    JQ('#counter').val("");
    JQ('#date').val("");
    JQ('#receipt_no').val("");

    JQ('#loading').html(LOADING);
    JQ.ajaxSetup({ async:false });

    JQ.getJSON(php_self+"?a=load_receipt",{ type:t, ref_no:ref_no, branch_id:branch_id },function(data){
      JQ.ajaxSetup({ async:true });
      if (data.branch_id!=undefined) {
        JQ('#branch_id').val(data.branch_id);
        JQ('#counter').attr("data-selected",data.counter_id);
        JQ('#date').val(data.date);
        JQ('#receipt_no').val(data.receipt_no);
        JQ('#card_no').val(data.card_no);
        load_counter('load');
      }
      else{
        if (data.msg){
          alert(data.msg);
        }
        else
          alert("Receipt not found.");
      }
      JQ('#loading').html('');
    });
  }
  else{
    if (t == "search")  alert("Please enter Ref. No.");
    else  alert("Please select branch");
  }
}

function load_counter(t) {
  var branch_id=JQ('#branch_id').val();
  var selected=JQ('#counter').attr('data-selected');
  
  if (t == undefined) {
    JQ('#date').val("");
    JQ('#receipt_no').val("");
  }
  
  if (branch_id>0) {
    JQ('#loading2').html(LOADING);
    JQ.ajaxSetup({ async:false });
    JQ.getJSON(php_self+"?a=load_counter",{ branch_id:branch_id },function(data){
      var str='<option value="">-- Please Select --</option>';
      for(i in data){
        str+='<option value="'+data[i].id+'"';

        if (selected==data[i].id) {
          str+=' selected="selected"';
        }
        str+='">'+data[i].network_name+'</option>';
      }
      JQ('#counter').html(str);
      JQ.ajaxSetup({ async:true });
      JQ('#loading2').html('');
    });
    JQ('#last_receipt').prop('disabled', false);
  }else {
    JQ('#last_receipt').prop('disabled', true);
  }
}

var PRINT_FULL_TAX = {
	show_remark: function(){
		var form = JQ("#data-form");
    
		if (JQ('#branch_id').val()=="") {
		  alert("Please select Branch");
		  JQ('#branch_id').focus();
		  return false;
		}

		if (JQ('#date').val()=="") {
		  alert("Please select Date");
		  JQ('#date').focus();
		  return false;
		}

		if (JQ('#counter').val()=="") {
		  alert("Please select Counter");
		  JQ('#counter').focus();
		  return false;
		}

		if (JQ('#receipt_no').val()=="") {
		  alert("Please enter receipt no.");
		  JQ('#receipt_no').focus();
		  return false;
		}

		curtain(true, 'curtain2');
		JQ.getJSON(php_self+"?a=check",form.serialize(),function(data){
		  if (data.msg=="OK"){
			JQ("#div_remark_content").html(data['html']);
			center_div($('div_remark').show());	// reposition to center
		  }
		  else{
			alert(data.msg);
			curtain(false, 'curtain2');
		  }
		});

		return false;
	},
	print_start: function(print_type){
		var form = JQ('#data-form-remark');
		var data = JQ('#data-form');
		var input=form.find('input[type="text"]');
		var THIS = this;
		var result=false;
		
		if(!check_required_field($('data-form-remark')))	return false;
		
		JQ.getJSON(php_self+"?a=save_remark",data.serialize()+"&"+form.serialize(),function(data){
		  if (data.msg=="OK"){
			THIS.close_remark();
			window.open(php_self+"?a=show&type="+print_type+"&"+data.url);
		  }
		  else{
			alert(data.msg);
		  }
		});
		return false;
	},
	close_remark: function(){
		hidediv('div_remark');
		curtain(false, 'curtain2');
	}
}
{/literal}
</script>
<pre></pre>
<div class="ndiv" id="div_remark" style="position:absolute;left:150;top:150;display:none;z-index:10000;">
  <div class="blur">
    <div class="shadow">
      <div class="content">
        <div style="height:20px;background-color:#6883C6;position:absolute;left:0;top:0;width:100%;color:white;font-weight:bold;padding:2px;" id="div_remark_header">
          <div class="small" style="position:absolute; right:10; text-align:right;top:2px;"><a href="javascript:void(PRINT_FULL_TAX.close_remark())"><img src="ui/closewin.png" border="0" align="absmiddle"></a></div>
          Tax Invoice Remark
        </div>
        <div id="div_remark_content" style="margin-top:20px;min-width:100px;min-height:110px;">
          
        </div>
      </div>
    </div>
  </div>
</div>


<div class="breadcrumb-header justify-content-between">
  <div class="my-auto">
      <div class="d-flex">
          <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
      </div>
  </div>
</div>

<div class="card mx-3">
  <div class="card-body">
    <form id="data-form" method=post class=form name="f_a" onsubmit="return false;">
      
    <div class="row">
      <div class="col-md-6 mt-2">
        <div class="form-inline">
          <b class="form-label">Search receipt by Ref. No.&nbsp;</b> 
          <input class="form-control" type="text" id="ref_no" name="ref_no" value=""/> 
         &nbsp;&nbsp; <button class="btn btn-primary" type="button" onclick="load_receipt('search')">Search</button>
        </div>
          <span id="loading"></span>
          <input type="hidden" id="card_no" name="card_no" value="">
      </div>
   
      
       <div class="col-md-6 mt-2">
        {if $branches}
        <div class="form-inline">
          <b class="form-label">Branched</b>
        &nbsp;<select class="form-control" id="branch_id" name="branch_id" onchange="load_counter()">
          <option value="">-- Please Select --</option>
          {foreach name="branch" from=$branches item=b}
            <option value="{$b.id}">{$b.code}</option>
          {/foreach}
        </select> 
       <span class="text-danger"  title="Required Field"> *</span> <span id="loading2"></span>
       &nbsp; <button class="btn btn-primary" type="button" id="last_receipt" onclick="load_receipt('last_receipt')">Search Last Receipt</button>
        
        </div>
      {else}
        <input type="hidden" id="branch_id" name="branch_id" value="{$form.branch_id|default:$sessioninfo.branch_id}" data-default_branch_id="{$sessioninfo.branch_id}"  onchange="load_counter()"/>
        <button type="button" id="last_receipt" onclick="load_receipt('last_receipt')">Search Last Receipt</button><br />
      {/if}
       </div>
  
  
       <div class="col-md-6 mt-2">
        <div class="form-inline">
          <b class="form-label">Date</b> 
          &nbsp;<input class="form-control" size=22 type=text name=date value="{$form.date}" id="date"/>
          &nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date"/> 
          <span class="text-danger" title="Required Field"> *</span>
         </div>
       </div>
        
       
          <div class="col-md-6 mt-2">
            <div id="data_type_row">
              <div class="form-inline">
              <b class="form-label">Counter</b>
              &nbsp;<select class="form-control" id="counter" name="counter_id" data-selected="">
                <option value="">-- Please Select --</option>
              </select> <span class="text-danger" title="Required Field"> *</span>
            </div>
            </div>
          </div>
    
  
        <div class="col-md-6 mt-2">
          <div class="form-inline">
            <b class="form-label">Receipt No.</b> 
           &nbsp; <input class="form-control" type="text" name="receipt_no" id="receipt_no" value=""/> 
          &nbsp; <span class="text-danger" title="Required Field"> *</span>
          &nbsp;<button class="btn btn-primary" type="button" id="btn-submit" onclick="PRINT_FULL_TAX.show_remark();">Show</button>
          </div>
        </div>
    </div>
    </form>
  </div>
</div>
{include file=footer.tpl}
{literal}
<script type="text/javascript">
  Calendar.setup({
      inputField     :    "date",     // id of the input field
      ifFormat       :    "%Y-%m-%d",      // format of the input field
      button         :    "t_added1",  // trigger for the calendar (button ID)
      align          :    "Bl",           // alignment (defaults to "Bl")
      singleClick    :    true,
      onClose        :    function(cal){
        JQ('#counter').removeAttr('style');
        cal.hide();
      }
  });

  new Draggable('div_remark', { handle: 'div_remark_header'});
</script>
{/literal}
