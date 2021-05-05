{include file='header.tpl'}

<script>

var phpself = '{$smarty.server.PHP_SELF}';

{literal}
function open(id){

	curtain(true);
	center_div($('div_interest_rate_details').show());
	$('div_interest_rate_details_content').update(_loading_);
	new Ajax.Updater('div_interest_rate_details_content', phpself, {
		parameters:{
			'a': 'ajax_open',
			'id': id
		}
	});
}

function save_interest_rate(){
	if(document.f_a['year'].value.trim()==''){
		alert('Please enter year.');
		document.f_a['year'].focus();
		return false;
	}
	if(int(document.f_a['year'].value)<2000){
		alert('Incorrect year.');
		document.f_a['year'].focus();
		return false;
	}
	if(float(document.f_a['interest_rate'].value)<=0){
		alert('Incorrect interest rate.');
		document.f_a['interest_rate'].focus();
		return false;
	}
	
	$$('#p_save_f_a input').each(function(inp){
		$(inp).disabled = true;
	});
	
	new Ajax.Request(phpself, {
		parameters: $(document.f_a).serialize(),
		onComplete: function(e){
			var msg = e.responseText.trim();
			if(msg=='OK'){
                reload_table();
                default_curtain_clicked();
			}else{
				alert(msg);
				$$('#p_save_f_a input').each(function(inp){
					$(inp).disabled = false;
				});
			}
		}
	});
}

function reload_table(){
    $('span_refreshing').update(_loading_);
	new Ajax.Updater('div_table', phpself+'?a=load_table_list');
}

function delete_interest_rate(id){
	if(!id) return;
	if(!confirm('Click OK to confirm.'))    return;
	
	$('span_refreshing').update(_loading_);
	new Ajax.Updater('div_table', phpself, {
		parameters:{
			'a': 'ajax_delete_interest_rate',
			'id': id
		},
		onComplete: function(e){

		}
	});
}
{/literal}
</script>

<div id="div_interest_rate_details" class="curtain_popup" style="position:absolute;z-index:10000;width:300px;height:150px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0;">
	<div id="div_interest_rate_details_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Bank Interest Rate</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_interest_rate_details_content" style="padding:2px;"></div>
</div>

<h1>{$PAGE_TITLE}</h1>
<p>
	<a href="javascript:void(open(0));" accesskey='A'><img src="ui/new.png" align="absmiddle" border="0" /> Add New Interest Rate</a> (Alt + A)
</p>

<div id="div_table" class="stdframe">
    {include file='masterfile_bank_interest.table.tpl'}
</div>

{include file='footer.tpl'}
