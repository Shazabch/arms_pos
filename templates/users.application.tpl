{*
3/01/2021 5:17 PM Rayleen
- New Module "User EForm Application"
*}
{include file=header.tpl}
<script type="text/javascript">

{literal}
function list_sel(n)
{
	var i;
	tab = n;
	for(i=0;i<=3;i++)
	{
		if ($('lst'+i)!=undefined)
		{
			if (i==n)
			    $('lst'+i).className='active';
			else
			    $('lst'+i).className='';
		}
	}
	$('application_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	new Ajax.Updater('application_list', 'users.application.php', {
		parameters: encodeURI('a=ajax_user_list&status='+n),
		evalScripts: true
		});
}
{/literal}
</script>


<h1>User EForm Application Lists</h1>
<form name="f_l" onsubmit="list_sel(0,0);return false;">
	<div class=tab style="height:20px;white-space:nowrap;">
		&nbsp;&nbsp;&nbsp;
		<a href="javascript:list_sel(0)" id="lst0" class="active">New Application</a>
		<a href="javascript:list_sel(2)" id="lst2">Rejected</a>
		<a href="javascript:list_sel(1)" id="lst1">Approved</a>
		<a href="javascript:list_sel(3)" id="lst3">Activated</a>
	</div>
	<div id="application_list" >
	</div>
</form>

{include file=footer.tpl}
<script>
{literal}
	list_sel(0);
{/literal}
</script>
