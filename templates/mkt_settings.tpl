{include file=header.tpl}
{literal}
<style>
.st_block {
	border-left:1px solid #ccc;
	border-top:1px solid #ccc;
}
.st_block td, .st_block th {
	border-right:1px solid #ccc;
	border-bottom:1px solid #ccc;
}
.st_block th { padding:4px; text-align:left; }
.st_block .lastrow th { background:#f00; color:#fff;}
.st_block .title { background:#e4efff; color:#00f;  }
.st_block input { border:1px solid #fff; margin:0;padding:0; }
.st_block input:hover { border:1px solid #00f; }
.st_block input.focused { border:1px solid #fec; background:#ffe; }
</style>

<script>
function do_save()
{
	document.f_a.a.value='save';
	if(check_a())
	{
	    new Ajax.Request('/mkt_settings.php', {
	        method: 'post',
			parameters: Form.serialize(document.f_a),
			onComplete: function(m) {
			    alert(m.responseText);
			}
		});
	}
}
function check_a()
{
	return true;
}
</script>
{/literal}
<h1>{$PAGE_TITLE}</h1>

{if $errm.top}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.top item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

<form name=f_a method=post>
<input type=hidden name=a value=save>

- Set the minimum no. of proposed "Offer Item" and "Offer Brand" required for each branch and each department
<table class=st_block cellspacing=0 cellpadding=2 border=0>
<tr bgcolor=#ffee99>
<th>&nbsp;</th>
{foreach from=$branches item=branch}
<th colspan=2>{$branch.code}</th>
{/foreach}
</tr>

<tr class=small bgcolor=#ffee99>
<th>&nbsp;</th>
{foreach from=$branches item=branch}
<th>min offer</th>
<th>min brand</th>
{/foreach}
</tr>

{foreach from=$lines item=line key=linename}
<tr bgcolor=#eeffff><th colspan={count var=$branches multi=2 offset=1}>{$linename}</th></tr>
{foreach from=$line.dept item=dept}
{assign var=did value=$dept.id}
<tr>
	<th nowrap>{$dept.description}</th>
	{foreach from=$branches item=branch}
	{assign var=bid value=$branch.id}
	<td><input size=5 name="settings[{$bid}][{$did}][min_offer]" value="{$form.settings.$bid.$did.min_offer|ifzero:''}" onclick="clear0(this)" onchange=mi(this)></td>
	<td><input size=5 name="settings[{$bid}][{$did}][min_brand]" value="{$form.settings.$bid.$did.min_brand|ifzero:''}" onclick="clear0(this)" onchange=mi(this)></td>
	{/foreach}
</tr>
{/foreach}
{/foreach}
</table>
<p id=submitbtn align=center>
<input name=bsubmit type=button value="Save & Close" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_save()" >
</p>
</form>

{include file=footer.tpl}
<script>
{if $smarty.request.a eq 'view'}
Form.disable(document.f_a);
{/if}
_init_enter_to_skip(document.f_a);
</script>
