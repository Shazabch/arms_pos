{*
5/24/2010 3:49:16 PM Andy
- Masterfile branch trade discount can insert secondary discount percent using "+".
*}

<script>
var allow_secondary_discount = '{$config.allow_secondary_discount}';
{literal}
check_value = function(ele){
    var discount = ele.value.trim();
    if(allow_secondary_discount){
        var discount_format = /^\d+(\.\d+){0,1}(\+\d+(\.\d+){0,1}){0,1}$/;
	    if(!discount_format.test(discount)){
	        discount = '';
		}
	}else   discount = float(discount);
    
    ele.value= discount;
    /*if(ele.value>100){
		alert('Discount % cannot over 100');
		ele.value = 100.0;
	}*/
}
close_discount_table = function(){
	if(confirm('Are you sure to close it without save?')){
		default_curtain_clicked();
	}
}

save_discount_table = function(){
	if(!confirm('Click OK to confirm save.'))   return false;
	
	$('btn_save_discount_table').disabled = true;
	
	new Ajax.Request(phpself+'?a=save_discount_table',{
		parameters: $('form_discount_table').serialize(),
		onComplete: function(e){
			if(e.responseText=='OK'){
				alert('Save successfully');
				default_curtain_clicked();
			}else{
				alert(e.responseText);
			}
			$('btn_save_discount_table').disabled = false;
		}
	});
}
{/literal}
</script>

<div class=small style="position:absolute; right:10; text-align:right;"><a href="javascript:void(default_curtain_clicked())" accesskey="C"><img src=ui/closewin.png border=0 align=absmiddle></a><br><u>C</u>lose (Alt+C)</div>
<h2>{$branch_info.code}<br />{$branch_info.description}</h2>

<form name="form_discount_table" id="form_discount_table" onSubmit="return false;">
<input type="hidden" name="branch_id" value="{$branch_info.id}" />
<table cellpadding="5" width="100%">
<tr height=24 bgcolor=#ffee99>
	<th>Discount Type</th><th>Discount %</th><th>Discount Type</th><th>Discount %</th>
</tr>
{assign var=ti value=0}
{foreach from=$trade_info key=tid item=r name=ti}
	{if $ti%2 eq 0}<tr>{/if}
		<td><b>{$r.code}</b></td>
		<td align="center"><input type="text" name="trade_discount[{$tid}]" size="10" style="text-align:right;" onChange="check_value(this);" value="{$r.value|ifzero:''}" /> %</td>
	{if $ti%2 eq 1}</tr>{/if}
	{assign var=ti value=$ti+1}
{/foreach}
</table>
<p style="text-align:center;">
	<input type="button" value="Save" id="btn_save_discount_table" onClick="save_discount_table();" />
	<input type="button" value="Close" onClick="close_discount_table();" />
</p>
</form>
