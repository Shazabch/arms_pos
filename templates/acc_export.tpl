{*
2016-01-13 11:12am Kee Kee
- Move "Grouy By" position

2016-03-07 11:32am Kee Kee
- Added date validation checking, when create export to account schedule

2016-03-07 10:40am Kee Kee
- Added legend to notify that SageUBS & Inter Application (IA) has removed from accounting software list

2016-11-07 9:40 AM Kee Kee
- Added Credit/IBT Sales & Credit Notes

2017-03-09 17:33 Qiu Ying
- Enhanced to add "change back to active" for archive

2017-03-16 17:16 Qiu Ying
- Enhanced to block exporting sales if got pos sales not yet finalise

12/18/2017 2:56 PM Andy
- Enhanced to show supported accounting software version (if have).

06/25/2020 10:44 AM Sheila
- Updated button css
*}
{include file=header.tpl}
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
{literal}
<style>
#data-result{
  overflow: auto;
}
/*
#data-result table {
    border-collapse: collapse;
    border: 1px solid #000;
}

#data-result table th, #data-result table td{
   border: 1px solid #000;
}
*/
#data-result table tr.selected td{
  background: #ffcece;
}

</style>
{/literal}
<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
<script src="js/jquery-1.7.2.min.js"></script>
<script>
var php_self="{$smarty.server.PHP_SELF}";
var global_groupby="{"|"|implode:$groupby}";
var myTimeout=0;
var debug="{$smarty.request.debug|default:0}";
var LOADING = '<img src=/ui/clock.gif align=absmiddle> ';
var selected_row=0;
var global_type=[];
{foreach from=$global_type key=k item=g}
global_type['{$k}']='{$g}';
{/foreach}

{literal}

var JQ = {};
JQ = jQuery.noConflict(true);

JQ(document).ready(function(){
	JQ('select[name="export_type"]').trigger('change');

	JQ('#data_type').on('change',function(){
		var data_type=JQ('#data_type').val();

		if (data_type=='cs') {
			JQ('#groupby_row').show();
		}
		else{
			JQ('#groupby_row').hide();
		}
	});
	JQ('#data_type').trigger('change');

	JQ('#data-form').on('submit',function(){
		if (myTimeout) clearTimeout(myTimeout);
		selected_row=0;
		JQ('#data-result tr').removeClass('selected highlight_row');

		JQ.ajaxSetup({ async:false });
		var form=JQ(this);
		JQ.ajax({
			type:'post',
			url:php_self+"?a=check_login",
			success:function(data){
			JQ.ajaxSetup({ async:true });
				if (data=='OK') {
					JQ.ajax({
						type:"post",
						dataType:"json",
						url: php_self+'?a=create_schedule',
						data: form.serialize(),
						success:function(ret){
							if(ret.status=="Error")
							{
								alert(ret.msg);
							}
							else if (ret.status=='OK') {
								if (form.find('#generate_now').val()==1) {
									start_now(parseInt(ret.id));
								}
							}
							else if(ret.status=='EXIST'){
								if(ret.data.archived==1) JQ('#schedule_type').val('archive');
								else JQ('#schedule_type').val('active');

								selected_row=ret.data.id;
								load_schedule();

								JQ('#data-row-'+ret.data.id).addClass('selected highlight_row');
						
								if (ret.data.started==1 && ret.data.completed==1) {
									if(ret.data.archived==1){
										alert("The file is currently existed and being archived, please change it to active before generate it.");
									}else{
										if (confirm("File is complete generated.\nDo you wish to regenerate?")) {
											start_now(ret.data.id,1);
										}
									}
								}
								else if(ret.data.started==1 && ret.data.completed==0){
									alert("In Progress");
								}
							}
							else{
								alert(ret);
							}
							load_schedule();
						}
					});
				}
				else{
					document.location.reload();
				}
			}
		});
		return false;
	});

	JQ('.tab a').on('click',function(e){
		e.preventDefault();
		JQ('.tab a').removeClass('active');
		JQ(this).addClass('active');
		JQ('#schedule_type').val(JQ(this).attr('data-type'));
		load_schedule();

	});

	load_schedule();
});

function load_schedule(){
	if (myTimeout) clearTimeout(myTimeout);

	var data="debug="+debug+"&selected_row="+selected_row+"&"+JQ('#filter-form').serialize();

	JQ.ajax({
		type:"post",
		url: php_self+"?a=load_schedule",
		data: data,
		success:function(data){
			JQ('#data-result').html(data);
			myTimeout=setTimeout(load_schedule,5000);
		}
	});
}

