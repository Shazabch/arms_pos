{*
9/10/2014 1:27 PM Justin
- Bug fixed on indicator did not hidden while add new GST.

10/9/2014 4:41 PM Justin
- Enhanced to remove the vendor settings column.

1/2/2015 5:22 PM Justin
- Enhanced to take off "Purchase Price Include GST".

1/24/2015 12:24 PM Justin
- Enhanced to add new option "Special Code for Vendor".

3/19/2015 10:46 AM Andy
- Add legend to let user know what privilege they need in order to edit.
- Enhanced to check privilege to allow user to add new gst.

07/18/2016 16:30 Edwin
- Bug fixed on some gst unable to open edit window.

7/26/2017 15:20 Qiu Ying
- Enhanced to add second tax code
*}
{include file=header.tpl}
{literal}
<style>
a{
	cursor:pointer;
}
</style>
{/literal}
<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
function curtain_clicked(){
	hidediv('div_gst_table');
	curtain(false);
}

var MASTERFILE_GST_MODULE = {
	curr_id: undefined,
	form_element: undefined,
	gst_id: undefined,
	prv_bid: undefined,
	initialize : function(){
		// event when user click "add"
		$('add_btn').observe('click', function(){
            MASTERFILE_GST_MODULE.validate('add');
		});

		// even when user click "cancel" and "close"
		$('cancel_btn').observe('click', function(){
            MASTERFILE_GST_MODULE.toggle_gst_table();
		});
		$('close_btn').observe('click', function(){
            MASTERFILE_GST_MODULE.toggle_gst_table();
		});

		// even when user click "edit"
		$('edit_btn').observe('click', function(){
			MASTERFILE_GST_MODULE.edit(0, 1);
		});

		// event when user click "update"
		$('update_btn').observe('click', function(){
            MASTERFILE_GST_MODULE.validate('update');
		});
		
		new Draggable('div_gst_table');
	},
	toggle_gst_table : function(type){
		this.form_element = document.f_a;
		if(type != undefined){
			if(type == "add"){
				$('bmsg').update("Complete below form and click Add");
				$('abtn').show();
				$('ebtn').hide();
				this.form_element.reset();
			}else{
				$('bmsg').update("Edit and click Update");
				$('abtn').hide();
				$('ebtn').show();
			}
			$('err_msg').update();
			hidediv('err_msg');

			center_div('div_gst_table');
			$('div_gst_table').show();
			curtain(true);
		}else{
			$('div_gst_table').hide();
			curtain(false);
		}
		this.toggle_type();
	},
	validate : function(prs_type){
		if (empty(document.f_a.code, 'You must enter Code')) return false;
		if (empty(document.f_a.description, 'You must enter Description')) return false;

		if(document.f_a.type.value == "supply" && empty(document.f_a.indicator_receipt, 'You must enter Indicator')) return false;
		
		if(prs_type == "add") MASTERFILE_GST_MODULE.ajax_add();
		else MASTERFILE_GST_MODULE.ajax_update();
	},
	ajax_add : function(){
		this.form_element = document.f_a;
		var prm = $(this.form_element).serialize();
		
		var params = {
		    a: 'ajax_add'
		};
		prm += '&'+$H(params).toQueryString();

		new Ajax.Request(phpself, {
			parameters: prm,
			method: 'post',
			onComplete: function(msg){
				if(!msg.responseText.trim()){
					alert("GST ["+document.f_a.code.value.trim()+"] has been added.");
					document.location=phpself;
				}else{
					$('err_msg').update(msg.responseText.trim());
					Effect.Appear('err_msg', {
						duration: 0.5
					});
				}
			},
			onFailure: function(msg){
				alert(msg.responseText.trim());
			}
		});
	},
	ajax_update : function(){
		this.form_element = document.f_a;
		var prm = $(this.form_element).serialize();

		var params = {
		    a: 'ajax_update'
		};
		prm += '&'+$H(params).toQueryString();

		new Ajax.Request(phpself, {
			parameters: prm,
			method: 'post',
			onComplete: function(msg){
				if(!msg.responseText.trim()){
					alert("GST ["+document.f_a.code.value.trim()+"] has been updated.");
					document.location=phpself;
				}else{
					$('err_msg').update(msg.responseText.trim());
					Effect.Appear('err_msg', {
						duration: 0.5
					});
				}
			},
			onFailure: function(msg){
				alert(msg.responseText.trim());
			}
		});
	},

	edit : function(id, is_restore){
		if(is_restore && !confirm("Are you sure want to restore?")) return;
		else if(is_restore && id == 0) id = this.curr_id;
		document.f_a.reset();
		document.f_a.id.value = id;
		this.curr_id = id;
		
		var THIS = this;
		new Ajax.Request(phpself, {
			parameters:{
				a: 'edit',
				gst_id: id
			},
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
				var err_msg = '';

				ret = JSON.parse(str); // try decode json object
				if(ret['ok'] && ret['gst_info']){ // success
					if(document.f_a){
						document.f_a.id.value = ret['gst_info']['id'];
						document.f_a.code.value = ret['gst_info']['code'];
						
						if (ret['gst_info']['second_tax_code'] != undefined) document.f_a.second_tax_code.value = ret['gst_info']['second_tax_code'];
						document.f_a.description.value = ret['gst_info']['description'];
						document.f_a.type.value = ret['gst_info']['type'];
						document.f_a.rate.value = ret['gst_info']['rate'];
						if(ret['gst_info']['is_vd_special_code'] > 0) document.f_a.is_vd_special_code.checked = true;
						else document.f_a.is_vd_special_code.checked = false;
						THIS.toggle_type(ret);
						if(is_restore == 0) MASTERFILE_GST_MODULE.toggle_gst_table('edit');
						return;
					}else err_msg = "Failed to load edit form!";
				}else{  // load GST info failed
					if(ret['failed_msg'])	err_msg = ret['failed_msg'];
					else err_msg = str;
				}

				alert(err_msg);
			}
		});

		document.f_a.a.value = 'ajax_update';
		document.f_a.code.focus();
	},
	toggle_activation : function(id, status){
		if(status == 0 && !confirm("Are you sure want to deactivate this GST?")) return;

		var params = {
		    a: 'activation',
			gst_id: id,
			value: status
		};
		//prm += '&'+$H(params).toQueryString();

		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
				var err_msg = '';

				ret = JSON.parse(str); // try decode json object
				if(ret['ok']){ // success
					if(document.f_a){
						if(status == 0) alert("GST deactivated.");
						else alert("GST activated.");
						document.location=phpself;
						return;
					}else err_msg = "Failed to load edit form!";
				}else{  // load GST info failed
					if(ret['failed_msg'])	err_msg = ret['failed_msg'];
					else err_msg = str;
				}

				alert(err_msg);
			},
			onFailure: function(msg){
				alert(msg.responseText.trim());
			}
		});
	},
	
	perc_check : function(obj){
		mf(obj);
		
		if(obj.value < 0 || obj.value > 100){
			obj.value = 0;
		}
	},
	
	toggle_type : function(prm){
		if(document.f_a.type.value == "purchase"){
			//if(prm != undefined){
				//if(prm['gst_info']['inc_item_cost'] == 1) document.f_a.inc_item_cost.checked = true;
				//document.f_a.vendor_gst_setting.value = prm['gst_info']['vendor_gst_setting'];
			//}
			$('tb_purchase').show();
			$('tb_supply').hide();
		}else{
			if(prm != undefined) document.f_a.indicator_receipt.value = prm['gst_info']['indicator_receipt'];
			$('tb_purchase').hide();
			$('tb_supply').show();
		}
	},
	
	code_changed: function(obj){
		$('second_tax_code').value = $('code').value;
	}
}
</script>
{/literal}

