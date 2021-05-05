{*
4/2/2019 9:35 AM Andy
- Enhanced the photo container to auto resize according to image size.
*}
<style>
{literal}
span.span_loading_actual_photo{
	padding:5px;
}
{/literal}
</style>

<script>

{literal}
var SHARED_SKU_PHOTO = {
	sku_photo_sample_html : '<img width=100 height=100 align=absmiddle vspace=4 hspace=4 src="__THUMB_SRC__" border=1 style="cursor:pointer" onclick=\'SHARED_SKU_PHOTO.show_img_full("__CONTENER_ID__", this);\' title="View" class="sku_photo" img_url="__SRC__" />',
	initialise: function(container_id, show_as_first_image){   // constructor accept prefix
		var THIS = this;
		if(!show_as_first_image)	show_as_first_image = 0;
		
		// ajax load sku item photo for multiple branch like aneka
		var params = {};
		if(show_as_first_image)	params['show_as_first_image'] = 1;
		this.ajax_load_sku_item_photo(container_id, params);
		
		if(show_as_first_image){
			this.show_first_image(container_id);
		}
		
		new Draggable('div_sku_photo_full',{ handle: 'div_sku_photo_full'});
	},
	// function to load actual item photo uploaded at other branch
	ajax_load_sku_item_photo: function(container_id, params){
		var THIS = this;
		
		var sid = $(container_id).readAttribute('sid');
		var span_loading_actual_photo = $('span_loading_actual_photo-'+container_id);
		if(!span_loading_actual_photo)	return;
		
		var url_to_get_photo = $(span_loading_actual_photo).readAttribute('url_to_get_photo');
		
		new Ajax.Request('http_con.php', {
			parameters:{
				a: 'ajax_load_sku_item_photo',
				sku_item_id: sid,
				url_to_get_photo: url_to_get_photo,
				SKIP_CONNECT_MYSQL: 1
			},
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
				var err_msg = '';
				
				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['photo_list']){ // success
						for(var i=0; i<ret['photo_list'].length; i++){
							var path = ret['photo_list'][i]; 
							var sku_photo_html = THIS.sku_photo_sample_html;
							var thumb_path = '/thumb.php?w=100&h=100&cache=1&img='+URLEncode(path);
							
							sku_photo_html = sku_photo_html.replace(/__SRC__/g, escape(path));
							sku_photo_html = sku_photo_html.replace(/__THUMB_SRC__/g, thumb_path);
							sku_photo_html = sku_photo_html.replace(/__CONTENER_ID__/g, container_id);

							new Insertion.After(span_loading_actual_photo, sku_photo_html);
							
						}
						$(span_loading_actual_photo).remove();
						
						if(params['show_as_first_image']){
							THIS.show_first_image(container_id);
						}
						return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}
				
				// failed
				$(span_loading_actual_photo).remove();
				
				if($$('#div_photo_icon-'+container_id+' img.sku_photo').length<=0){	// no photo found for this sku
					$('div_photo_icon-'+container_id).remove();	//remove container as well
				}
			}
		});
	},
	// show first image
	show_first_image: function(container_id){
		// Get first photo
		var img_sku_photo_list = $(container_id).getElementsBySelector("div.photo_hide img.sku_photo");
		if(img_sku_photo_list.length <= 0)	return;
		
		var img_sku_photo = img_sku_photo_list[0];	// get first photo
		var img_default = $('img_default-'+container_id);	// get default photo
		img_default.src = img_sku_photo.src;
		img_default.setAttribute('img_url', $(img_sku_photo).readAttribute('img_url'));
	},
	// function to get container_id by element
	get_container_id_by_element: function(ele){
		var parent_ele = ele

		while(parent_ele){    // loop parebt until it found the div contain group id
		    if(parent_ele.tagName.toLowerCase()=='span'){
                if($(parent_ele).hasClassName('parent_container')){    // found the div
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}

		if(!parent_ele) return 0;

		var container_id = parent_ele.id;
		return container_id;
	},
	// function to show full image
	show_img_full: function(container_id, img){
		var img_url = $(img).readAttribute('img_url');
		
		// no image url
		if(!img_url)	return;
		var img = $('img_sku_photo_full_content');
		img.src = URLEncode(img_url);
		img.setAttribute('current_container_id', container_id);
		center_div($('div_sku_photo_full').show());
	},
	// function to close full image
	close_img_full: function(){
		$('div_sku_photo_full').hide();
	},
	// function when full image loaded
	img_full_loaded: function(){
		div_sku_photo_full = $('div_sku_photo_full');
		if(div_sku_photo_full.style.display == 'none')	return;
		
		// Re-center again after image loaded
		center_div(div_sku_photo_full);
	}
}
{/literal}
</script>

<div id="div_sku_photo_full" class="curtain_popup" style="position:absolute;z-index:10005;min-width:600px;min-height:300px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_sku_photo_full_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">SKU Photo</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="SHARED_SKU_PHOTO.close_img_full();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_sku_photo_full_content" style="padding:2px;text-align:center;">
		<img src="" width="640" id="img_sku_photo_full_content" current_container_id="" onLoad="SHARED_SKU_PHOTO.img_full_loaded();" />
	</div>
</div>