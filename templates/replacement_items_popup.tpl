<script>
{literal}
var replacement_item = {
	sid: '',
	can_confirm_item: 0,
	exclude_self: 0,
	can_click_item_row: 0,
	php: 'replacement_items_popup.php',
	show_popup: function(params){
	    this.sid = params['sid'];
	    this.can_confirm_item = params['can_confirm_item'];
	    this.exclude_self = params['exclude_self'];
	    this.can_click_item_row = params['can_click_item_row'];
	    
	    this.load_available_sku();
	    curtain(true);
		center_div($('div_ri_popup').show());
	},
	load_available_sku: function(){
		if(!this.sid){
			$('div_ri_popup_content').update('Invalid SKU');
			return false;
		}
		$('div_ri_popup_content').update(_loading_);
		new Ajax.Updater('div_ri_popup_content', this.php+'?a=load_available_sku',{
			parameters:{
				'sid': this.sid,
				'can_confirm_item': this.can_confirm_item,
				'exclude_self': this.exclude_self,
				'can_click_item_row': this.can_click_item_row
			}
		});
	},
	popup_close: function(){
        default_curtain_clicked();
	},
	confirm_selected_item: function(){
		var selected_sid = getRadioValue(document.f_ri['sku_item_id']);
		confirm_replacement_item(selected_sid);  // u must create this function in your own templates
	},
	item_code_clicked: function(sku_item_id, sku_item_code){
		var params = {
			'sku_item_id': sku_item_id,
			'sku_item_code': sku_item_code
		};
		
		replacement_item_code_clicked(params);  // u must create this function in your own templates
	}
}
{/literal}
</script>

<div id="div_ri_popup" class="curtain_popup" style="position:absolute;z-index:10000;width:600px;height:450px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_ri_popup_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Available Replacement Item</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="replacement_item.popup_close();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_ri_popup_content" style="padding:2px;"></div>
</div>


{literal}
<script>
new Draggable('div_ri_popup',{ handle: 'div_ri_popup_header'});
</script>
{/literal}
