{*
2/7/2012 10:55:02 AM Andy
- Reconstruct CC Trans output format.

2/29/2012 2:32:01 PM Andy
- Add Download Format 2 for GL Journal Entry

3/6/2012 3:59:55 PM Andy
- Change AP/AR/CC Trans to use Posting Account Code and Project Code from Settings Module.

3/19/2012 2:37:47 PM Andy
- Add project code at "Details" (txt1).
- Change UOM to "UNIT" (txt1).
- Change Terms to 30 (txt1).

4/4/2012 10:39:17 AM Andy
- Add new export format 3 (Customer Invoice).
*}
{foreach from=$data.items item=r name=f}
{if $export_format eq 'txt1'}{strip}
	MASTER;"{$r.docno|default:'----'}";;{$r.date|date_format:'%d/%m/%y'};{$smarty.now|date_format:'%d/%m/%y'};"{$r.customer_code|default:'----'}";"Point of Sales";;;;;;;;----;----;"{$project_code|default:'----'}";30;1;;F;{$r.amt|round2};;;;;;;;;;;;;;;;T;{$r.amt|round2};"{$r.acc_code|default:'----'}";;0.00;{$r.amt|round2};
	{/strip}
{strip}
	DETAIL;"{$r.docno|default:'----'}";{$smarty.foreach.f.iteration};;----;"{$project_code|default:'----'}";;;;1;"UNIT";1;{$r.amt|round2};{$r.date|date_format:'%d/%m/%y'};;;0;{$r.amt|round2};T;"{$r.acc_code|default:'----'}";T;;;
	{/strip}
{elseif $export_format eq 'txt2'}
MASTER;"{$r.docno|default:'----'}";{$r.date|date_format:'%d/%m/%y'};{$smarty.now|date_format:'%d/%m/%y'};"POS - {$r.payment_type}";F;
DETAIL;"{$r.docno|default:'----'}";"{$r.customer_code|default:'----'}";"POS - {$r.payment_type}";;"{$project_code|default:'----'}";{$r.amt|round2};{$r.amt|round2};0;0;
DETAIL;"{$r.docno|default:'----'}";"{$r.acc_code|default:'----'}";"POS - {$r.payment_type}";;"{$project_code|default:'----'}";0;0;{$r.amt|round2};{$r.amt|round2};
{elseif $export_format eq 'txt3'}
"{$r.acc_code|default:'----'}";"{$r.docno|default:'----'}";{$r.date|date_format:'%d/%m/%y'};{$smarty.now|date_format:'%d/%m/%y'};"0";"POS - {$r.payment_type}";;;"{$project_code|default:'----'}";1;{$r.amt|round2};F;"{$r.docno|default:'----'}";"{$r.customer_code|default:'----'}";"POS - {$r.payment_type}";"{$project_code|default:'----'}";{$r.amt|round2};
{/if}
{/foreach}
