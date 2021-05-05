
{include file=header.tpl}

<h1>{$PAGE_TITLE}</h1>

<script>

var tab = '{$smarty.request.t}';
{literal}

var DSP = {
	initialize: function(){
		this.list_sel(tab, '{$smarty.request.search}');
	},
	list_sel: function(n,s){
		var i;
		for(i=0;i<=6;i++){
			if ($('lst'+i)!=undefined){
				if (i==n){
					$('lst'+i).className='active';			
				}
				else{
					$('lst'+i).className='';
				}			    
			}
		}
		$('adjust_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

		var pg = '';
		if (s!=undefined) pg = 's='+s;
		if (n==0) pg +='&search='+ $('search').value;
		if (n==6) pg +='&search='+ $('search_bid').value;
		
		new Ajax.Updater('adjust_list', 'vp.disposal.php', {
			parameters: 'a=ajax_load_adjust_list&t='+n+'&'+pg,
			evalScripts: true
			});
	},
	do_print: function(id,bid) {
		document.f_print.id.value=id;
		document.f_print.branch_id.value=bid;
		curtain(true);
		document.f_print.a.value='print_disposal';
		center_div('print_dialog');
		$('print_dialog').style.display = '';
		$('print_dialog').style.zIndex = 10000;
	},
	curtain_clicked: function() {
		$('print_dialog').style.display = 'none';
		curtain(false);
	},
	print_ok: function (){
		$('print_dialog').style.display = 'none';
		document.f_print.a.value='print_disposal';
		document.f_print.target = '_blank';
		document.f_print.submit();
		curtain(false);
	}
};
{/literal}

</script>

<!-- Start print dialog -->
<div id=print_dialog style="background:#fff;border:3px solid #000;width:260px;height:80px;position:absolute; padding:10px; display:none;">
<form name=f_print method=post>
<input type=hidden name=a>
<input type=hidden name=id>
<input type=hidden name=branch_id>
<table width="100%">
	<tr>
		<td colspan="2" align="center">
			This Disposal will Print with <br> <b>A4 Portrait</b> Format.
		</td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td colspan="2" align="center">
			<input type=button value="Print" onclick="javascript:DSP.print_ok()">
			<input type=button value="Cancel" onclick="javascript:DSP.curtain_clicked();">
		</td>
	</tr>
</table>
</p>
</form>
</div>
<!--End print dialog -->

<div id=show_last>
{if $smarty.request.t eq 'save'}
<img src=/ui/approved.png align=absmiddle> Disposal saved as ID#{$smarty.request.id}<br>
{elseif $smarty.request.t eq 'delete'}
<img src=/ui/cancel.png align=absmiddle> Disposal ID#{$smarty.request.id} was deleted<br>
{elseif $smarty.request.t eq 'confirm'}
<img src=/ui/approved.png align=absmiddle> Disposal ID#{$smarty.request.id} confirmed. 
{/if}
</div>

<ul>
<li> <img src=ui/new.png align=absmiddle> 
<a href=vp.disposal.php?a=open&id=0>Create New Disposal</a>
</ul>

<br>

<form onsubmit="javascript:DSP.list_sel(0,0);return false;">
<div class=tab style="height:25px;white-space:nowrap;">
&nbsp;&nbsp;&nbsp;
<a href="javascript:DSP.list_sel(1)" id=lst1 class=active>Saved</a>
<a href="javascript:DSP.list_sel(4)" id=lst4>Completed</a>
<a name=find_po id=lst0>Find <input id=search name=pono> <input type=submit value="Go"></a>
</div>
</form>
<div id=adjust_list style="border:1px solid #000">
</div>
{include file=footer.tpl}

<script>
DSP.list_sel(1);
</script>
