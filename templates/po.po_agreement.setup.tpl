{*
9/3/2012 3:23 PM Andy
- Fix pagination problem.

9/26/2018 4:35 PM Andy
- Enhanced to have "Upload CSV" for Purchase Agreement.

10/2/2018 2:33 PM Andy
- Fixed using "Upload CSV" after document saved will have error.

06/24/2020 03:13 PM Sheila
- Updated button css
*}

{include file="header.tpl"}

<script type="text/javascript">

var phpself = '{$smarty.server.PHP_SELF}';

{literal}

var PURCHASE_AGREEMENT_LIST = {
	page_num: 0,
	tab_num: 1,
	initialize: function(){
		this.list_sel(1);
	},
	list_sel: function(t, p){
		if(t == -1){
			t = this.tab_num;
		}else{
			this.tab_num = t;
		}
		t = int(t);
		
		if(p != undefined){
			this.page_num = int(p);
		}
		var page_num = this.page_num;
		var tab_num = this.tab_num;
		
		this.change_tab_active(t);
			
		var params = {
			t: tab_num,
			p: page_num
		};
		if(!t){
			params['search_str'] = $('inp_find').value.trim();
			if(!params['search_str']){
				alert('Please key in something to search.');
				return false;
			}
		}	
		
		$('pa_list').update('<img src=ui/clock.gif align=absmiddle> Loading…');
				
		new Ajax.Request(phpself+'?a=load_list', {
			method: 'post',
			parameters: params,
			onComplete: function(msg){		    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						$('pa_list').update(ret['html']);
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    if(!err_msg)	err_msg = 'Error: No respond from server.';
			    alert(err_msg);
			    $('pa_list').update(err_msg);
			}
		});
	},
	change_tab_active: function(t){
		var a_tab_list = $$('#div_tab_list a.a_tab');
		
		t = int(t);
		for(i=0; i<a_tab_list.length; i++){
			$(a_tab_list[i]).removeClassName('active');		
		}
		
		var tab = $('lst-'+t);
		if(tab)	tab.addClassName('active');
	},
	page_change: function(p){
		this.page_num = p;
		this.list_sel(-1);
	},
	// Toggle Upload CSV div
	toggle_upload_csv: function(){
		var div = $('div_upload_csv');
		
		if(div.style.display == 'none'){
			div.show();
		}else{
			div.hide();
		}
	},
	upload_csv: function(){
		var filename = document.f_upload_csv['csv_file'].value;
		if(filename.indexOf('.csv')<0){
			alert('Please select a valid csv file');
			return false;
		}
		document.f_upload_csv.submit();
	}
}

function list_sel(t){
	PURCHASE_AGREEMENT_LIST.list_sel(t, 0);
}

function search_input_keypress(event){
	if (event == undefined) event = window.event;
	if(event.keyCode==13){  // enter
		list_sel();
	}
}

function page_change(ele){
	PURCHASE_AGREEMENT_LIST.page_change(ele.value);
}


{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

<div id=show_last>
	{if $smarty.request.type eq 'save'}
		<img src="/ui/approved.png" align="absmiddle"> Purchase Agreement saved as ID#{$smarty.request.id}<br>
	{elseif $smarty.request.type eq 'cancel'}
		<img src="/ui/cancel.png" align="absmiddle"> Purchase Agreement ID#{$smarty.request.id} was cancelled<br>
	{elseif $smarty.request.type eq 'reset'}
		<img src="/ui/cancel.png" align="absmiddle"> Purchase Agreement ID#{$smarty.request.id} was reset<br>
	{elseif $smarty.request.type eq 'delete'}
		<img src="/ui/cancel.png" align="absmiddle"> Purchase Agreement ID#{$smarty.request.id} was deleted<br>
	{elseif $smarty.request.type eq 'confirm'}
		<img src="/ui/approved.png" align="absmiddle"> Purchase Agreement ID#{$smarty.request.id} confirmed.
	{elseif $smarty.request.type eq 'approved'}
		<img src="/ui/approved.png" align="absmiddle"> Purchase Agreement ID#{$smarty.request.id} was Fully Approved.
	{/if}
</div>

<ul>
	<li> <img src="ui/new.png" align="absmiddle" /> <a href="?a=open">Create New Purchase Agreement</a></li>
	<li> <img src="ui/new.png" align="absmiddle" /> <a href="javascript:void(PURCHASE_AGREEMENT_LIST.toggle_upload_csv());">Create New Purchase Agreement by Upload CSV</a>
		<div id="div_upload_csv" style="display:none;" class="stdframe">
			<form name="f_upload_csv" method="post" ENCTYPE="multipart/form-data" action="{$smarty.server.PHP_SELF}">
				<input type="hidden" name="a" value="create_by_upload_csv" />
				
				<b>CSV File:</b> <input type="file" name="csv_file" />
				<input type="button" value="Upload" style="background-color:#f90; color:#fff;" onclick="PURCHASE_AGREEMENT_LIST.upload_csv()">
				<br />
				[<a href="?a=download_csv_sample">Download Sample</a>]<br />
				<div style="background-color: #fff;float:left;" class="stdframe">
					<ul>
						<li><b>Type</b>: 
							<ul>
								<li> P = Purchase Item</li>
								<li> F = FOC Item</li>
							</ul>
						</li>
						<li><b>Item Code</b>: System will match ARMS CODE / MCode / Art No / {$config.link_code_name}</li>
						<li><b>Rule No</b>: Use to match with FOC Item. (Optional)</li>
						<li><b>Qty Type</b>: 
							<ul>
								<li> Fixed = Fixed Purchase Quantity.</li>
								<li> Multiply = Purchase Quantity is using fixed multiply.</li>
								<li> Range = Purchase Quantity have minimum and maximum quantity</li>
							</ul>
						</li>
						<li><b>Qty1</b>: Purchase Quantity or Minimum Purchase Quantity for Qty Type (Range)</li>
						<li><b>Qty2</b>: Maximum Purchase Quantity for Qty Type (Range)</li>
						<li><b>Discount and Suggested Selling Price</b>: Optional, can leave it empty.</li>
					</ul>
				</div>
				<br style="clear:both;" />
			</form>
			
		</div>
	</li>
</ul>


<div class="tab" id="div_tab_list" style="height:25px;white-space:nowrap;">
	&nbsp;&nbsp;&nbsp;
	<a href="javascript:list_sel(1)" class="a_tab" id="lst-1" class=active>Saved</a>
	<a href="javascript:list_sel(2)" class="a_tab" id="lst-2">Waiting for Approval</a>
	<a href="javascript:list_sel(3)" class="a_tab" id="lst-3">Rejected</a>
	<a href="javascript:list_sel(4)" class="a_tab" id="lst-4">Cancelled/Terminated</a>
	<a href="javascript:list_sel(5)" class="a_tab" id="lst-5">Approved</a>
	<a id="lst-0" class="a_tab">Find <input id="inp_find" onKeyPress="search_input_keypress(event);" /> <input class="btn-default" type="button" value="Go" onClick="list_sel();"  /></a>
</div>

<div id="pa_list" style="border:1px solid #000">
</div>

<script type="text/javascript">
	PURCHASE_AGREEMENT_LIST.initialize();
</script>

{include file="footer.tpl"}
