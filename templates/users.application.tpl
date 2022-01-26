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
			    //$('lst'+i).className='active';
			    jQuery('lst'+i).addClassName('active');
			else
			    //$('lst'+i).className='';
				jQuery('lst'+i).removeClassName('active');
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

<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">User EForm Application Lists</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<div class="card mx-2">
	<div class="card-body">
		<form name="f_l" onsubmit="list_sel(0,0);return false;">
			<div class=tab style="white-space:nowrap;">
				&nbsp;&nbsp;&nbsp;
				<a href="javascript:list_sel(0)" id="lst0" class="btn btn-outline-primary btn-rounded ">New Application</a>
				<a href="javascript:list_sel(2)" id="lst2" class="btn btn-outline-primary btn-rounded">Rejected</a>
				<a href="javascript:list_sel(1)" id="lst1" class="btn btn-outline-primary btn-rounded">Approved</a>
				<a href="javascript:list_sel(3)" id="lst3" class="btn btn-outline-primary btn-rounded">Activated</a>
			</div>
			<div id="application_list" >
			</div>
		</form>
	</div>
</div>

{include file=footer.tpl}
<script>
{literal}
	list_sel(0);
{/literal}
</script>
