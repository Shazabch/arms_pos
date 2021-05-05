{foreach from=$data item=r}
"{$r.code}","{$r.period}","{$r.ci_date|date_format:'%d/%m/%Y'}","{$r.ci_no}","","SALES","","{$r.total_amount|round2}","0"
"5000/001","{$r.period}","{$r.ci_date|date_format:'%d/%m/%Y'}","{$r.ci_no}","","{$r.description}","","0","{$r.total_amount|round2}"
{/foreach}
