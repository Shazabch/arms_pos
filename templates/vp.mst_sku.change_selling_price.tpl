{include file="header.tpl"}

{literal}
<style>
.tr_is_parent td{
	background-color: #cfcfcf;
}
</style>
{/literal}

<script type="text/javascript">

var phpself = '{$smarty.server.PHP_SELF}';

{literal}

var CHANGE_SELLING_PRICE_MODULE = {
	sku_autocomplete: undefined,
	updating_selling_price: false,
	initialize: function(){
		var THIS = this;
		
		this.sku_autocomplete = new SKU_AUTOCOMPLETE(document.f_a, 'vp-', function(sid){
			THIS.show_sku_info(sid);
		});
	},
	// function when user click "show" after search and choose sku
	show_sku_info: function(sid){
		if(!sid){
			alert('Please search and select 1 SKU first.');
			return false;
		}
		
		if(this.updating_selling_price){
			alert('Please wait for updating finish.');
			return false;
		}
		
		document.f_a['sid'].value = sid;
		document.f_a.submit();
	},
	// function when user change selling price
	selling_price_changed: function(sid){
		var inp = $('inp_selling_price-'+sid);
		
		if(!inp){
			alert('Selling Price Input Not Found.');
			return false;
		}
		
		mfz(inp, 2);	// round 2
		
		var is_parent = int(document.f_cp['is_parent['+sid+']'].value);
		if(is_parent){
			var inp_selling_price_list = $(document.f_cp).getElementsBySelector("input.inp_selling_price");
			
			if(inp_selling_price_list.length>1){
				// ask user whether want system auto change child selling price
				if(!confirm('Do you want to let system auto multiply selling price and fraction then apply to all child SKU?'))	return false;
				
				var parent_sp = float(inp.value);	// get parent selling price
				
				
				
				for(var i=0; i<inp_selling_price_list.length; i++){
					var tmp_sid = inp_selling_price_list[i].id.split('-')[1];
					
					if(tmp_sid == sid)	continue;
					
					var fraction = float(document.f_cp['packing_uom_fraction['+tmp_sid+']'].value);
					var new_sp = round(parent_sp*fraction,2);
					inp_selling_price_list[i].value = new_sp;
				}
			}			
		}
	},
	// function when user click "Update" selling price
	update_price_clicked: function(){
		var THIS = this;
		var inp_selling_price_list = $(document.f_cp).getElementsBySelector("input.inp_selling_price");
		if(inp_selling_price_list.length<=0){
			alert('There is no item to change price.');
			return false;
		}
		
		// check zero selling price
		for(var i=0; i<inp_selling_price_list.length; i++){
			var sp = float(inp_selling_price_list[i].value);
			if(sp<=0){
				alert('Selling price must more then zero.');
				inp_selling_price_list[i].select();
				return false;
			}
		}
		
		if(!confirm('Are you sure?'))	return false;
		
		$('btn_update_selling_price').disabled = true;
		$('span_updating_selling_price_loading').show();
		this.updating_selling_price = true;
		
		new Ajax.Request(phpself+'?a=ajax_update_selling_price', {
			parameters: $(document.f_cp).serialize(),
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
			    				
			    $('btn_update_selling_price').disabled = false;
				$('span_updating_selling_price_loading').hide();
				THIS.updating_selling_price = false;
					    
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						alert('Update Successfully');
						
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'No Respond from server.';
			    // prompt the error
			    alert(err_msg);
			}
		});
	}
}
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

{if $err}
	<div><div class="errmsg"><ul>
	{foreach from=$err item=e}
		<li> {$e}</li>
	{/foreach}
	</ul></div></div>
{/if}

<form name="f_a" method="post" onsubmit="return false" class="stdframe">
	<input type="hidden" name="show_sku" value="1" />
	<input type="hidden" name="sid" value="" />
	
	{include file="vp.sku_items_autocomplete.tpl" _add_value="Show" prefix="vp-"}
</form>

<script type="text/javascript">
	CHANGE_SELLING_PRICE_MODULE.initialize();
</script>

{if $smarty.request.show_sku and !$err}
	<br />
	
	{if !$data}
		* No Data *
	{else}
		<form name="f_cp" method="post" onSubmit="return false;">

			<table width="100%" class="report_table" cellpadding="0" cellspacing="0">
				<tr class="header">
					<th>&nbsp;</th>
					<th>ARMS Code</th>
					<th>MCode</th>
					<th>Art No.</th>
					<th>{$config.link_code_name|default:'Old Code'}</th>
					<th>Description</th>
					<th>Selling Price</th>
				</tr>
				
				{foreach from=$data.items key=sid item=r name=fr}
					<tr class="{if $r.is_parent}tr_is_parent{/if}">
						<td class="r">{$smarty.foreach.fr.iteration}.</td>
						<td>{$r.sku_item_code|default:'-'}</td>
						<td>{$r.mcode|default:'-'}</td>
						<td>{$r.artno|default:'-'}</td>
						<td>{$r.link_code|default:'-'}</td>
						<td>{$r.description|default:'-'}</td>
						<td align="center">
							<input type="hidden" name="is_parent[{$sid}]" value="{$r.is_parent}" />
							<input type="hidden" name="packing_uom_fraction[{$sid}]" value="{$r.packing_uom_fraction}" />
							<input type="text" id="inp_selling_price-{$sid}" class="inp_selling_price" name="selling_price[{$sid}]" value="{$r.selling_price|number_format:2:".":""}" style="text-align:right;width:100px;" onchange="CHANGE_SELLING_PRICE_MODULE.selling_price_changed('{$sid}')" />
						</td>
					</tr>
				{/foreach}
			</table>
			
			<p>
				<input type="button" value="Update" onClick="CHANGE_SELLING_PRICE_MODULE.update_price_clicked();" id="btn_update_selling_price" />
				<span id="span_updating_selling_price_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Updating...</span>
			</p>
		</form>
	{/if}
{/if}
{include file="footer.tpl"}
