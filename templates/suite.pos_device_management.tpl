{include file='header.tpl'}

{literal}
<style>
.div_banner_prev{
	height: 550px;
	float: left;
	margin-right: 20px;
	border-width:3px;
	border-style: outset;
	text-align: center;
}

.div_banner_prev:hover{
	border-style: inset;
}
</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var POS_DEVICE_MANAGEMENT = {
	f_screen: undefined,
	initialise: function(){
		this.f_screen = document.f_screen;
		
		this.load_screen_banner();
	},
	// function when user change screen
	screen_changed: function(){
		this.load_screen_banner();
	},
	// core function to load screen banner
	load_screen_banner: function(){
		$('div_banner_list').update(_loading_);
		
		var params = $(this.f_screen).serialize()+'&a=ajax_load_screen_banner';
		var THIS = this;
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						// Update html
						$('div_banner_list').update(ret['html']);
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

				if(!err_msg)	err_msg = 'Server No Respond';
				
			    // prompt the error
			    alert(err_msg);
				$('div_banner_list').update(err_msg);
			}
		});
	}
}
{/literal}
</script>
<h1>{$PAGE_TITLE}</h1>

<form name="f_screen" onSubmit="return false;">
<p>
	<b>Select Screen: </b>
	<select name="screen_name" onChange="POS_DEVICE_MANAGEMENT.screen_changed();">
		{foreach from=$screen_list key=screen_name item=screen_info}
			<option value="{$screen_name}" {if $smarty.request.screen_name eq $screen_name}selected {/if}>{$screen_info.screen_description}</option>
		{/foreach}
	</select>
</p>
</form><br />

<div id="div_banner_list" class="stdframe" style="background-color: #fff;"></div>

<script>POS_DEVICE_MANAGEMENT.initialise();</script>

{include file='footer.tpl'}