{section name=i loop=$items}
L
D11
1F3202600200015{$items[i].sku_item_code}
202200000550002 ARMS
202300000550120{$items[i].artno}
122100000010008 RM {$items[i].selling_price|number_format:2}
191100100780120{$items[i].default_trade_discount_code}
191100100780002{$smarty.now|date_format:"%mA%y"} 
191100100650005{$items[i].receipt_description|substr:0:25}
191100100560005{$items[i].receipt_description|substr:25}
1F3202600200155{$items[i].sku_item_code}
202200000550145 ARMS
202300000550265{$items[i].artno}
122100100010147 RM {$items[i].selling_price|number_format:2}
191100100780260{$items[i].default_trade_discount_code}
191100100780147{$smarty.now|date_format:"%mA%y"} 
191100100650150{$items[i].receipt_description|substr:0:25}
191100100560150{$items[i].receipt_description|substr:25}
Q{$items[i].qty/2|string_format:"%04d"}
E
{/section}
