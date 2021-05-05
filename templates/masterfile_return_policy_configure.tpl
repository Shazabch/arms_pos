{*
*}

{include file=header.tpl}
{literal}
<style>
a{
	cursor: pointer;
}

table.sub_tbl th, table.sub_tbl td{
	text-align: left;
	border-right: 0px !important;
	border-bottom: 0px !important;
}

.div_rp ul li {
	cursor:pointer;
	display:block;
	margin:0;padding:4px;
}
.div_rp ul li:hover {
	background:#ff9;
}

.div_rp ul li.current {
	background:#9ff;
}

.div_rp:hover ul {
	visibility:visible;
}
</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';

{literal}
function curtain_clicked(){
	$('div_rpc_dialog').hide();
	document.f_condition.reset();
	curtain(false);
}

var RP_CONFIGURATION_MODULE = {
	form_element: undefined,
	selected_cat_id: 0,
	initialize: function(){
		this.form_element = document.f_a;
		var THIS = this;
		if(!this.form_element){
			alert('Return Policy Configuration module failed to initialize.');
			return false;
		}

		// event for create configure return policy
		$('rp_configure_link').observe('click', function(){
            THIS.toggle_condition_dialog();
		});
		// event for adding new return policy configuration by category
		$('btn_add_cat').observe('click', function(){
            THIS.ajax_add_rp_configuration("cat");
		});
		// event for adding new return policy configuration by sku group
		$('btn_add_sg').observe('click', function(){
            THIS.ajax_add_rp_configuration("sg");
		});
		// event when user click "save"
		$('save_btn').observe('click', function(){
            THIS.save_rp_configuration();
		});

		new Draggable('div_rpc_dialog');
		reset_sku_autocomplete();
		THIS.reset_category_autocomplete();
		THIS.reset_row();

		// event to save commission
		/*$('save_btn').observe('click', function(){
			THIS.save_commission();
		});*/

		// event to close commission without save
		/*$('close_btn').observe('click', function(){
			if(!confirm("Close without save?")) return;
            window.location = phpself;
		});*/

		//SA_COMMISSION_CONDITION_DIALOG.initialize();
	},
	// event for open condition window
	table_appear: function(){
		Effect.toggle('div_rpc_dialog', 'blind', {
			duration: 0.5
		});
		center_div('div_rpc_dialog');
		curtain(true);
	},
	// even when click on "add commission item"
	toggle_condition_dialog: function(){
		RP_CONFIGURATION_MODULE.table_appear();
		curtain(true);
	},
	// even when click on "delete commission"
	toggle_rp_configuration_status: function(id, obj){
		if(obj.src.indexOf("deact") > 0){ // means user deactivated the item
			if(!confirm("Are you sure want to deactivate this configuration?")) return;
			this.form_element['active['+id+']'].value = 0;
			obj.src = "/ui/act.png";
			$('inact_area_'+id).show();
		}else{ // else means user activated the item
			this.form_element['active['+id+']'].value = 1;
			obj.src = "/ui/deact.png";
			$('inact_area_'+id).hide();
		}
	},
	delete_rp_configuration: function(id){
		var THIS = this;
		if(!confirm("Are you sure want to delete?")) return;
		if(id == 0 || id == ""){
			alert("Nothing to delete!");
			return;
		}

		Effect.DropOut("rpc_item_"+id, {
			duration:0.5,
			afterFinish: function() {
				if(id < 10000000) document.f_a.elements['is_deleted['+id+']'].value = 1;
				else $("rpc_item_"+id).remove();
				
				var e = $('rp_configuration_items').getElementsByClassName('rpc_items');
				var total_row=e.length;
				if(total_row == 0) $('no_data').show();
				else THIS.reset_row();
			}
		});
	},
	save_rp_configuration: function(){
		this.form_element.submit();
	},
	// add sku from autocomplete
	add_autocomplete: function(){
		if(!document.f_condition.sku_item_id.value) return;
		this.ajax_add_rp_configuration("si");
	},
	// reset category autocomplete
	reset_category_autocomplete: function(){
	    this.selected_cat_id = 0;
	    $('inp_search_cat_autocomplete').value = '';
	    
		var THIS = this;
	    if(!this.cat_autocomplete){
            var params = $H({
				a: 'ajax_search_category',
				max_level: 10,
				no_findcat_expand: 1
			}).toQueryString();

	        this.cat_autocomplete = new Ajax.Autocompleter("inp_search_cat_autocomplete", "div_search_cat_autocomplete_choices", 'ajax_autocomplete.php', {
		        parameters: params,
				paramName: "category",
				indicator: 'span_sac_autocomplete_loading',
				afterUpdateElement: function (obj, li) {
				    s = li.title.split(",");

		            if (s[0]==''){
				        obj.value='';
				        return;
				    }

					THIS.selected_cat_id = s[0];
				}
			});
		}
	},
	ajax_add_rp_configuration: function(type){
		var THIS = this;
		var prm = document.f_condition.serialize();

		if((type == "cat" && !this.selected_cat_id) || (type == "sg" && !document.f_condition.sg_id.value)) return;

		var params = {
			'a': 'ajax_add_rp_configuration',
			category_id: this.selected_cat_id,
			condition_type: type
		};

		prm += '&'+$H(params).toQueryString();
		new Ajax.Request(phpself, {
			parameters: prm,
			method: 'post',
			onSuccess: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

				ret = JSON.parse(str); // try decode json object
				if(ret['ok']==1 && ret['html']){ // success
					if($('no_data') != undefined) $('no_data').hide();

					// append html
					new Insertion.Bottom('rp_configuration_items', ret['html']);
					Effect.Appear('rp_configuration_row_'+ret['id']);

					return;
				}else{  // save failed
					if(ret['err_msg'])	err_msg = ret['err_msg'];
					else err_msg = str;
				}

			    // prompt the error
			    alert(err_msg);	
			},
			onComplete: function(msg){
				// close dialog
				curtain_clicked();
				THIS.reset_row();
			}
		});
		this.selected_cat_id = 0;
	},
	reset_row: function(){
		var e = $('rp_configuration_items').getElementsByClassName('row_no');
		var total_row=e.length;
		var deduct_row = 0;
		
		for(var i=0;i<total_row;i++){
			var s = e[i].id.split("_");
			
			if(this.form_element['is_deleted['+s[2]+']'].value){
				deduct_row += 1;
				continue;
			}
			var no_row = (i+1-deduct_row);
			td_1=(no_row)+'.';
			e[i].innerHTML=td_1;
			e[i].title='No. '+(no_row);
		}
	},
	load_rp_list : function(id, member_type){
	
		if($('div_rp_'+id+'_'+member_type).style.display == "") $('div_rp_'+id+'_'+member_type).hide();
		else $('div_rp_'+id+'_'+member_type).show();

		$$('#div_rp_'+id+'_'+member_type+' li').each(function (obj,idx){
			if (obj.innerHTML == document.f_a.elements['setup['+id+']['+member_type+']'].value){
				obj.className = 'current';
				obj.scrollToPosition;
			}
			else{
				obj.className = '';
			}
		});

		if($('div_rp_'+id+'_'+member_type).innerHTML.trim() != "") return; // stop to go further if found contents is already loaded

		$('span_loading_'+id+'_'+member_type).update(_loading_);

		var params = {
			a: 'ajax_load_rp_list',
			id: id,
			mt: member_type,
			type: this.form_element['type['+id+']'].value,
			is_parent: this.form_element['is_parent['+id+']'].value,
		};
	
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};

				ret = JSON.parse(str); // try decode json object
				if(ret['ok'] == 1){ // success
					$('div_rp_'+id+'_'+member_type).update(ret['html']);
				}else{
					alert(ret['error']);
				}
				$('span_loading_'+id+'_'+member_type).update();
				
				$$('#div_rp_'+id+'_'+member_type+' li').each(function (obj,idx){
					if (obj.innerHTML == document.f_a.elements['setup['+id+']['+member_type+']'].value){
						obj.className = 'current';
						obj.scrollToPosition;
					}
					else{
						obj.className = '';
					}
				});
			},
			onFailure: function(msg){
				alert(msg.responseText.trim());
			}
		});
	},
	set_return_policy: function(id, member_type, obj){
		var name =obj.title;	
		$('setup_'+id+'_'+member_type).value=name;
		var rp_id = obj.getAttribute("rp_id");
		var rp_branch_id = obj.getAttribute("rp_branch_id");
		this.form_element['rp_id['+id+']['+member_type+']'].value = rp_id;
		this.form_element['rp_branch_id['+id+']['+member_type+']'].value = rp_branch_id;
		Element.hide('div_rp_'+id+'_'+member_type);
	}
}

