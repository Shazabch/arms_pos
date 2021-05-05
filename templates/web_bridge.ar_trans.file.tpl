{*
2/7/2012 10:53:29 AM Andy
- Change output AR description to "Sales".
- Add output column DocNo and Terms.
- Fix checking DO Type bugs.

3/6/2012 2:46:36 PM Andy
- Change AP/AR/CC Trans to use Posting Account Code and Project Code from Settings Module.
*}
{foreach from=$data.items item=r}{strip}
	{if $export_format eq 'txt1'}
		{if $r.do_type eq 'transfer'}
			"{$r.bcode|default:'----'}";
		{elseif $r.do_type eq 'open'}
			"{$r.open_info.name|default:'----'}";
		{else}
			"{$r.debtor_code|default:'----'}";
		{/if}
	
		"{$r.inv_no|default:'----'}";
	
		{$r.do_date|date_format:'%d/%m/%y'};{$r.do_date|date_format:'%d/%m/%y'};----;
	
		"Sales";"{$r.project_code|default:'----'}";----;----;1;{$r.total_inv_amt|round2};F;"{$r.inv_no|default:'----'}";"{$r.posting_account|default:'----'}";
	
		{if $r.do_type eq 'transfer'}
			"Transfer";
		{elseif $r.do_type eq 'open'}
			"Cash Sales";
		{else}
			"Credit Sales";
		{/if}
		
		----;{$r.total_inv_amt|round2}
	{/if}
	{/strip}
{/foreach}