<h1>{$PAGE_TITLE}</h1>

<div>
	{if $sessioninfo.privilege.MST_GST_EDIT}
		<a onclick="MASTERFILE_GST_MODULE.toggle_gst_table('add');" style="cursor:pointer;"><img src="ui/icons/user_add.png" title="Create New GST" align="absmiddle" border="0"> Create New GST</a> <span id="span_loading"></span><br /><br />
	{/if}
</div>

<ul>
	<li>You need privilege Masterfile (Common) > 'MST_GST_EDIT' to edit the tax.</li>
</ul>

{include file="masterfile_gst.list.tpl"}

<br>

<div class="ndiv" id="div_gst_table" style="position:absolute;width:500px;height:300px;display:none;z-index:10000;">
<div class="blur"><div class="shadow"><div class="content">

<div class="small" style="position:absolute; right:10; text-align:right;"><a onclick="curtain_clicked();" accesskey="C"><img src="ui/closewin.png" border="0" align="absmiddle" style="pointer:cursor;"></a></div>

<form method="post" name="f_a" onSubmit="return MASTERFILE_GST_MODULE.validate();">
	<div id="bmsg" style="padding:10 0 10 0px;"></div>
	<div id="err_msg" style="color:#CE0000; display:none; font-weight:bold;"></div>
	<input type="hidden" name="a" value="add">
	<input type="hidden" name="id" value="">
	<table id="tb">
		<tr>
			<td width="120"><b>Tax Code</b></td>
			<td><input onBlur="uc(this)" name="code" id="code" size="20" maxlength="30" onchange="MASTERFILE_GST_MODULE.code_changed(this);"> <img src="ui/rq.gif" align="absbottom" title="Required Field"></td>
		</tr>
		<tr>
			<td width="120"><b>Second Tax Code</b></td>
			<td><input onBlur="uc(this)" name="second_tax_code" id="second_tax_code" size="20" maxlength="30"></td>
		</tr>
		<tr>
			<td><b>Description</b></td>
			<td><input name="description" size="40"> <img src="ui/rq.gif" align="absbottom" title="Required Field"></td>
		</tr>
		<tr>
			<td><b>Tax Type</b></td>
			<td>
				<select name="type" onchange="MASTERFILE_GST_MODULE.toggle_type();">
					{foreach from=$tax_type_list key=val item=desc}
						<option value="{$val}">{$desc}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<td><b>Rate</b></td>
			<td><input onchange="MASTERFILE_GST_MODULE.perc_check(this);" name="rate" class="r" size="5"> %</td>
		</tr>
		<tbody id="tb_purchase">
			<tr>
				<td valign="top"><b>Special Code for Vendor</b></td>
				<td><input type="checkbox" name="is_vd_special_code" value="1"></td>
			</tr>
			<!--tr>
				<td valign="top"><b>Purchase Price<br />Include GST</b></td>
				<td><input type="checkbox" name="inc_item_cost" value="1"></td>
			</tr>
			<!--tr>
				<td valign="top"><b>Vendor GST Settings</b></td>
				<td>
					<select name="vendor_gst_setting">
						{foreach from=$vd_gst_settings_list key=val item=desc}
							<option value="{$val}">{$desc}</option>
						{/foreach}
					</select>
				</td>
			</tr-->
		</tbody>
		<tbody id="tb_supply">
			<tr>
				<td><b>Indicator</b></td>
				<td><input name="indicator_receipt" size="10" maxlength="10" onchange="uc(this);"> </td>
			</tr>
		</tbody>
	</table>
	<!-- bottom -->
	<div align="center" id="abtn" style="display:none;">
		<input type="button" value="Add" id="add_btn"> 
		<input type="button" value="Cancel" id="cancel_btn">
	</div>
	<div align="center" id="ebtn" style="display:none;">
		<input type="button" value="Update" id="update_btn"> 
		<input type="button" value="Restore" id="edit_btn"> 
		<input type="button" value="Close" id="close_btn">
	</div>
</form>
</div></div></div>

</div>

<div style="display:none"><iframe name="_irs" width="500" height="400" frameborder="1"></iframe></div>

<script>
//init_chg(document.f_b);
MASTERFILE_GST_MODULE.initialize();
</script>

{include file=footer.tpl}
