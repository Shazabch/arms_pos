{include file=header.tpl}

{literal}
<script>


function do_approve()
{
    if (document.f_a.approve_comment.value=='')
	{
	    document.f_a.approve_comment.value='Approved';
	}
	if (confirm('Press OK to Approve the GRN.'))
	{
	    document.f_a.a.value = "save_approval";
	    document.f_a.submit();
	}
}

function do_reject()
{

	if (empty(document.f_a.approve_comment, 'Comment is empty')) return false;
	if (confirm('Press OK to Reject the GRN.'))
	{
	    document.f_a.a.value = "reject_approval";
	    document.f_a.submit();
	}
}

function load_grn(id)
{
	$('loadgrn').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	new Ajax.Updater(
		"loadgrn",
		"goods_receiving_note_approval.php",
		{
		    method: 'get',
			parameters: 'a=ajax_load_grn&id='+id,
			evalScripts: true
		});
}
</script>
{/literal}
<h1>GRN Approval</h1>
<p>
Select a GRN to approve <select id=id>
{section name=i loop=$grn}
<option value="{$grn[i].id}">{$grn[i].id}</option>
{/section}
</select> <input type=button onclick="load_grn($('id').value)" value="Load">
</p>

<div id=loadgrn>
</div>
{include file=footer.tpl}