// SKU AUTOCOMPLETE DIALOG add
function add_autocomplete(){
	RP_CONFIGURATION_MODULE.add_autocomplete();
}
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

<!-- commission condition dialog -->
<form name="f_condition" method="post">
<div id="div_rpc_dialog" class="curtain_popup" style="position:absolute;z-index:10000;width:650px;height:250px;display:none;border:2px solid #1569C7;background-color:#1569C7;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_rpc_dialog_header" style="border:2px ridge #1569C7;color:white;background-color:#1569C7;padding:2px;cursor:default;"><span style="float:left;" id="span_sa_cc_dialog_header">Choose Condition</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_rpc_dialog_content" style="padding:2px;">
		{include file='masterfile_return_policy_configure.condition_dialog.tpl'}
	</div>
</div>
</form>

<iframe width="1" height="1" style="visibility:hidden" name="ifprint"></iframe>

<form name="f_a" method="post">
<input type="hidden" name="a" value="save_rp_configuration">

<div id="show_last">
{if $status eq 'save'}
<img src="/ui/approved.png" align="absmiddle"> Return Policy Configure saved as ID#{$id|string_format:"%05d"}<br>
{elseif $status eq 'delete'}
<img src="/ui/cancel.png" align="absmiddle"> Return Policy Configure ID#{$smarty.request.id|string_format:"%05d"} was deleted<br>
{elseif $status eq 'reset'}
<img src="/ui/notify_sku_reject.png" align="absmiddle"> Return Policy Configure ID#{$smarty.request.save_id|string_format:"%05d"} was reset.
{/if}
</div>

<a id="rp_configure_link" ><img src="ui/new.png" align="absmiddle" title="Configure new Return Policy Item"> Configure New Return Policy Item</a>

<br /><br />

{if $err}
<div id=err><div class=errmsg><ul>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

<div id="rp_configure_list">
	{include file="masterfile_return_policy_configure.table.tpl"}
</div>
</form>
{include file="footer.tpl"}
<script>
RP_CONFIGURATION_MODULE.initialize();
</script>