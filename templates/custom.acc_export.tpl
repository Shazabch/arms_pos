{*
2017-04-21 10:41 AM Qiu Ying
- Bug fixed on error message shown in file when download empty file
*}

{include file="header.tpl"}
<style>
{literal}
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
{/literal}
</style>
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
<link type="text/css" href="include/css/base/jquery-ui.min.css" rel="stylesheet" />
<script src="js/jquery-1.7.2.min.js"></script>
<script src="js/jquery-ui.min.js"></script>

<script type="text/javascript">
var php_self="{$smarty.server.PHP_SELF}";
var myTimeout=0;
var debug="{$smarty.request.debug|default:0}";
var LOADING = '<img src=/ui/clock.gif align=absmiddle> ';
var selected_row=0;
{literal}
var JQ = {};
JQ = jQuery.noConflict(true);
var CUSTOM_ACC_EXPORT = {
	initialize: function(){
		this.f_a = document.f_a;
		var THIS = this;
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
		
		JQ('.tab a').on('click',function(e){
			e.preventDefault();
			JQ('.tab a').removeClass('active');
			JQ(this).addClass('active');
			JQ('#schedule_type').val(JQ(this).attr('data-type'));
			THIS.load_schedule();
		});
		THIS.load_schedule();
	},
	generate:function(){
		var THIS = this;
		if (myTimeout) clearTimeout(myTimeout);
		selected_row=0;
		JQ('#data-result tr').removeClass('selected highlight_row');
		
		if(JQ("select option").length == 1){
			alert("There is no Export Format currently. Please go to Setup Custom Accounting Export to create a format before you generate accounting export.");
			return false;
		}
		
		if (JQ("#branch_id").val() == ''){
			alert("Please Select A Branch");
			return false;
		}
		
		if (JQ("#export_format").val() == ''){
			alert("Please Select An Export Format");
			return false;
		}
		
		JQ.ajaxSetup({ async:false });
		var form = this.f_a;
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
								if (JQ('input[name=generate_now]:checked').val()==1) {
									THIS.start_now(parseInt(ret.id),0,parseInt(ret.branch_id));
								}
							}
							else if(ret.status=='EXIST'){
								if(ret.data.archived==1) JQ('#schedule_type').val('archive');
								else JQ('#schedule_type').val('active');

								selected_row=ret.data.id+'-'+ret.data.branch_id;
								THIS.load_schedule();
								
								JQ('#data-row-'+ret.data.id+'-'+ret.data.branch_id).addClass('selected highlight_row');
								if (ret.data.started==1 && ret.data.completed==1) {
									if(ret.data.archived==1){
										alert("The file is currently existed and being archived, please change it to active before generate it.");
									}else{
										if (confirm("File is complete generated.\nDo you wish to regenerate?")) {
											THIS.start_now(ret.data.id,1, ret.data.branch_id);
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
							THIS.load_schedule();
						}
					});
				}else{
					document.location.reload();
				}
			}
		});
		return false;
	},
	load_schedule: function(){
		var THIS = this;
		if (myTimeout) clearTimeout(myTimeout);
		
		var data="debug="+debug+"&selected_row="+selected_row+"&"+JQ('#filter-form').serialize();

		JQ.ajax({
			type:"post",
			url: php_self+"?a=load_schedule",
			data: data,
			success:function(data){
				JQ('#data-result').html(data);
				myTimeout=setTimeout(CUSTOM_ACC_EXPORT.load_schedule,5000);
			}
		});
	},
	start_now: function(id,reset,branch_id){
		var THIS = this;
		if (myTimeout) clearTimeout(myTimeout);
		if (reset==undefined) reset=0;

		JQ('#data-row-'+id+'-'+branch_id+' .data-status').html("Pending");
		JQ('#data-row-'+id+'-'+branch_id+' .data-file_size').html("0 B");
		JQ('#data-row-'+id+'-'+branch_id+' .data-action').html(LOADING);

		JQ.ajax({
			type:"post",
			url: "custom.acc_export.generate.php",
			data: { id:id,debug:debug,reset:reset,branch_id:branch_id},
			success:function(data){
				if (data) {
					alert(data);
				}
				THIS.load_schedule();
			},
			complete:function(){
				THIS.load_schedule();
			},
			timeout: 5000
		});

		return false;
	},
	remove_schedule: function(id, branch_id){
		var THIS = this;
		if (confirm("Are you sure?")) {
			JQ.ajax({
				type:"post",
				url: php_self+"?a=remove_schedule",
				data: { id:id,branch_id:branch_id
					},
				success:function(data){
					THIS.load_schedule();
				}
			});
		}
	},
	do_download: function(id,branch_id){
		JQ('#_download').attr('src',php_self+"?a=download&id="+id+"&branch_id="+branch_id);
	},
	reset: function(id,branch_id){
		var THIS = this;
		if (confirm("Are you sure?")) {
			THIS.start_now(id, 1,branch_id);
		}
		return false;
	},
	archive: function(id,branch_id){
		var THIS = this;
		if (confirm("Are you sure?")) {
			JQ.ajax({
				type:"post",
				url: php_self+"?a=archive",
				data: { id:id,branch_id:branch_id
					},
				success:function(data){
					THIS.load_schedule();
				}
			});
		}
	},
	reactivate: function(id,branch_id){
		var THIS = this;
		if (confirm("Are you sure?")) {
			JQ.ajax({
				type:"post",
				url: php_self+"?a=reactivate",
				data: { id:id,branch_id:branch_id
					},
				success:function(data){
					THIS.load_schedule();
				}
			});
		}
	}
};
{/literal}
</script>
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
	<div class="alert alert-danger rounded mx-3">
		The following error(s) has occured:
	<ul class=err>
		{foreach from=$err item=e}
		<li>{$e}</li>
		{/foreach}
	</ul>
	</div>
{/if}

