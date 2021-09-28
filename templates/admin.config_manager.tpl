{*
8/13/2013 3:48 PM Andy
- Add/Show config default value.

9/9/2013 3:42 PM Andy
- Add a hidden input to hold config_name.
*}

{include file='header.tpl'}

<style>
{literal}
.section_name{
	background-color: #efefef;
	cursor:s-resize;
}
.section_name td{
    border-bottom:1px solid #999;
}
.error_msg{
	color: red;
}
.error_row{
	background: #ffd0f0;
}
.config_not_set{
	background-color:grey;
	color: #fff;
}
.config_row td{
      border-bottom: 1px solid #999;
}
{/literal}
</style>

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
var arr_config = [];
{foreach from=$config_list key=section_name item=section}
    {foreach from=$section key=config_name item=config_data}
        arr_config.push('{$config_name}');
    {/foreach}
{/foreach}

{literal}
function change_row_config_editable(ele, config_name){
	var c = ele.checked;
	
	$('div_setting-'+config_name).getElementsBySelector("input", "textarea", "select").each(function(inp){
		$(inp).disabled = !c;
	});
}

function save_config(){
	if(!confirm('Are you sure?'))   return false;
	
	// change button to prevent user click it again
	var btn_save = $('btn_save');
	btn_save.value = 'Saving...';
	btn_save.disabled = true;
	
	// construct params
	clear_config_error();
	var params = $(document.f_a).serialize();
	
	new Ajax.Request(phpself, {
		parameters: params,
		onComplete: function(e){
		    try{
		        // try to decode json
                eval('var json = '+e.responseText);
                if(json['ok']){
					alert('Save successfully.');
					// reload window
					//window.location.reload(0);
				}else if(json['error']){  // got error
					var err = json['error'];
					for(var i=0; i<err.length; i++){
						var config_name = err[i]['config_name'];
						var error_msg = err[i]['error_msg'];
						$('div_setting_error-'+config_name).update(error_msg);
						$('tr_config_row-'+config_name).addClassName('error_row');
					}
					alert('Save Failed! Please correct the error and save again.')
				}
			}catch(ex){
			    // failed to decode json
				alert(e.responseText);
			}
			
			// enable back the button
			btn_save.disabled = false;
			btn_save.value = 'Save';
		}
	})
}

function clear_config_error(){
	// remove row error color
	$$('#tbl_config tr.config_row').invoke('removeClassName', 'error_row');
	// remove row error message
	$$('#tbl_config div.error_msg').invoke('update', '');
}

function toggle_all_config(show){
	// image location
	var img_src = 'ui/'+(show ? 'collapse' : 'expand')+'.gif';

	// change image src
	$$('#tbl_config img.img_section').each(function(img){
		img.src = img_src;
	});
	
	// show/hide tbody
	$$('#tbl_config tbody.tbody_config').invoke((show ? 'show' : 'hide'));
}

function searh_cf_name(){
	// clear highlight row color
	$$('#tbl_config tr.config_row').invoke('removeClassName', 'highlight_row');
	
	// check search
	var cf_name = $('inp_search_cf_name').value.trim();
	if(!cf_name)    return; // empty
	
	// try to get the <tr> row element
	var tr = $('tr_config_row-'+cf_name);

	if(!tr){    // row not found
		alert('Config Name \''+cf_name+'\' cannot be found.');
		return false;
	}else{  // row found
	    // get parent tbody
	    var parent_tbody = $(tr).parentNode;
	    var section_name = $(parent_tbody).id.split('-')[1];

		// change section img
		$('img_section-'+section_name).src='ui/collapse.gif';
	    $(parent_tbody).show(); // show whole tbody
	    
	    // show highlight color
		$(tr).addClassName('highlight_row').scrollTo();
	}
}

function initial_config_autocomplete(){
	var a = new Autocompleter.Local('inp_search_cf_name', 'div_search_cf_name_list', arr_config);
}
{/literal}
</script>
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<form name="f_search_cf" onSubmit="searh_cf_name();return false;">
			<b>Config Name: </b>
			<input type="text" class="form-control mt-2" id="inp_search_cf_name" />
			<input type="submit" class="btn btn-primary mt-2" value="Find" />
			<div id="div_search_cf_name_list" class="autocomplete" style="display:none;height:150px !important;width:400px !important;overflow:auto !important;z-index:100; background-color: white; color: black;"></div>
		</form>
	</div>
</div>
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary ">
				<a href="javascript:void(toggle_all_config(1));">Expand All</a> /
				<a href="javascript:void(toggle_all_config(0));">Collapse All</a>

			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>


