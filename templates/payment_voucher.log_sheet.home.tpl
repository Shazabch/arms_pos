{include file=header.tpl}

<h1>{$PAGE_TITLE}</h1>

{literal}
<script>
function list_sel(n,s)
{
	var i;
	for(i=0;i<=2;i++)
	{
		if (i==n)
		    $('lst'+i).className='active';
		else
		    $('lst'+i).className='';
	}
	$('log_sheet_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	var pg = '';
	if (s!=undefined) pg = 's='+s;

	new Ajax.Updater('log_sheet_list', 'payment_voucher.log_sheet.php', {
		parameters: 'a=ajax_load_log_sheet_list&t='+n+'&'+pg,
		evalScripts: true
		});
}

function show_print_dialog(){
	center_div('print_dialog');
	$('print_dialog').style.display = '';
	$('print_dialog').style.zIndex = 10000;
}

function print_ok(){
	$('print_dialog').style.display = 'none';
	document.f_a.a.value = 'print';
	document.f_a.target = "ifprint";
	document.f_a.submit();
	curtain(false);
}

function do_print(ls_no,page,b){
	document.f_a.ls_no.value = ls_no;
	document.f_a.p.value = page;
	document.f_a.b.value = b;
	curtain(true);
	show_print_dialog();		
}

function curtain_clicked(){
	Element.hide('print_dialog');
	curtain(false);
}
</script>
{/literal}

<!-- Start print dialog -->
<div id=print_dialog style="background:#fff;border:3px solid #000;width:250px;height:80px;position:absolute; padding:10px; display:none;">
<p align=center>
This Cheque Issue Log Sheet will Print with <br>
<b>A5 Portrait</b> Format.
<br><br>
<input type=button value="Print" onclick="print_ok()"> 
<input type=button value="Cancel" onclick="curtain_clicked();">
</p>
</div>
<!--End print dialog -->


<iframe style="visibility:hidden" width=1 height=1 name=ifprint></iframe>

<form name=f_a>
<input type=hidden name=a>
<input type=hidden name=ls_no>
<input type=hidden name=p>
<input type=hidden name=b>
</form>

<form onsubmit="list_sel(0,find.value);return false;">
<div class=tab style="height:25px;white-space:nowrap;">
&nbsp;&nbsp;&nbsp;
<a href="javascript:list_sel(1)" id=lst1 class=active>Saved Log Sheet</a>
<a href="javascript:list_sel(2)" id=lst2>Completed</a>
<a id=lst0>Find <input name=find> <input type=submit value="Go"></a>
</div>
</form>
<div id=log_sheet_list  style="border:1px solid #000;width:40%">
</div>
{include file=footer.tpl}
<script>
list_sel(1);
</script>
