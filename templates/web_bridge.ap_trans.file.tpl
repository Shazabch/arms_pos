{*
6/22/2011 4:56:20 PM Andy
- add double quotation to "vendor description" and "Trade Purchase".

6/24/2011 11:19:18 AM Andy
- Add double quotation to "vendor code", "document no" and "posting account".

7/6/2011 6:18:58 PM Andy
- Fix invoice amount bugs.

2/7/2012 10:53:07 AM Andy
- Change output AP description to "Purchase".

3/6/2012 2:39:44 PM Andy
- Change AP/AR/CC Trans to use Posting Account Code and Project Code from Settings Module.
*}
{foreach from=$data item=r}{strip}
	{if $export_format eq 'csv1'}
		"{$r.vendorcode}","{$r.vendor_desc}","{$r.doc_no}","{$r.export_type}","{$r.rcv_date}","{$r.term}","{$r.export_desc}","{$r.amount|number_format:2}","{$r.posting_account}","{$r.bcode}"
	{elseif $export_format eq 'txt1_invoice' or $export_format eq 'txt1_dn'}
		"{$r.vendorcode|default:'----'}";"{$r.doc_no|default:'----'}";{$r.rcv_date|date_format:'%d/%m/%y'};{$r.rcv_date|date_format:'%d/%m/%y'};{$r.term|default:'----'};"Purchase";----;----;"{$r.project_code|default:'----'}";1;{$r.amount|round2};F;"{$r.doc_no|default:'----'}";"{$r.posting_account|default:'----'}";"Trade Purchase";----;{$r.amount|round2}
	{/if}
	{/strip}
{/foreach}