<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" method="post">
			<input type="hidden" name="a" value="save_config" onSubmit="return false;" />
			
		<div>
		<div class="table-responsive">
			<table id="tbl_config" class="report_table table mb-0 text-md-nowrap  table-hover">
				<thead class="bg-gray-100">
					<tr>
						<th>Config Name</th>
						<th>Value in config.php</th>
						<th>Active <a href="javascript:void(alert('If no tick, it will follow config.php'));"><img src="/ui/icons/information.png" align="absmiddle" border="0" /></a><br />(override config.php)</th>
						<th>Settings</th>
						<th>Sample Value</th>
						<th>Default Value</th>
					</tr>
				</thead>
				{foreach from=$config_list key=section_name item=section}
					<tr class="section_name " id="tr_section_row-{$section_name}">
						<td colspan="6" onClick="togglediv('tbody_config_section-{$section_name}', 'img_section-{$section_name}');">
							<h4 style="margin:0" class="fs-08">
								<img src="ui/expand.gif" border="0" title="Expand/Collapse" class="img_section" id="img_section-{$section_name}" />
								{$section_name}
								({count var=$section})
							</h4>
						</td>
					</tr>
					<tbody style="display:none;"  id="tbody_config_section-{$section_name}" class="tbody_config">
						{foreach from=$section key=config_name item=config_data}
							{assign var=is_disabled value='disabled'}
							{if $config_master.$config_name.active}{assign var=is_disabled value=''}{/if}
									
							<tr class="fs-08" id="tr_config_row-{$config_name}" class="config_row thover">
								<td><b class="b_config_name">{$config_name}</b>
									{if $config_data.description}<br /><span style="color:#6f6f6f;">{$config_data.description}</span>{/if}
								</td>
								
								<!-- Value in config.php -->
								<td  valign="top" class="{if !isset($default_config.$config_name)}config_not_set{/if}">
									{if isset($default_config.$config_name)}
										&nbsp;
										{if $config_data.type eq 'array'}
											<pre>{var_export var=$default_config.$config_name}</pre>
										{else}
											{$default_config.$config_name}
										{/if}
									{else}
										<i>-- Not set --</i>
									{/if}
								</td>
								
								<td style="text-align:center;">
									{* Name *}
									<input type="hidden" name="config_master[{$config_name}][config_name]" value="{$config_name}" />
									
									{* Active *}
									<input type="checkbox" name="config_master[{$config_name}][active]" value="1" onChange="change_row_config_editable(this, '{$config_name}');" {if !$is_disabled}checked {/if} />
								</td>
								
								<!-- Settings -->
								<td  valign="top">
									<div id="div_setting-{$config_name}">
										<input type="hidden" name="config_master[{$config_name}][type]" value="{$config_data.type}" {$is_disabled} />
										<!-- Radio -->
										{if $config_data.type eq 'radio'}
											{foreach from=$config_data.value key=v item=label}
												<input type="radio" name="config_master[{$config_name}][value]" value="{$v}" {if $config_master.$config_name.value eq $v}checked {/if} {$is_disabled} /> {$label}
											{/foreach}
										{elseif $config_data.type eq 'array'}
											<!-- Array -->
											<textarea name="config_master[{$config_name}][value]" style="width:200px;height:200px;" {$is_disabled}>{$config_master.$config_name.value}</textarea>
										{elseif $config_data.type eq 'str'}
											<!-- Str -->
											<input name="config_master[{$config_name}][value]" style="width:200px;" value="{$config_master.$config_name.value}" {$is_disabled} />
										 {elseif $config_data.type eq 'select'}
											<!-- Select -->
											<select name="config_master[{$config_name}][value]" {$is_disabled}>
												{foreach from=$config_data.value key=v item=label}
													<option value="{$v}" {if $config_master.$config_name.value eq $v}selected {/if}>{$label}</option>
												{/foreach}
											</select>
										{/if}
									</div>
									<div id="div_setting_error-{$config_name}" class="error_msg"></div>
								</td>
								
								<!-- Sample -->
								<td valign="top">
									{if $config_data.default_info.sample}
										<pre>
										{$config_data.default_info.sample}
										</pre>
									{else}&nbsp;
									{/if}
								</td>
								
								{* Default *}
								<td valign="top" class="{if !$config_data.default_info.default}config_not_set{/if}">
									{if $config_data.default_info.default}
										 <pre>
										{$config_data.default_info.default}
										</pre>
									{else}
										<i>-- No default value --</i>
									{/if}
								</td>
							</tr>
						{/foreach}
					</tbody>
				{/foreach}
			</table>
		</div>
		</div>
		</form>
		
	</div>
</div>
<div style="position:fixed;bottom:0;background:rgb(189, 202, 231);width:100%;text-align:center;left:0;padding:3px;opacity:0.9;">
		<input type="button" class="btn btn-primary fs-06" id="btn_save" value="Save" onClick="save_config();" />
</div>
{include file='footer.tpl'}

<script>
initial_config_autocomplete();
</script>
