{*
	2009/10/19 10:31:57 AM Andy
	- check config.do_allow_credit_sales to allow user view credit sales DO
	*}
	
	{include file=header.tpl}
	
	
	{literal}
	<style>
	.popup_div_header{
		color: white;
		padding:2px;
		cursor:default;
	}
	.popup_div{
		  background-color:#FFFFFF;
		background-image:url(/ui/ndiv.jpg);
		background-repeat:repeat-x;
	}
	
	#div_from_b_details_content{
	
	}
	#div_to_b_details_content{
		padding-top:10px;
		padding-left:5px;
		padding-right:5px;
	}
	ul.no_point,ul.no_point ul{
		list-style:none;
		padding: 0 5px;
	}
	</style>
	{/literal}
	
	<!-- calendar stylesheet -->
	<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
	
	<!-- main calendar program -->
	<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
	
	<!-- language for the calendar -->
	<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
	
	<!-- the following script defines the Calendar.setup helper function, which makes
	   adding a calendar a matter of 1 or 2 lines of code. -->
	<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
	
	
	<script type="text/javascript">
	var from_date = '{$smarty.request.from}';
	var to_date = '{$smarty.request.to}';
	var phpself = '{$smarty.server.PHP_SELF}';
	
	{literal}
	function sel_from_b(){
		//curtain(true);
		//center_div('div_from_b_details');
		jQuery('#div_from_b_details').modal('show');

	}
	function sel_to_b(){
		curtain(true);
		center_div('div_to_b_details');
		$('div_to_b_details').show();
	}
	
	function curtain_clicked(){
		$$('.popup_div').each(function(ele){
			$(ele).hide();
		});
	}
	
	function toggle_child_box(ele,status){
		 parent_ul = $(ele).parentNode.parentNode;
		var inp = $(parent_ul).getElementsBySelector('input');
		
		for(var i=0; i<inp.length; i++){
			inp[i].checked = status;
		}
	}
	
	function toggle_from_list(ele){
		var c = $(ele).id.split(',')[2];
		var status = $(ele).title;
		var parent_ul = $(ele).parentNode.parentNode;
		var li = $(parent_ul).getElementsBySelector('.li_from_b_child_'+c);
		
		if(status=='close'){
			$(ele).title = 'expand';
			$(ele).src = '/ui/expand.gif';
		} 
		else{
			$(ele).title = 'close';
			$(ele).src = '/ui/collapse.gif';
		}    
		for(var i=0; i<li.length; i++){
			if(status=='close')  li[i].hide();
			else	li[i].show();
		}
	}
	
	function toggle_to_list(ele){
		var c = $(ele).id.split(',')[2];
		var status = $(ele).title;
		var parent_ul = $(ele).parentNode.parentNode;
		var li = $(parent_ul).getElementsBySelector('.li_to_b_child_'+c);
	
		if(status=='close'){
			$(ele).title = 'expand';
			$(ele).src = '/ui/expand.gif';
		}
		else{
			$(ele).title = 'close';
			$(ele).src = '/ui/collapse.gif';
		}
		for(var i=0; i<li.length; i++){
			if(status=='close')  li[i].hide();
			else	li[i].show();
		}
	}
	
	function show_details(do_type,branch_id,to_id){
		curtain(true);
		center_div('div_transfer_details');
		$('div_transfer_details').show();
		$('div_transfer_details_content').update(_loading_);
		
		new Ajax.Updater('div_transfer_details_content',phpself,{
			parameters: {
				a: 'ajax_get_transfer_details',
				do_type: do_type,
				branch_id: branch_id,
				to_id: to_id,
				from_date: from_date,
				to_date: to_date
			}
		});
	}
	{/literal}
	</script>
	
	<!-- popup div-->
	<div id="div_transfer_details" class="popup_div" style="position:absolute;z-index:10000;width:500px;height:450px;display:none;border:2px solid #CE0000;">
		<div id="div_transfer_details_header" class="popup_div_header"><span style="float:left;">Details</span>
			<span style="float:right;">
				<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
			</span>
			<div style="clear:both;"></div>
		</div>
		<div id="div_transfer_details_content" style="padding:2px;"></div>
	</div>
	<!-- end of popup div-->
	
	
	<div class="breadcrumb-header justify-content-between">
		<div class="my-auto">
			<div class="d-flex">
				<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
			</div>
		</div>
	</div>
	
	
	{if $err}
	<ul>
		{foreach from=$err item=e}
			<div class="alert alert-danger"><li>{$e}</li></div>
		{/foreach}
		</ul>
	{/if}
	<form name="f_a" class="noprint" method="post" style="border:1px solid #eee;padding:5px;white-space:nowrap;">
	
	<!-- popup div -->
	
	
	<div class="modal popup_div" id="div_from_b_details" >
		<div class="modal-dialog modal modal-dialog-centered"  role="document">
			<div class="modal-content modal-content-demo">
				<div class="modal-header bg-danger">
					<h6 id="div_from_b_details_header" class="modal-title popup_div_header  text-center">Please Choose Delivery From</h6><button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true">&times;</span></button>
				</div>
				<div class="modal-body" id="div_from_b_details_content">
					<div >
						<div>
							<ul class="no_point">
								<li>
								<img src="/ui/collapse.gif" align="absmiddle" class="clickable" onClick="toggle_from_list(this);" id="img,from_b,all" title="close" />
								<b>All</b>
								[ <img src="/ui/checkall.gif" align="absmiddle" class="clickable" onClick="toggle_child_box(this,true);" />
								<img src="/ui/uncheckall.gif" align="absmiddle" class="clickable" onClick="toggle_child_box(this,false);" /> ]
				
								{foreach from=$branches_group.header item=bh}
									{assign var=bg_id value=$bh.id}
									<ul><li class="li_from_b_child_all">
									<img src="/ui/expand.gif" align="absmiddle" onClick="toggle_from_list(this);" id="img,from_b,{$bh.id}"  />
									<b>{$bh.code}</b>
									[ <img src="/ui/checkall.gif" align="absmiddle" class="clickable" onClick="toggle_child_box(this,true);" />
									<img src="/ui/uncheckall.gif" align="absmiddle" class="clickable" onClick="toggle_child_box(this,false);" /> ]
									  <ul>
										{foreach from=$branches_group.items.$bg_id item=b}
											<li class="li_from_b_child_{$bh.id}" style="display:none;"><input type="checkbox" value="{$b.branch_id}" name="branch_id[]" {if is_array($smarty.request.branch_id)}{if in_array($b.branch_id,$smarty.request.branch_id)}checked {/if}{elseif !isset($smarty.request.branch_id)}checked {/if} >{$b.code} - {$b.description}</li>
										{/foreach}
									  </ul>
									</li></ul>
								{/foreach}
				
								</li>
								{foreach from=$all_branch item=b}
									{assign var=bid value=$b.id}
									{if !$branches_group.have_group.$bid}
										<li class="li_from_b_child_all"><input type="checkbox" value="{$bid}" name="branch_id[]" {if is_array($smarty.request.branch_id)}{if in_array($bid,$smarty.request.branch_id)}checked {/if}{elseif !isset($smarty.request.branch_id)}checked {/if} >{$b.code} - {$b.description}</li>
									{/if}
								{/foreach}
							</ul>
						</div>
						<p align="center"><input type="button" value="OK" onClick="default_curtain_clicked();"></p>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn ripple btn-primary" type="button">Save changes</button>
					<button class="btn ripple btn-secondary" data-dismiss="modal" type="button">Close</button>
				</div>
			</div>
		</div>
	</div>

	<div id="div_to_b_details" class="popup_div" style="position:absolute;z-index:10000;width:500px;height:450px;display:none;border:2px solid #CE0000;">
		<div id="div_to_b_details_header" class="popup_div_header"><span style="float:left;">Please Choose Delivery To</span>
			<span style="float:right;">
				<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
			</span>
			<div style="clear:both;"></div>
		</div>
		<div id="div_to_b_details_content">
			<div style="background:white;width:100p%;height:300px;border:1px inset black;overflow:auto;">
				<ul class="no_point">
					<li>
					<img src="/ui/collapse.gif" align="absmiddle" class="clickable" onClick="toggle_to_list(this);" id="img,to_b,all" title="close" />
					<b>All</b>
					[ <img src="/ui/checkall.gif" align="absmiddle" class="clickable" onClick="toggle_child_box(this,true);" />
					<img src="/ui/uncheckall.gif" align="absmiddle" class="clickable" onClick="toggle_child_box(this,false);" /> ]
	
					{foreach from=$branches_group.header item=bh}
						{assign var=bg_id value=$bh.id}
						<ul><li class="li_to_b_child_all">
						<img src="/ui/expand.gif" align="absmiddle" onClick="toggle_to_list(this);" id="img,to_b,{$bh.id}"  />
						<b>{$bh.code}</b>
						[ <img src="/ui/checkall.gif" align="absmiddle" class="clickable" onClick="toggle_child_box(this,true);" />
						<img src="/ui/uncheckall.gif" align="absmiddle" class="clickable" onClick="toggle_child_box(this,false);" /> ]
						  <ul>
							{foreach from=$branches_group.items.$bg_id item=b}
								<li class="li_to_b_child_{$bh.id}" style="display:none;"><input type="checkbox" value="{$b.branch_id}" name="to_branch_id[]" {if is_array($smarty.request.to_branch_id)}{if in_array($b.branch_id,$smarty.request.to_branch_id)}checked {/if}{elseif !isset($smarty.request.branch_id)}checked {/if} >{$b.code} - {$b.description}</li>
							{/foreach}
						  </ul>
						</li></ul>
					{/foreach}
	
					</li>
					{foreach from=$all_branch item=b}
						{assign var=bid value=$b.id}
						{if !$branches_group.have_group.$bid}
							<li class="li_to_b_child_all"><input type="checkbox" value="{$bid}" name="to_branch_id[]" {if is_array($smarty.request.to_branch_id)}{if in_array($bid,$smarty.request.to_branch_id)}checked {/if}{elseif !isset($smarty.request.branch_id)}checked {/if} >{$b.code} - {$b.description}</li>
						{/if}
					{/foreach}
				</ul>
			</div>
			*Note: Delivery To Branch Will only effect on Transfer Type DO
			<p align="center"><input type="button" value="OK" onClick="default_curtain_clicked();"></p>
		</div>
	</div>
	<!-- end of popup div-->
	<div class="card mx-3">
		<div class="card-body">
			
	<p>
		 <div class="row">
			<div class="col-md-3">
				<b class="form-label">Date From</b>
				<div class="form-inline">
				   <input class="form-control" type="text" name="from" value="{$smarty.request.from}" id="added1" readonly="1" />
					&nbsp;&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date"/> 
				</div>
			  </div>
			  
			  <div class="col-md-3">
				<b class="form-inline">To</b> 
				<div class="form-inline">
				  <input class="form-control" type="text" name="to" value="{$smarty.request.to}" id="added2" readonly="1"  /> 
				  &nbsp;&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date"/>
				</div>
			  </div>
			
		<div class="col-md-3">
			<b class="form-label">DO Type</b>
			<select class="form-control" name="do_type">
				<option value="">-- All --</option>
				<option value="transfer" {if $smarty.request.do_type eq 'transfer'}selected {/if}>Transfer</option>
				{if $config.do_allow_credit_sales}
					<option value="credit_sales" {if $smarty.request.do_type eq 'credit_sales'}selected {/if}>Credit Sales</option>
				{/if}
				<option value="open" {if $smarty.request.do_type eq 'open'}selected {/if}>Cash Sales</option>
			</select>
		</div>
			
			<div class="col-md-3">
				<input type=submit class="btn btn-primary mt-4" name=subm value="Refresh">
			</div>
			</p>
		 </div>
		<div class="row mt-3 ml-2">
			<p>
				{if BRANCH_CODE eq 'HQ'}
				<a href="javascript:void(sel_from_b());">
				<img src="/ui/report_edit.png" align="absmiddle" border="0" /> Choose Delivery From Branch
				</a>
				<br /><br>
				{/if}
				<a href="javascript:void(sel_to_b());">
				<img src="/ui/report_edit.png" align="absmiddle" border="0" /> Choose Delivery To Branch
				</a>
				</p>
		</div>
	
		</div>
	</div>	
	</form>
	<br>
	{if !$table}
	{if $smarty.request.subm}- No record -{/if}
	{else}
	{if !$smarty.request.do_type or $smarty.request.do_type eq 'transfer'}
		{assign var=show_transfer value=1}
	{/if}
	{if !$smarty.request.do_type or $smarty.request.do_type eq 'credit_sales'}
		{assign var=show_credit_sales value=1}
	{/if}
	{if !$smarty.request.do_type or $smarty.request.do_type eq 'open'}
		{assign var=show_open value=1}
	{/if}
	<div class="card mx-3">
		<div class="card-body">
			<div class="table-responsive">
				<table width="100%" class="report_table table mb-0 text-md-nowrap  table-hover"  id="tbl_do">
					<thead class="bg-gray-100">
						<tr >
							<th rowspan="2" align="left">
								<div class="r">To</div>
								<div>From</div>
							</th>
							{if $show_transfer}
								<th colspan="{count var=$to_branches_list offset=1}">Transfer</th>
							{/if}
							{if $show_credit_sales && $config.do_allow_credit_sales}
									<th colspan="{count var=$debtor offset=1}">Credit Sales</th>
							{/if}
							{if $show_open}
								<th rowspan="2">Cash Sales</th>
							{/if}
						</tr>
					</thead>
					<tr bgcolor="#ffee99">
						{if $show_transfer}
							{foreach from=$to_branches_list item=b}
								<th>{$b.code}</th>
							{/foreach}
							<th>Total</th>
						{/if}
						{if $show_credit_sales && $config.do_allow_credit_sales}
							{foreach from=$debtor item=d}
								<th>{$d.code}</th>
							{/foreach}
							<th>Total</th>
						{/if}
					</tr>
					<tbody class="fs-08">
						{foreach from=$branches_list item=br}
							{assign var=bid value=$br.id}
							<tr bgcolor='{cycle values=",#eeeeee"}' >
								<td nowrap>{$br.code} - {$br.description}</td>
								{if $show_transfer}
									{foreach from=$to_branches_list item=b}
										{assign var=do_bid value=$b.id}
										<td align="center" {if $do_bid eq $bid}bgcolor="#c0c0c0"{/if}>
										<a href="javascript:void(show_details('transfer','{$bid}','{$do_bid}'));">
										{$table.transfer.$bid.$do_bid|number_format|ifzero:''}
										</a>
										</td>
									{/foreach}
									<td align="center">{$total.transfer.$bid.total|number_format|ifzero:''}</td>
								{/if}
								{if $show_credit_sales && $config.do_allow_credit_sales}
									{foreach from=$debtor item=d}
										{assign var=debtor_id value=$d.id}
										<td align="center">
										<a href="javascript:void(show_details('credit_sales','{$bid}','{$debtor_id}'));">
										{$table.credit_sales.$bid.$debtor_id|number_format|ifzero:''}
										</a></td>
									{/foreach}
									<td align="center">{$total.credit_sales.$bid.total|number_format|ifzero:''}</td>
								{/if}
								{if $show_open}
									<td align="center">
									<a href="javascript:void(show_details('open','{$bid}',''));">
									{$table.open.$bid.open|number_format|ifzero:''}</a></td>
								{/if}
							</tr>
						{/foreach}
					</tbody>
					<tr bgcolor="#ffee99">
						<th class="r">Total</th>
						{if $show_transfer}
							{foreach from=$to_branches_list item=b}
								{assign var=do_bid value=$b.id}
								<td align="center">{$total.transfer.total.$do_bid|number_format|ifzero:''}</td>
							{/foreach}
							<td align="center">{$total.transfer.total.total|number_format|ifzero:''}</td>
						{/if}
						{if $show_credit_sales && $config.do_allow_credit_sales}
							{foreach from=$debtor item=d}
								{assign var=debtor_id value=$d.id}
								<td align="center">{$total.credit_sales.total.$debtor_id|number_format|ifzero:''}</td>
							{/foreach}
							<td align="center">{$total.credit_sales.total.total|number_format|ifzero:''}</td>
						{/if}
						{if $show_open}
							<td align="center">{$total.open.total.total|number_format|ifzero:''}</td>
						{/if}
					</tr>
				</table>
			</div>
		</div>
	</div>
	{/if}
	
	<script>
	{literal}
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
		
		new Draggable('div_from_b_details',{ handle: 'div_from_b_details_header'});
		new Draggable('div_to_b_details',{ handle: 'div_to_b_details_header'});
		new Draggable('div_transfer_details',{ handle: 'div_transfer_details_header'});
	{/literal}
	</script>
	{include file=footer.tpl}
	