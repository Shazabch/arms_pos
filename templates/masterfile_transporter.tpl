{*
9/3/2010 10:51:36 AM Andy
- Remove promt confirmation if user click close edit.
*}

{include file='header.tpl'}
<h1>{$PAGE_TITLE}</h1>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
{literal}

function open(id){
    curtain(true);
	center_div('div_transporter_popup');
	$('div_transporter_popup').show();
	$('div_transporter_popup_content').update(_loading_);
	new Ajax.Updater('div_transporter_popup_content',phpself,{
	    parameters:{
			a: 'open',
			id: id
		},
		evalScripts: true
	})
}

function submit_form(action){
	if(action=='close'){
        //if(!confirm('Are you sure?'))   return false;
        default_curtain_clicked();
        return false;
	}else if(action=='save'){
		if(!document.f_a['code'].value.trim()){
			alert('Please enter code.');
			return false;
		}
		if(!document.f_a['company_name'].value.trim()){
			alert('Please enter Company Name.');
			return false;
		}

		if(!confirm('Are you sure?'))   return false;

        $('btn_save').disable().value = 'Saving...';

        new Ajax.Request(phpself,{
			parameters: document.f_a.serialize(),
			onComplete: function(e){
				var msg = e.responseText.trim();
				if(msg!='OK'){
					alert(msg);
					$('btn_save').enable().value = 'Save';
					return;
				}
				reload_table();
				alert('Save successfully.');
				default_curtain_clicked();
			}
		})
	}
}

function reload_table(){
	$('span_refreshing').update(_loading_);
	new Ajax.Updater('div_table',phpself,{
		parameters:{
			a: 'reload_table'
		}
	});
}

function act(id,status){
    $('span_refreshing').update(_loading_);
	new Ajax.Updater('div_table',phpself,{
		parameters:{
			a: 'toggle_status',
			id: id,
			status: status
		}
	});
}
{/literal}
</script>
<div id="div_transporter_popup" class="curtain_popup" style="position:absolute;z-index:10000;width:500px;height:250px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding: 0 !important;">
	<div id="div_transporter_popup_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Multiple Add SKU</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_transporter_popup_content" style="padding:2px;"></div>
</div>

<div><a accesskey="A" href="javascript:void(open(0))"><img src=ui/new.png title="New" align=absmiddle border=0></a> <a href="javascript:void(open(0))"><u>A</u>dd New Transporter</a> (Alt+A)</div>

<br>
<div id="div_table" class="stdframe">
	{include file='masterfile_transporter.table.tpl'}
</div>

{include file='footer.tpl'}


{literal}
<script>
new Draggable('div_transporter_popup',{ handle: 'div_transporter_popup_header'});
</script>
{/literal}