function change_format(){
	var GAF="GST Audit File (GAF)";
	var c_name=JQ('select[name="export_type"]').val();
	var obj=JQ('select[name="export_type"]').find('option:selected');
	var groupby=obj.attr('data-groupby');
	var selected=JQ('#groupby').attr('data-selected');

	if (groupby=="") groupby=global_groupby.split("|");
	else groupby=groupby.split("|");

	if (c_name==GAF) JQ('a[rel="gaf"]').show();

	var str="";
	for(i=0;i<groupby.length;i++){
		str+='<option value="'+groupby[i]+'"';
		if (selected==groupby[i]) {
			str+=" selected ";
		}

		str+='>'+groupby[i]+'</option>';
	}
	JQ('#groupby').html(str);

	str="";
	selected=JQ('#data_type').attr('data-selected');

	if (c_name==GAF){
		JQ('#data_type_row').hide();
		JQ('#data_type').html('<option value="gaf">GAF</option>');
	}
	else{
		JQ('#data_type_row').show();
		for(i in global_type){
			if (obj.attr('data-dataType-'+i)==1) {
				str+='<option value="'+i+'"';
				if (selected==i) {
					str+=" selected ";
				}

				str+='>'+global_type[i]+'</option>';
			}
		}
		JQ('#data_type').html(str);
	}
	JQ('#data_type').trigger('change');
}

function reset(id){
	if (confirm("Are you sure?")) {
		start_now(id, 1);
	}
	return false;
}

function remove_schedule(id){
	if (confirm("Are you sure?")) {
		JQ.ajax({
			type:"post",
			url: php_self+"?a=remove_schedule",
			data: { id:id },
			success:function(data){
				load_schedule();
			}
		});
	}
}

function archive(id){
	if (confirm("Are you sure?")) {
		JQ.ajax({
			type:"post",
			url: php_self+"?a=archive",
			data: { id:id },
			success:function(data){
				load_schedule();
			}
		});
	}
}

function start_now(id,reset){
	if (myTimeout) clearTimeout(myTimeout);
	if (reset==undefined) reset=0;

	JQ('#data-row-'+id+' .data-status').html("Pending");
	JQ('#data-row-'+id+' .data-file_size').html("0 B");
	JQ('#data-row-'+id+' .data-action').html(LOADING);

	JQ.ajax({
		type:"post",
		url: "acc_export.generate.php",
		data: { id:id,debug:debug,reset:reset },
		success:function(data){
			if (data) {
				alert(data);
			}
			load_schedule();
		},
		complete:function(){
			load_schedule();
		},
		timeout: 5000
	});

	return false;
}

function do_download(id){
	JQ('#_download').attr('src',php_self+"?a=download&id="+id);
	JQ('#_download2').attr('src',php_self+"?a=download&id="+id+"&file2=1");
}

function reactivate(id){
	if (confirm("Are you sure?")) {
		JQ.ajax({
			type:"post",
			url: php_self+"?a=reactivate",
			data: { id:id },
			success:function(data){
				load_schedule();
			}
		});
	}
}

</script>
{/literal}
<div id="message_box" style="z-index: 10000;position: absolute;display: none;background: #fff;padding: 10px;">
<h1>
  <img src=/ui/clock.gif align=absmiddle> <span>Processing...</span>
  <span id="message_box_status"></span>
</h1>
</div>
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

{if $err}
The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<div class="alert alert-danger mx-3 rounded">
	<li> {$e} </li>
