{include file="header.tpl"}
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
<h1>{$PAGE_TITLE}</h1>
<p>
<table>
  <form id="filter_form" onsubmit="return false">    
    <tr>
      <th align="left">Receipt Number</th>
      <td><input type="input" name="sf[receipt_number]" value=""/> </td>      
    </tr>    
    <tr>
      <th align="left">Date From</th>
      <td><input type="input" id="date_from" name="sf[date_from]" value="{$smarty.now|date_format:"%Y-%m-%d"}"/><img align=absbottom src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date"/></td>
      <th align="left">To</th>
      <td><input type="input" id="date_to" name="sf[date_to]" value="{$smarty.now|date_format:"%Y-%m-%d"}"/><img align=absbottom src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date"/></td>
    </tr>    
    <tr>
      <th align="left">Keyword</th>
      <td colspan="3">
        <select name="sf[keyword_type]" onchange="toggleInput(this,'keyword')" style="float:left;">
          <option value="">-- Please Select --</option>
          <option value="remark">Remark</option>
          <option value="delivery_name">Receipient</option>
          <option value="delivery_address">Delivery Address</option>
          <option value="delivery_phone">Contact</option>
        </select><input type="text" id="keyword" name="sf[keyword]" value="" style="display: none;float:left;width:200px;margin: 0 0 0 10px;"/></td>
    </tr>
    <tr>
      <th align="left">Status</th>
      <td>
        <select name="sf[status]">
          <option value="">All</option>
          <option value="1">Delivered</option>
          <option value="0">Undelivered</option>
        </select>
      </td>
      <td colspan="2">
        <input type="button" value="Reload" onclick="javascript:reload_list();"/>        
      </td>
    </tr>    
 </form>
</table>
</p>

<div id="udiv">

</div>
<form name=f_print target="_blank" method="get" action="membership.delivery.php">
<input type="hidden" name="a" value="print_delivery">
<input type="hidden" name="counter_id" value=""/>
<input type="hidden" name="branch_id" value=""/>
<input type="hidden" name="pos_id" value=""/>
<input type="hidden" name="date" value=""/>
<input type="hidden" name="type" value=""/>
</form>
<iframe name="_print" style="width:1px;height:1px;visibility: hidden;"></iframe>
<script>
{literal}
init_calendar();
reload_list();
function reload_list()
{
  var str="";
  var elem = document.getElementById('filter_form').elements;
  str=formserialize(elem);
  
  if(document.getElementById('page')!=null) str+="&s="+document.getElementById('page').value;
      
  var jax = new Ajax.Updater(
    "udiv", "membership.delivery.php",
    {
        method: 'get',
        parameters: "a=reload"+str,
        onLoading: function() { $('udiv').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...' },
    });
}

function formserialize(elem){
  var str="";
  for(var i = 0; i < elem.length; i++)
  {
    if(elem[i].type=='button') continue;
    
    if(elem[i].value!=""){
      str+="&"+elem[i].name+"="+elem[i].value;
    }     
  }
  return str;
}

function init_calendar(){
  Calendar.setup({
    inputField     :    "date_from",     // id of the input field
    ifFormat       :    "%Y-%m-%d",      // format of the input field
    button         :    "img_date_from",  // trigger for the calendar (button ID)
    align          :    "Bl",           // alignment (defaults to "Bl")
    singleClick    :    true
  });
  
  Calendar.setup({
    inputField     :    "date_to",     // id of the input field
    ifFormat       :    "%Y-%m-%d",      // format of the input field
    button         :    "img_date_to",  // trigger for the calendar (button ID)
    align          :    "Bl",           // alignment (defaults to "Bl")
    singleClick    :    true
  });
}

function toggleInput(obj,id){
  if(obj.value!=""){
    document.getElementById(id).style.display="block";
  }
  else{
    document.getElementById(id).value="";
    document.getElementById(id).style.display="none";
  }
}

function d_print(counter_id,branch_id,pos_id,date,type){
  if(!confirm('Click OK to print'))   return false;
  document.f_print['counter_id'].value = counter_id;
  document.f_print['branch_id'].value = branch_id;
  document.f_print['pos_id'].value = pos_id;
  document.f_print['date'].value = date;
  document.f_print['type'].value = type;
  document.f_print.submit();
  setTimeout(reload_list(),2000);
}
{/literal}
</script>
{include file="footer.tpl"}