<div class="card mx-3">
	<div class="card-body">
		<form id="data-form" method="post" class="form" name="f_a" onsubmit="return false">	
			<input type="hidden" name="debug" value="{if $smarty.request.debug}1{else}0{/if}"/>
			<input type="hidden" name="schedule_id" value="{$form.schedule_id|default:0}"/>
			<input type="hidden" name="user_id" value="{$sessioninfo.id}"/>
			<table cellspacing="5" cellpadding="4" border="0">
				<tr>	
					<td><b class="form-label">From</b></td>
					<td>
						<div class="form-inline">
							<input class="form-control" type="text" name="date_from" value="{$form.date_from}" id="date_from" readonly>
						&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">&nbsp;&nbsp;&nbsp;&nbsp;
						</div>
					</td>
				</tr>
				<tr>
					<td><b class="form-label">To</b></td>
					<td>
						<div class="form-inline">
							<input class="form-control"  type="text" name="date_to" value="{$form.date_to}" id="date_to" readonly>
						&nbsp;<img align="absmiddle" src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">  
						</div>
					</td>
				</tr>
				<tr>
					<td><b class="form-label">Export Format</b></td>
					<td>
						<select class="form-control" name="export_format" id="export_format">
							<option value="">-- Select --</option>
							{foreach from=$format_list item=r}
								<option value="{$r.id}">{$r.title}</option>
							{/foreach}
						</select>
					</td>
				</tr>
				{if $branch && !$is_consignment}
					<tr>
						<td><b class="form-label">Branch</b></td>
						<td>
							<select class="form-control" name="branch_id" id="branch_id">
								<option value="">-- Select --</option>
								{foreach from=$branch item=r}
									<option value="{$r.id}">{$r.code}</option>
								{/foreach}
							</select>
						</td>
					</tr>
				{else}
					<input type="hidden" name="branch_id" value="{$form.branch_id.0}"/>
				{/if}
				<tr>
					<td><b class="form-label">Generate Now</b></td>
					<td>
						<input type="radio" name="generate_now" value="1"> Yes &nbsp;
						<input type="radio" name="generate_now" value="0" checked> No
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<input type="button" class="btn btn-primary" name="btnGenerate" onclick="CUSTOM_ACC_EXPORT.generate();" value="Generate">
					</td>
				</tr>
			</table>
			</form>
	</div>
</div>

<div style="padding:10px 0;">
  <div class="row mx-3 mb-2">
	<div class="tab" style="white-space:nowrap;">
		  <a href="javascript:void(0);" data-type="active" class="active btn btn-outline-primary btn-rounded">Active</a>
		  <a href="javascript:void(0);" data-type="archive" class=" btn btn-outline-primary btn-rounded" >Archive</a>
		</div>
  </div>
  <div id="div_grn" >
    <div id="data-result">
    </div>
  </div>
</div>
<iframe id="_download" style="visibility: hidden;width:1px;height: 1px;" src=""></iframe>
<script type="text/javascript">
	CUSTOM_ACC_EXPORT.initialize();
</script>

{include file="footer.tpl"}