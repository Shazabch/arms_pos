{**}
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
var TAX_LISTING_MODULE = {
	add : function(){
		this.open(0, 0);
	},
	open : function(id, is_restore){
		if(is_restore && !confirm("Are you sure want to restore?")) return;
		new Ajax.Request(phpself, {
			method: 'post',
			parameters: {
				'a': 'open',
				'id': id
			},
			onComplete: function(msg){			    
				var str = msg.responseText.trim();
				var ret = {};
				var err_msg = '';
				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['ok']){ // success
						$('tax_settings_table').update(ret['html']);
						center_div('tax_settings_table');
						$('tax_settings_table').show();
						curtain(true);
						return;
					}else{
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){
					err_msg = str;
				}

				// prompt the error
				alert(err_msg);
			}
		});
	},
	ajax_update_tax : function(id){
		var code = document.f_a.code;
		var description = document.f_a.description;
		var tax_apply_to = document.f_a['tax_apply_to[]'];
		
		if(code.value == ''){
			alert("Please Enter Code.");
			return false;
		}
		if(description.value == ''){
			alert("Please Enter Description.");
			return false;
		}
		
		if(tax_apply_to.length > 0){
			var tax_apply_to_checked = 0;
			for(var i=0; i < tax_apply_to.length; i++){
				if(tax_apply_to[i].checked == true)  tax_apply_to_checked+= 1;
			}
			
			if(tax_apply_to_checked <= 0){
				alert("Please select Tax Apply To checkbox.");
				return false;
			}
		}
		
		new Ajax.Request(phpself, {
			method: 'post',
			parameters: $(document.f_a).serialize(),
			onComplete: function(msg){			    
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						alert("Update Success");
						location.reload(true);
						return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    alert(err_msg);
			}
		});
	},
	toggle_activation: function(id, active){
		if(active) var status_desc = "activate";
		else var status_desc = "deactivate";
	
		if(!confirm("Are you sure want to "+status_desc+" the selected tax?")) return;
		
		new Ajax.Request(phpself, {
			method: 'post',
			parameters: {
				'a': 'ajax_active_changed',
				'id': id,
				'active': active
			},
			onComplete: function(msg){			    
				var str = msg.responseText.trim();
				var ret = {};
				var err_msg = '';
				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['ok']){ // success
						location.reload(true);
						return;
					}else{
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){
					err_msg = str;
				}

				// prompt the error
				alert(err_msg);
			}
		});
	}
}

function curtain_clicked(){
	hidediv('tax_settings_table');
	curtain(false);
}

</script>
{/literal}

<h1>{$PAGE_TITLE}</h1>
<br>
{if $sessioninfo.privilege.ADMIN_TAX_EDIT}
<p><a onclick="TAX_LISTING_MODULE.add()"><img src="ui/icons/page_add.png" align="absmiddle" border="0" /> Create New Tax</a></p>
{/if}
<div class="stdframe">
<table class="sortable" border="0" cellpadding="4" cellspacing="1">
	<tr>
		<th bgcolor="{#TB_CORNER#}">&nbsp;</th>
		<th bgcolor="{#TB_COLHEADER#}">Tax Code</th>
		<th bgcolor="{#TB_COLHEADER#}">Description</th>
		<th bgcolor="{#TB_COLHEADER#}">Rate (%)</th>
		<th bgcolor="{#TB_COLHEADER#}">Indicator</th>
		<th bgcolor="{#TB_COLHEADER#}">Tax Apply To</th>
		<th bgcolor="{#TB_COLHEADER#}">Last Update</th>
	</tr>
	{foreach from=$tax_list item=r}
	<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
		<td>
		{if $sessioninfo.privilege.ADMIN_TAX_EDIT}
			<a onclick="TAX_LISTING_MODULE.open('{$r.id}', 0);"><img src="ui/ed.png" title="Edit" border="0"></a>
			{if $r.active}
				<a href="javascript:void(TAX_LISTING_MODULE.toggle_activation({$r.id}, 0));"><img src="ui/deact.png" title="Deactivate this Tax" border="0"></a>
			{else}
				<a href="javascript:void(TAX_LISTING_MODULE.toggle_activation({$r.id}, 1));"><img src="ui/act.png" title="Activate this Tax" border="0"></a>
				<br /><span class="small">(inactive)</span>
			{/if}
		{/if}
		</td>
		<td>{$r.code}</td>
		<td>{$r.description}</td>
		<td>{$r.rate}</td>
		<td>{$r.indicator_receipt}</td>
		<td>{$r.tax_apply_to}</td>
		<td>{$r.last_update}</td>
	</tr>
	{foreachelse}
		<tr>
			<td colspan="6">* No Data *</td>
		</tr>
	{/foreach}
</table>
</div>
<div class="ndiv" id="tax_settings_table" style="position:absolute;width:500px;display:none;height:300px;z-index:10000;">
	{include file="admin.tax_listing.open.tpl"}
</div>

{include file=footer.tpl}