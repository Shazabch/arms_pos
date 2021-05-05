{include file=header.tpl}

<h1>{$PAGE_TITLE}</h1>

<ul>
<li> <img src=ui/new.png align=absmiddle> <a href="?a=new">Create New</a>
</ul>
<br>
{literal}
<script>
function list_sel(n,s)
{
	var i;
	for(i=0;i<=3;i++)
	{
		if (i==n)
		    $('lst'+i).className='active';
		else
		    $('lst'+i).className='';
	}
	$('mkt_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	var pg = '';
	if (s!=undefined) pg = 's='+s;

	new Ajax.Updater('mkt_list', 'mkt0.php', {
		parameters: 'a=ajax_load_mkt_list&t='+n+'&'+pg,
		evalScripts: true
		});
}
</script>
{/literal}

<form onsubmit="list_sel(0,find.value);return false;">
<div class=tab style="height:25px;white-space:nowrap;">
&nbsp;&nbsp;&nbsp;
<a href="javascript:list_sel(1)" id=lst1 class=active>Saved Sales Target</a>
<a href="javascript:list_sel(2)" id=lst2>Confirmed</a>
<a href="javascript:list_sel(3)" id=lst3>Cancelled</a>
<a id=lst0>Find <input name=find> <input type=submit value="Go"></a>
</div>
</form>
<div id=mkt_list style="border:1px solid #000">
</div>
{include file=footer.tpl}

<script>
list_sel(1);
</script>