</div>
{/foreach}
</ul>
{/if}
<div class="card mx-3">
	<div class="card-body">
		<form id="data-form" method="post" class="form" nama="f_a" onsubmit="return false">	
			<input type="hidden" name="debug" value="{if $smarty.request.debug}1{else}0{/if}"/>
			<input type="hidden" name="schedule_id" value="{$form.schedule_id|default:0}"/>
			<input type="hidden" name="user_id" value="{$sessioninfo.id}"/>
			<div class="row">
				<div class="col-md-3">
					<b class="form-label">From</b>
				<div class="form-inline">
					<input class="form-control"  type=text name=date_from value="{$form.date_from}" id="date_from">
					&nbsp;&nbsp;	<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
				</div>
				</div>
				
				<div class="col-md-3">
					<b class="form-label">To</b>
				 <div class="form-inline">
					<input class="form-control"  type=text name=date_to value="{$form.date_to}" id="date_to">
				&nbsp;&nbsp;	<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
				 </div>
				</div>
			
			<div class="col-md-6">
				<b class="form-label">Format</b> 
				<select class="form-control" name="export_type" onchange="change_format();">
				{foreach from=$accountings key=class item=a}
				<option data-groupby="{'|'|implode:$a.groupby}" data-dataType-cscn="{if $a.dataType.cscn}1{else}0{/if}" data-dataType-cs="{if $a.dataType.cs}1{else}0{/if}" data-dataType-arcn="{if $a.dataType.arcn}1{else}0{/if}" data-dataType-ar="{if $a.dataType.ar}1{else}0{/if}" data-dataType-cn="{if $a.dataType.cn}1{else}0{/if}" data-dataType-ap="{if $a.dataType.ap}1{else}0{/if}" data-dataType-dn="{if $a.dataType.dn}1{else}0{/if}" value="{$class}" {if $form.export_type eq $class}selected="selected"{/if}>
					{$class}
					{if $a.version}({$a.version}){/if}
				</option>
				{/foreach}
			  </select> 
			</div>
			<div class="col-md-6">
			  <div id="data_type_row">
				
					<b class="form-label">Data Type</b>
				<select class="form-control" id="data_type" name="data_type" data-selected="{$form.data_type|default:"cs"}">
				</select>
				</div>
			</div> 
				<div class="col-md-6">
					<span id="groupby_row" style="margin: 0 0 0 20px;">
						<b class="form-label">Group By</b> 
						<select class="form-control" id="groupby" name="groupby" data-selected="{$form.groupby}">
						  {foreach from=$groupby item=name}
						  <option value="{$name}" {if $form.groupby eq $name}selected="selected"{/if}>{$name}</option>
						  {/foreach}
						</select></span>
				</div>
			  
	
			  <div class="col-md-6">
				{if $branches && !$is_consignment}
				<b class="form-label">Branch</b>
				<select class="form-control" name="branch_id">
				  {foreach name="branch" from=$branches item=b}
				  <option value="{$b.id}">{$b.code}</option>
				  {/foreach}
				</select>
				<br/>
				{else}
				  <input type="hidden" name="branch_id" value="{$form.branch_id.0}"/>
				{/if}
			  </div>
	
			 <div class="col-md-6">
				<b class="form-label">Generate Now</b>
				<select class="form-control" id="generate_now" name="generate_now">
				  <option value="0">No</option>
				  <option value="1">Yes</option>
				</select>
			 </div>
			</div>
		  
		  <button class="btn btn-primary mt-2" name="">Generate</button>
		  <br/>
		 <div class="alert alert-danger mx-3 mt-3">
			<span >Please note that SageUBS and IA Accounting Software is no longer under our Accounting Software Export Support List.<br/>For any remaining Accounting Software listed, their compatibility is only verified until the 1st of July 2016.<br/>ARMS Software will not be held liable for any compatibility issues resulting from any accounting software upgrades from that period after.
		 </div>
		</span>
		</form>
		
	</div>
</div>
<div class="row mx-3">
	<div style="padding:10px 0;">
		<div class="tab" style="white-space:nowrap;">
		  <a href="javascript:void(0);" data-type="active" class="btn btn-outline-primary btn-rounded">Active</a>
		  <a href="javascript:void(0);" data-type="archive" class="btn btn-outline-primary btn-rounded" >Archive</a>
		</div>
		<div id="div_grn mt-3">
		  <div id="data-result">
	  
		  </div>
		</div>
	  </div>
	  
</div>
<iframe id="_download" style="visibility: hidden;width:1px;height: 1px;" src=""></iframe>
<iframe id="_download2" style="visibility: hidden;width:1px;height: 1px;" src=""></iframe>

{include file=footer.tpl}
{literal}
<script type="text/javascript">
  Calendar.setup({
      inputField     :    "date_from",     // id of the input field
      ifFormat       :    "%Y-%m-%d",      // format of the input field
      button         :    "t_added1",  // trigger for the calendar (button ID)
      align          :    "Bl",           // alignment (defaults to "Bl")
      singleClick    :    true,
      onClose        :    function(cal){
        JQ('#data-form').find('select').removeAttr('style');
        cal.hide();
      }
  });

  Calendar.setup({
      inputField     :    "date_to",     // id of the input field
      ifFormat       :    "%Y-%m-%d",      // format of the input field
      button         :    "t_added2",  // trigger for the calendar (button ID)
      align          :    "Bl",           // alignment (defaults to "Bl")
      singleClick    :    true,
      onClose        :    function(cal){
        JQ('#data-form').find('select').removeAttr('style');
        cal.hide();
      }
  });
</script>
{/literal}
