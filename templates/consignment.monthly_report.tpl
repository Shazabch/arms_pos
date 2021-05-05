{*
7/10/2009 4:51:23 PM Andy
- add closing stock

2/8/2010 3:16:38 PM Andy
- Add consignment over invoice column in monthly report

3/12/2010 3:44:28 PM Andy
- Fix year selection will not retrain after showing report

4/30/2010 11:53:21 AM Andy
- monthly report able to key in "-", no need press 2 times

6/2/2010 2:52:48 PM Andy
- Add print draft monthly report.

6/8/2010 10:11:36 AM Andy
- CN/DN Swap

7/21/2010 11:27:23 AM Andy
- Fix a bugs Monthly Report last row data cannot save problems.

7/28/2010 1:38:35 PM Andy
- Add delete function to consignment monthly report (prompt to enter reason before delete).

10/12/2010 10:59:09 AM Andy
- Change to use lighter color for those column which allow user to keyin data.

1/26/2011 2:56:35 PM Andy
- Add search sku and directly goto the page.

9/19/2011 10:57:10 AM Andy
- Fix FF6 focus and blur event problem.

11/22/2011 11:07:41 AM Andy
- Add Qty In, Qty Out, Posted Adj and Adj Amount.
- Add "Stock Closing" to include adjustment, lost and over.
- Fix press enter to search sku.
- Show price type total at top panel list.

1/11/2012 5:24:43 PM Justin
- Added to show region/exchange rate info when branch contain region.
- Added to pick up exchange rate and update into database when user click on Save.
- Removed all the "RM" and place currency type under header of table and it is dynamic base on branch's region.

1/18/2012 10:19:43 AM Justin
- Fixed the bugs that return error while doing save.

4/2/2012 12:23:19 PM Andy
- Add column "Open Qty" and "Open Amt" at summary panel.

4/24/2012 4:12:31 PM Alex
- fix new firefox check script problem => do_delete()

30/8/2012 4:37:42PM Drkoay
- Add 4 customize column. if $config.monthly_report_additional_qty_sold=1
- Update function do_edit(),do_select(),save(),do_save(),do_confirm();

5/8/2015 11:15 AM Andy
- Enhanced to show Consignment Invoice ID for Lost.

4/19/2017 9:37 AM Khausalya
- Enhanced changes from RM to use config setting.

1/31/2019 9:29 AM Andy
- Change event onkeypress to onkeydown due to firefox latest version no longer support the arrow key in onkeypress().
- Enhanced to check numpad 0 - 9 .

2/1/2019 11:46 AM Andy
- Fixed character "-" cannot be detect by latest firefox version.
*}

{include file=header.tpl}

{*
{if $export_info or $smarty.request.read_only or !$sessioninfo.privilege.CON_MONTHLY_REPORT}
	{assign var=read_only value=1}
{else}
	{assign var=read_only value=0}
{/if}
*}
{literal}
<style>
.keyin{
{/literal}
{if !$read_only}background-color: #ffc;{/if}
{literal}
	text-align:right;
}
span.link{
	cursor:pointer;
	color: #CE0000;
}
span.active{
	cursor:default;
	color: black;
}
.total_col_fixed{
	background-color:#ddd;
}
.panel_row_price_type{
	background-color:#fee;
}
</style>
{/literal}

<script>
var year = '{$smarty.request.year}';
var month = '{$smarty.request.month}';
var phpself = '{$smarty.server.PHP_SELF}';
var branch_id = '{$smarty.request.branch}';
var read_only = '{$read_only}';
var page_price_type = [''];	// assign empty for page zero

// assign page price type
{foreach from=$page_data item=pt}	
	page_price_type.push('{$pt.discount_code}');
{/foreach}
		
{literal}
document.onkeydown = check_perform;
document.onmouseup = check_click;
var last_obj;
var current_table;
var is_escape = false;
var is_column = false;
var last_info = {type:'' , value:''};
var tmp_char = '';
var current_page_num = 1;

function do_edit(){
	var obj = last_obj
	
	if(is_column){
		$('edit_text').value = obj.innerHTML;
	}
	else{
		$('edit_text').value = float(obj.innerHTML.replace(/^&nbsp;/,''));
	}
	Position.clone(obj, $('edit_popup'));
	Position.clone(obj, $('edit_text'));
	Element.show('edit_popup');
	$('edit_text').select();
	$('edit_text').focus();
}

function do_edit2(){
	obj = last_obj
	//$('edit_text').value = float(obj.innerHTML.replace(/^&nbsp;/,''));
	Position.clone(obj, $('edit_popup'));
	Position.clone(obj, $('edit_text'));
	Element.show('edit_popup');
	//$('edit_text').select();
	$('edit_text').focus()
	
	if(tmp_char){
		$('edit_text').value = tmp_char;
		tmp_char = '';
	}//else	$('edit_text').select();
}

function do_select(obj,column){
	is_escape = false;
	last_obj = obj;
	if(column){
		is_column=true;
		var qty = obj.innerHTML;		
	}
	else{
		is_column=false;		
		var qty = float(obj.innerHTML.replace(/^&nbsp;/,''));		
		if(qty==0)  qty='';		
	}
	
	$('edit_text').value = qty;
	$('selected_popup').update(qty);
	Position.clone(obj, $('selected_popup'));
	Element.show('selected_popup');
	//$('edit_text').focus();
}

function start_edit(){
    Element.hide($('selected_popup'));
    do_edit();
}

function start_edit2(){
    Element.hide($('selected_popup'));
    do_edit2();
}

function check_perform(event){
	//console.log('check_perform: '+event.keyCode+', w: '+event.which+', character: '+event.key);
	
	if($('selected_popup').style.display==''){
        var kc = event.keyCode;
        //var which = event.which;
		var k = event.key;
		
		//var str = String.fromCharCode(which);

		switch(kc)
		{
			case 27:    // escape
	            $('selected_popup').style.display='none';
	     		break;
			case 13:    // enter
			    start_edit();
			    break;
			case 38: 	// up
				up_cell();
			    break;
			case 40:    // down
				down_cell();
			    break;
			case 37:    // left
				previous_cell();
			    break;
			case 39:    // right
				next_cell();
			    break;
	   		default:    // other
	   		    //if((which >=48 && which <= 57) || which ==45){  // 0 - 9, "-"
				if(k == '-' || (k >= 0 && k<=9)){	// 0 - 9, "-"
                    //$('edit_text').value = str;
                    tmp_char = k;
				}	
                start_edit2();
	     		break;
		}
		return false;
	}
}

function next_cell(){
	var obj = $(last_obj).next("td.keyin");
	if(obj!=undefined)	do_select(obj);
}

function previous_cell(){
    var obj = $(last_obj).previous("td.keyin");
	if(obj!=undefined)	do_select(obj);
}

function up_cell(){
	var data = $(last_obj).title.split(',');
	var x = data[0];
	var y = data[1];
	var new_y = int(y)-1;
	//alert(x+','+y);
	
	if(new_y>0){
		//var cname = 'row_'+x+'_'+new_y;
		//var cell = $(current_table.getElementsByClassName(cname));
		//var cell = $($('div_content').getElementsByClassName(cname));

		var cell = $($('div_content').getElementsBySelector('td[title="'+x+','+new_y+'"]'));
        do_select(cell[0]);
	}
	
	// if the page offset, scroll to focus it
	var page_y = Position.page($('selected_popup'))[1];
	if(page_y<0)    $($('selected_popup')).scrollTo();
}

function down_cell(){
    var data = $(last_obj).title.split(',');
	var x = data[0];
	var y = data[1];
	var new_y = int(y)+1;
	if(new_y>0){
		//var cname = 'row_'+x+'_'+new_y;
		//var cell = $(current_table.getElementsByClassName(cname));
		//var cell = $($('div_content').getElementsByClassName(cname));
		var cell = $($('div_content').getElementsBySelector('td[title="'+x+','+new_y+'"]'));
		if(cell.length>0)   do_select(cell[0]);
	}else{
        //last_obj = undefined;
        return false;
	}

    // if the page offset, scroll to focus it
	var page_y = Position.page($('selected_popup'))[1];
	var height = $('selected_popup').getHeight();
	var dif = int(window.innerHeight)-int(page_y);
	
 	if(dif<height){
 	    var temp = int(height)-int(dif);
        document.body.scrollTop = document.body.scrollTop+temp;
	}    
	return false;
}

function save(){
	Element.hide('edit_popup');
    
	if(is_escape){
		if(last_obj)	do_select(last_obj);
		return;
	} 
	
	if(is_column){
		var obj_id=last_obj.id
		is_column=false;
		
		if(last_obj.innerHTML!=$('edit_text').value){
			
			last_obj.innerHTML = 'Saving..';
			
			var newp = last_obj;
			new Ajax.Updater(newp, phpself,
			{
				method: 'post',
				parameters:
				{
					a: 'save_column',
					branch_id: branch_id,
					year: year,
					month: month,
					value: $('edit_text').value,
					key:obj_id,
				},
				onComplete:function(){
					$('column_'+obj_id).update($('edit_text').value);
					//$('report_table').getElement('#'+obj_id).update($('edit_text').value);
					
					remove_focus();
					//do_select(last_obj);
					down_cell();
				}
			});
			
			
			
			
		}
		else{
			remove_focus();
			do_select(last_obj);
			down_cell();
		}
	}
	else{		
		if(float(last_obj.innerHTML)!=float($('edit_text').value)){
			var data = last_obj.id.split(',');
			//var day = data[0];
			var type = data[0];
			var sku_item_id = data[2];
			
			last_info.value = float(last_obj.innerHTML);
			last_info.type = type;
	
			last_obj.innerHTML = 'Saving..';
			var newp = last_obj;
			new Ajax.Updater(newp, phpself,
			{
				method: 'post',
				parameters:
				{
					a: 'save',
					branch_id: branch_id,
					sku_item_id: sku_item_id,
					year: year,
					month: month,
					value: $('edit_text').value,
					type: type
				},
				onComplete:function(){
					if(type=='qty'||type=='qty1'||type=='qty2'||type=='qty3'||type=='qty4'||type=='price'||type=='adj'||type=='lost'||type=='over')	re_calc(newp,sku_item_id);
					
					remove_focus();
					do_select(last_obj);
					down_cell();
				}
			});
		}else{
			remove_focus();
			do_select(last_obj);
			down_cell();
		}
	}
}

function checkKey(event){
	
    if (event == undefined) event = window.event;

	if(event.keyCode==13){  // enter
		save();
	}else if(event.keyCode==27){    // escape
	    remove_focus();
		//do_select(last_obj);
	}
	//alert(event.keyCode)
	event.stopPropagation();
}

function remove_focus(){
    is_escape = true;
    document.f_mr.h.focus();
    $('edit_text').blur();	// fix bugs at FF6, cannot auto trigger blur event
}

function trim(str){
 return str.replace(/(^\s*)|(\s*$)/g, "");
}

function addCommas(nStr)
{
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
}

function re_calc(obj,sku_item_id){
  	total_qty = int($('qty,keyin,'+sku_item_id).innerHTML);	
	
	total_qty+=($('qty1,keyin,'+sku_item_id)!=null)?int($('qty1,keyin,'+sku_item_id).innerHTML):0;
	total_qty+=($('qty2,keyin,'+sku_item_id)!=null)?int($('qty2,keyin,'+sku_item_id).innerHTML):0;
	total_qty+=($('qty3,keyin,'+sku_item_id)!=null)?int($('qty3,keyin,'+sku_item_id).innerHTML):0;
	total_qty+=($('qty4,keyin,'+sku_item_id)!=null)?int($('qty4,keyin,'+sku_item_id).innerHTML):0;
	
	if(total_qty==0){
        $('qty,keyin,'+sku_item_id).update('');		
		
		if($('qty1,keyin,'+sku_item_id)!=null) $('qty1,keyin,'+sku_item_id).update('');
		if($('qty2,keyin,'+sku_item_id)!=null) $('qty2,keyin,'+sku_item_id).update('');
		if($('qty3,keyin,'+sku_item_id)!=null) $('qty3,keyin,'+sku_item_id).update('');
		if($('qty4,keyin,'+sku_item_id)!=null) $('qty4,keyin,'+sku_item_id).update('');
		
        $('price,total,'+sku_item_id).update('0.00');
	}    
	else{
        var price = float($('price,keyin,'+sku_item_id).innerHTML.replace(/^&nbsp;/,''));
        var total_price = price*total_qty;
        $('price,total,'+sku_item_id).update(addCommas(total_price.toFixed(2)));
	}
	
	// report info  - page total and grand total
	var type = last_info.type;
	var value = last_info.value;
	var old_qty = 0;
	
	var old_qty1 = 0;
	var old_qty2 = 0;
	var old_qty3 = 0;
	var old_qty4 = 0;
	
	var old_amt = 0;
	
	var old_amt1 = 0;
	var old_amt2 = 0;
	var old_amt3 = 0;
	var old_amt4 = 0;
	
	var old_lost_amt = 0;
	var old_lost = 0;
	var old_over = 0;
	var old_over_amt = 0;
	var price = 0;
	var old_adj = 0;
	var old_adj_amt = 0;
	var open_bal = int($('open_bal,inp,'+sku_item_id).value);
	var opening_qty = int($('opening_qty,inp,'+sku_item_id).value);
	
	// price type
	price_type = page_price_type[current_page_num];
	
	////////////////////////////// new method //////////////////////////////
	// get all old data	
	var new_qty = old_qty = int($('qty,keyin,'+sku_item_id).innerHTML.replace(/^&nbsp;/,''));	
	
	var new_qty1 = old_qty1 = ($('qty1,keyin,'+sku_item_id)!=null)? int($('qty1,keyin,'+sku_item_id).innerHTML.replace(/^&nbsp;/,'')):0;
	var new_qty2 = old_qty2 = ($('qty2,keyin,'+sku_item_id)!=null)? int($('qty2,keyin,'+sku_item_id).innerHTML.replace(/^&nbsp;/,'')):0;
	var new_qty3 = old_qty3 = ($('qty3,keyin,'+sku_item_id)!=null)? int($('qty3,keyin,'+sku_item_id).innerHTML.replace(/^&nbsp;/,'')):0;
	var new_qty4 = old_qty4 = ($('qty4,keyin,'+sku_item_id)!=null)? int($('qty4,keyin,'+sku_item_id).innerHTML.replace(/^&nbsp;/,'')):0;
	
	var new_lost = old_lost = int($('lost,keyin,'+sku_item_id).innerHTML.replace(/^&nbsp;/,''));
	var new_over = old_over = int($('over,keyin,'+sku_item_id).innerHTML.replace(/^&nbsp;/,''));
	var new_adj = old_adj = int($('adj,keyin,'+sku_item_id).innerHTML.replace(/^&nbsp;/,''));
	var new_price = old_price = float($('price,keyin,'+sku_item_id).innerHTML.replace(/^&nbsp;/,''));
	
	switch(type){
		case 'qty':
			old_qty = value;
			break;
		case 'qty1':
			old_qty1 = value;
			break;
		case 'qty2':
			old_qty2 = value;
			break;
		case 'qty3':
			old_qty3 = value;
			break;
		case 'qty4':
			old_qty4 = value;
			break;
		case 'price':
			old_price = value;
			break;
		case 'adj':
			old_adj = value;
			break;
		case 'lost':
			old_lost = value;
			break;
		case 'over':
			old_over = value;
			break;
		default:
			return;
	}		
	
	// old row info
	old_amt = old_qty*old_price;
	
	old_amt1 = old_qty1*old_price;
	old_amt2 = old_qty2*old_price;
	old_amt3 = old_qty3*old_price;
	old_amt4 = old_qty4*old_price;
	
	old_lost_amt = old_lost*old_price;
	old_over_amt = old_over*old_price;
	old_adj_amt = old_adj*old_price;
	var old_stock_closing = (open_bal-old_qty-old_qty1-old_qty2-old_qty3-old_qty4+old_adj-old_lost+old_over)*old_price;
	var old_opening_amt = opening_qty*old_price;
	
	// new row info
	var new_amt = new_qty*new_price;
	
	var new_amt1 = new_qty1*new_price;
	var new_amt2 = new_qty2*new_price;
	var new_amt3 = new_qty3*new_price;
	var new_amt4 = new_qty4*new_price;
	
	var new_lost_amt = new_lost*new_price;
	var new_over_amt = new_over*new_price;
	var new_adj_amt = new_adj*new_price;
	var new_stock_closing = (open_bal-new_qty-new_qty1-new_qty2-new_qty3-new_qty4+new_adj-new_lost+new_over)*new_price;
	var new_opening_amt = opening_qty*new_price;
	
	// calculate page total
	// get old info
	var page_qty = int($('span,qty,page_total').innerHTML);
	
	var page_qty1 = ($('span,qty1,page_total')!=null)?int($('span,qty1,page_total').innerHTML):0;
	var page_qty2 = ($('span,qty2,page_total')!=null)?int($('span,qty2,page_total').innerHTML):0;
	var page_qty3 = ($('span,qty3,page_total')!=null)?int($('span,qty3,page_total').innerHTML):0;
	var page_qty4 = ($('span,qty4,page_total')!=null)?int($('span,qty4,page_total').innerHTML):0;
	
	var page_amt = float($('span,amount,page_total').innerHTML);
	var page_adj = int($('span,adj,page_total').innerHTML);
    var page_adj_amt = float($('span,adj_amount,page_total').innerHTML);
    var page_lost = int($('span,lost,page_total').innerHTML);
	var page_lost_amt = float($('span,lost_amount,page_total').innerHTML);
	var page_over = int($('span,over,page_total').innerHTML);
	var page_over_amt = float($('span,over_amount,page_total').innerHTML);
	var page_stock_closing = float($('span,stock_closing,page_total').innerHTML);
	var page_opening_amt = float($('span,opening_amt,page_total').innerHTML);
	
	// calculate new page info
	page_qty = page_qty - old_qty + new_qty;
	
	page_qty1 = page_qty1 - old_qty1 + new_qty1;
	page_qty2 = page_qty2 - old_qty2 + new_qty2;
	page_qty3 = page_qty3 - old_qty3 + new_qty3;
	page_qty4 = page_qty4 - old_qty4 + new_qty4;
	
	page_amt = page_amt - old_amt + new_amt - old_amt1 + new_amt1 - old_amt2 + new_amt2 - old_amt3 + new_amt3 - old_amt4 + new_amt4;
	page_adj = page_adj - old_adj + new_adj;
	page_adj_amt = page_adj_amt - old_adj_amt + new_adj_amt;
	page_lost = page_lost - old_lost + new_lost;
	page_lost_amt = page_lost_amt - old_lost_amt + new_lost_amt;
	page_over = page_over - old_over + new_over;
	page_over_amt = page_over_amt - old_over_amt + new_over_amt;
  	page_stock_closing = page_stock_closing - old_stock_closing + new_stock_closing;
    page_opening_amt = page_opening_amt - old_opening_amt + new_opening_amt;
    
    // update span for page total
  	$('span,qty,page_total').update(page_qty);
	
	if($('span,qty1,page_total')!=null) $('span,qty1,page_total').update(page_qty1);
	if($('span,qty2,page_total')!=null) $('span,qty2,page_total').update(page_qty2);
	if($('span,qty3,page_total')!=null) $('span,qty3,page_total').update(page_qty3);
	if($('span,qty4,page_total')!=null) $('span,qty4,page_total').update(page_qty4);
	
	$('span,amount,page_total').update(addCommas(page_amt.toFixed(2)));
	$('span,adj,page_total').update(addCommas(page_adj));
	$('span,adj_amount,page_total').update(addCommas(page_adj_amt.toFixed(2)));
	$('span,lost,page_total').update(addCommas(page_lost));
	$('span,lost_amount,page_total').update(addCommas(page_lost_amt.toFixed(2)));
	$('span,over,page_total').update(addCommas(page_over));
	$('span,over_amount,page_total').update(addCommas(page_over_amt.toFixed(2)));
	$('span,stock_closing,page_total').update(addCommas(page_stock_closing.toFixed(2)));
	$('span,opening_amt,page_total').update(addCommas(page_opening_amt.toFixed(2)));
	
	// get price type total
	var pt_qty = int($('span,qty,'+price_type).innerHTML);
	
	var pt_qty1 = ($('span,qty1,'+price_type)!=null)?int($('span,qty1,'+price_type).innerHTML):0;
	var pt_qty2 = ($('span,qty1,'+price_type)!=null)?int($('span,qty2,'+price_type).innerHTML):0;
	var pt_qty3 = ($('span,qty1,'+price_type)!=null)?int($('span,qty3,'+price_type).innerHTML):0;
	var pt_qty4 = ($('span,qty1,'+price_type)!=null)?int($('span,qty4,'+price_type).innerHTML):0;
	
	var pt_amt = float($('span,amount,'+price_type).innerHTML);
	var pt_adj = int($('span,adj,'+price_type).innerHTML);
    var pt_adj_amt = float($('span,adj_amount,'+price_type).innerHTML);
    var pt_lost = int($('span,lost,'+price_type).innerHTML);
	var pt_lost_amt = float($('span,lost_amount,'+price_type).innerHTML);
	var pt_over = int($('span,over,'+price_type).innerHTML);
	var pt_over_amt = float($('span,over_amount,'+price_type).innerHTML);
	var pt_stock_closing = float($('span,stock_closing,'+price_type).innerHTML);
	var pt_opening_amt = float($('span,opening_amt,'+price_type).innerHTML);
	
	// calculate new price type info
	pt_qty = pt_qty - old_qty + new_qty;
	
	pt_qty1 = pt_qty1 - old_qty1 + new_qty1;
	pt_qty2 = pt_qty2 - old_qty2 + new_qty2;
	pt_qty3 = pt_qty3 - old_qty3 + new_qty3;
	pt_qty4 = pt_qty4 - old_qty4 + new_qty4;
	
	pt_amt = pt_amt - old_amt + new_amt - old_amt1 + new_amt1 - old_amt2 + new_amt2 - old_amt3 + new_amt3 - old_amt4 + new_amt4;
	pt_adj = pt_adj - old_adj + new_adj;
	pt_adj_amt = pt_adj_amt - old_adj_amt + new_adj_amt;
	pt_lost = pt_lost - old_lost + new_lost;
	pt_lost_amt = pt_lost_amt - old_lost_amt + new_lost_amt;
	pt_over = pt_over - old_over + new_over;
	pt_over_amt = pt_over_amt - old_over_amt + new_over_amt;
  	pt_stock_closing = pt_stock_closing - old_stock_closing + new_stock_closing;
	pt_opening_amt = pt_opening_amt - old_opening_amt + new_opening_amt;
	
	// update span for grand total
	$('span,qty,'+price_type).update(pt_qty);
	
	if($('span,qty1,'+price_type)!=null) $('span,qty1,'+price_type).update(pt_qty1);
	if($('span,qty2,'+price_type)!=null) $('span,qty2,'+price_type).update(pt_qty2);
	if($('span,qty3,'+price_type)!=null) $('span,qty3,'+price_type).update(pt_qty3);
	if($('span,qty4,'+price_type)!=null) $('span,qty4,'+price_type).update(pt_qty4);
	
	$('span,amount,'+price_type).update(addCommas(pt_amt.toFixed(2)));
	$('span,adj,'+price_type).update(pt_adj);
	$('span,adj_amount,'+price_type).update(addCommas(pt_adj_amt.toFixed(2)));
	$('span,lost,'+price_type).update(pt_lost);
	$('span,lost_amount,'+price_type).update(addCommas(pt_lost_amt.toFixed(2)));
	$('span,over,'+price_type).update(pt_over);
	$('span,over_amount,'+price_type).update(addCommas(pt_over_amt.toFixed(2)));
	$('span,stock_closing,'+price_type).update(addCommas(pt_stock_closing.toFixed(2)));
	$('span,opening_amt,'+price_type).update(addCommas(pt_opening_amt.toFixed(2)));
	
	// grand total
	var grand_qty = int($('span,qty,grand_total').innerHTML);
	
	var grand_qty1 = ($('span,qty1,grand_total')!=null)?int($('span,qty1,grand_total').innerHTML):0;
	var grand_qty2 = ($('span,qty2,grand_total')!=null)?int($('span,qty2,grand_total').innerHTML):0;
	var grand_qty3 = ($('span,qty3,grand_total')!=null)?int($('span,qty3,grand_total').innerHTML):0;
	var grand_qty4 = ($('span,qty4,grand_total')!=null)?int($('span,qty4,grand_total').innerHTML):0;
	
	var grand_amt = float($('span,amount,grand_total').innerHTML);
	var grand_adj = int($('span,adj,grand_total').innerHTML);
	var grand_adj_amt = float($('span,adj_amount,grand_total').innerHTML);
	var grand_lost = int($('span,lost,grand_total').innerHTML);
	var grand_lost_amt = float($('span,lost_amount,grand_total').innerHTML);
	var grand_over = int($('span,over,grand_total').innerHTML);
	var grand_over_amt = float($('span,over_amount,grand_total').innerHTML);
	var grand_stock_closing = float($('span,stock_closing,grand_total').innerHTML);
	var grand_opening_amt = float($('span,opening_amt,grand_total').innerHTML);
	
	grand_qty = grand_qty - old_qty + new_qty;
	
	grand_qty1 = grand_qty1 - old_qty1 + new_qty1;
	grand_qty2 = grand_qty2 - old_qty2 + new_qty2;
	grand_qty3 = grand_qty3 - old_qty3 + new_qty3;
	grand_qty4 = grand_qty4 - old_qty4 + new_qty4;
	
	grand_amt = grand_amt - old_amt + new_amt - old_amt1 + new_amt1 - old_amt2 + new_amt2 - old_amt3 + new_amt3 - old_amt4 + new_amt4;
	grand_adj = grand_adj - old_adj + new_adj;
	grand_adj_amt = grand_adj_amt - old_adj_amt + new_adj_amt;
	grand_lost = grand_lost - old_lost + new_lost;
	grand_lost_amt = grand_lost_amt - old_lost_amt + new_lost_amt;
	grand_over = grand_over - old_over + new_over;
	grand_over_amt = grand_over_amt - old_over_amt + new_over_amt;
	grand_stock_closing = grand_stock_closing - old_stock_closing + new_stock_closing;
	grand_opening_amt = grand_opening_amt - old_opening_amt + new_opening_amt;
		
	// update span for grand total
	$('span,qty,grand_total').update(grand_qty);
	
	if($('span,qty1,grand_total')!=null) $('span,qty1,grand_total').update(grand_qty1);
	if($('span,qty2,grand_total')!=null) $('span,qty2,grand_total').update(grand_qty2);
	if($('span,qty3,grand_total')!=null) $('span,qty3,grand_total').update(grand_qty3);
	if($('span,qty4,grand_total')!=null) $('span,qty4,grand_total').update(grand_qty4);
	
	$('span,amount,grand_total').update(addCommas(grand_amt.toFixed(2)));
	$('span,adj,grand_total').update(grand_adj);
	$('span,adj_amount,grand_total').update(addCommas(grand_adj_amt.toFixed(2)));
	$('span,lost,grand_total').update(grand_lost);
	$('span,lost_amount,grand_total').update(addCommas(grand_lost_amt.toFixed(2)));
	$('span,over,grand_total').update(grand_over);
	$('span,over_amount,grand_total').update(addCommas(grand_over_amt.toFixed(2)));
	$('span,stock_closing,grand_total').update(addCommas(grand_stock_closing.toFixed(2)));
	$('span,opening_amt,grand_total').update(addCommas(grand_opening_amt.toFixed(2)));
}

function view_page(obj, highlight_sid){
	highlight_sid = int(highlight_sid);
	var page_num = obj.value
	if(!page_num)   return;
	$('div_content').update(_loading_);
	var params = {
			page_num: page_num,
			year: year,
			month: month,
			branch: branch_id,
			read_only: read_only,
			highlight_sid: highlight_sid
		};
		
	new Ajax.Updater('div_content',phpself+'?a=ajax_load_table',{
		parameters: params,
		method: 'get',
		evalScripts: true,
		onComplete: function(){
			current_page_num = page_num;
		}
	});
}

function check_click(){
    if(last_obj!=undefined){
		Element.hide($('selected_popup'));
	}
}

function do_save(obj){
	
	var qtys=['qty1','qty2','qty3','qty4'];
	
	for(i in qtys){	
		if($('span,'+qtys[i]+',grand_total')!=null){
			var v=int($('span,'+qtys[i]+',grand_total').innerHTML);
			if( v>0 && $('column_'+qtys[i]+'_sold').innerHTML=="" ){
				alert('Please enter the title for the discount column');
				do_select($(qtys[i]+'_sold'),true);
				return false;
			}
		}
	}
	
	if(!confirm("Click OK to save."))    return false;
	obj.update("Saving...");
	obj.disabled = true;
	var prms = "&branch_id="+branch_id+"&year="+year+"&month="+month;

	if($("inp_exchange_rate") != undefined) prms += "&exchange_rate="+$("inp_exchange_rate").value;
	
	new Ajax.Request(phpself+"?a=real_save"+prms,{
		method: 'get',
		onComplete: function(e){
		    if(e.responseText=='OK')    alert("Updated");
		    else    alert("Updated Failed");
   			obj.update("Save");
			obj.disabled = false;
		}
	});
}

function do_confirm(ele){
	var qtys=['qty1','qty2','qty3','qty4'];
	
	for(i in qtys){	
		if($('span,'+qtys[i]+',grand_total')!=null){
			var v=int($('span,'+qtys[i]+',grand_total').innerHTML);
			if( v>0 && $('column_'+qtys[i]+'_sold').innerHTML=="" ){
				alert('Please enter the title for the discount column');
				do_select($(qtys[i]+'_sold'),true);
				return false;
			}
		}
	}
	
	if(!confirm('Click OK to confirm and submit to invoice')){
		return false;
	}
	
	if($('inp_exchange_rate') != undefined) document.invoice_f.exchange_rate.value = $('inp_exchange_rate').value;
	document.invoice_f.a.value = 'save_to_invoice';
	document.invoice_f.target = '';
    document.invoice_f.submit();
}

function re_call(ele){
    if(!confirm('Are you sure to call back all generated report?')){
		return false;
	}
	
	ele.update('Processing...');
	ele.disabled = true;
	
	document.invoice_f.a.value = 'invoice_recall';

	new Ajax.Request(phpself+'?'+$(document.invoice_f).serialize(),{
		method : 'post',
		onComplete: function(e){
			if(e.responseText=='OK'){
				document.f_mr.submit();
			}else{
                alert(e.responseText);
                ele.update('ReCall');
				ele.disabled = false;
			}
		}
	});
}

function print_draft(){
	document.fprint['branch_id'].value = document.f_mr['branch'].value;
	document.fprint['year'].value = document.f_mr['year'].value;
	document.fprint['month'].value = document.f_mr['month'].value;
	document.fprint.submit();
}

function do_delete(){
	document.f_mr['delete_reason'].value = '';
	var p = prompt('Enter reason to Delete :');
	if (p==null || p.trim()=='') return;
	document.f_mr['delete_reason'].value = p;
	if (confirm('Delete this Monthly Report?')){
		document.f_mr['a'].value = "delete_table";
		document.f_mr.submit();
	}
}

function add_autocomplete(){
	var sid = int($('sku_item_id').value);
	if(!sid){
		alert('Please search item first.');
		return;
	}
	
	// construct query string
	var query_str = $(document.f_mr).serialize()+'&sid='+sid+'&a=ajax_search_sku_page';
	// show loading icon
	$('span_sku_page_searching').show();
	// call ajax
	new Ajax.Request(phpself, {
		parameters:query_str,
		type: 'post',
		onComplete: function(e){
		    // hide loading icon
		    $('span_sku_page_searching').hide();
			var str = e.responseText.trim();
			try{
				eval("var json = "+str);    // convert to object
				if(json['ok']){ // success search
					if(json['page']){   // found item
					    var sel_page = $('sel_page');
					    if(sel_page){
                            sel_page.value = json['page'];
	                    	view_page(sel_page, sid);
						}else{
							alert('Module failed to change the page, please refresh your browser.');
						}
					}else{
						alert('Item cannot found in this monthly report.');
					}
				}else{
					alert('Module failed to search this item from monthly report.');
				}
			}catch(ex){
                alert(str);
			}
		}
	});
}
{/literal}
</script>

<form name="fprint" target="_blank">
	<input type="hidden" name="a" value="print_draft" />
	<input type="hidden" name="branch_id" />
	<input type="hidden" name="year" />
	<input type="hidden" name="month" />
</form>
<div id=edit_popup style="display:none;position:absolute;z-index:100;background:#fff;border:2px solid #000;margin:-2px 0 0 -2px;">
<input id="edit_text" size="5" onblur="save()" onKeyDown="checkKey(event)" style="text-align:right;" />
</div>
<div id="selected_popup" style="display:none;position:absolute;z-index:100;background:#fff;border:2px solid #0000f0;margin:-2px 0 0 -2px;text-align:right;vertical-align:middle;" ondblclick="start_edit();">
</div>

<h1>{$PAGE_TITLE}</h1>

<form method="post" name="invoice_f" style="display:none;">
<input type="hidden" name="a" value="save_to_invoice" />
<input type="hidden" name="branch_id" value="{$smarty.request.branch}" />
<input type="hidden" name="year" value="{$smarty.request.year}" />
<input type="hidden" name="month" value="{$smarty.request.month}" />
<input type="hidden" name="exchange_rate" value="1" />
</form>

<iframe id="if_inv" name="if_inv" style="display:none;"></iframe>
{if $err}

{/if}

{if !$table} -- No Data --
{else}
	{if $read_only && $form.status eq 1}
	    <div style="border:1px solid black;padding:5px;background:#f7f7f7;">
		The Report already export to following:<br />
		{if $export_info.invoice_list}<b>Consignment Invoice ID:</b>
			{foreach from=$export_info.invoice_list item=cid name=f}
			    <a href="consignment_invoice.php?a=open&id={$cid}&branch_id={$sessioninfo.branch_id}" target="_blank">
				{$cid}
				</a>
				{if !$smarty.foreach.f.last}, {/if}
			{/foreach}
			<br />
		{/if}
		{if $export_info.inv_lost_list}<b>Consignment Invoice ID (Lost):</b>
			{foreach from=$export_info.inv_lost_list item=cid name=f}
			    <a href="consignment_invoice.php?a=open&id={$cid}&branch_id={$sessioninfo.branch_id}" target="_blank">
				{$cid}
				</a>
				{if !$smarty.foreach.f.last}, {/if}
			{/foreach}
			<br />
		{/if}
		{if $export_info.lost_list}<b>Debit Note ID:</b>
			{foreach from=$export_info.lost_list item=cid name=f}
			    <a href="consignment.debit_note.php?a=open&id={$cid}&branch_id={$sessioninfo.branch_id}" target="_blank">
				{$cid}
				</a>
				{if !$smarty.foreach.f.last}, {/if}
			{/foreach}
			<br />
		{/if}
		{if $export_info.over_list}<b>Credit Note ID:</b>
			{foreach from=$export_info.over_list item=cid name=f}
			    <a href="consignment.credit_note.php?a=open&id={$cid}&branch_id={$sessioninfo.branch_id}" target="_blank">
				{$cid}
				</a>
				{if !$smarty.foreach.f.last}, {/if}
			{/foreach}
			<br />
		{/if}
		{if $export_info.adj_list}<b>Adjustment ID:</b>
			{foreach from=$export_info.adj_list item=cid name=f}
			    <a href="adjustment.php?a=open&id={$cid}&branch_id={$smarty.request.branch}" target="_blank">
				{$cid}
				</a>
				{if !$smarty.foreach.f.last}, {/if}
			{/foreach}
			<br />
		{/if}
		</div>
	{/if}
	
<h3>
Branch: {$branches[$smarty.request.branch].code} - {$branches[$smarty.request.branch].description}
({$months[$smarty.request.month]} {$smarty.request.year})
</h3>

<form method="post" class="form" name="f_mr" onSubmit="add_autocomplete();return false;">
	<input type="hidden" name="h" value="">
	<input type="hidden" name="a" value="load_table">
	<input type="hidden" name="month" value="{$smarty.request.month}" />
	<input type="hidden" name="year" value="{$smarty.request.year}" />
	<input type="hidden" name="branch" value="{$smarty.request.branch}" />
	<input type="hidden" name="delete_reason" />

	{include file='sku_items_autocomplete.tpl' parent_form='document.f_mr' _add_value='Goto page'}
	<span id="span_sku_page_searching" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Searching...</span>
</form>

<!-- got use currency -->
<div style="float:left;">
{if $currency_info}
	<table class="report_table">
		<tr class="header">
			<th colspan="2">Foreign Currency Info</th>
		</tr>
		<tr>
			<td><b>Region / Currency</b></td>
			<td>{$to_branch.region} / {$currency_info.currency}</td>
		</tr>
		<tr>
			<td><b>Exchange Rate</b></td>
			<td>
				{if $read_only}
					{$form.exchange_rate|default:$currency_info.exchange_rate}
				{else}
					<input type="text" id="inp_exchange_rate" value="{$form.exchange_rate|default:$currency_info.exchange_rate}" size="15" onchange="this.value=float(this.value);" />
				{/if}
			</td>
		</tr>
	</table>
{/if}

{if $page_total>1}
<h3>Page
	{assign var=last_discount_code value='EMPTY'}
		<select id="sel_page" onChange="view_page(this);">
			{section loop=$page_total name=pn}
			    {if $last_discount_code ne $page_data[$smarty.section.pn.iteration].discount_code}
			        {if $last_discount_code ne 'EMPTY'}</optgroup>{/if}
			    	<optgroup label="{$page_data[$smarty.section.pn.iteration].discount_code|default:'NONE'}">
			        {assign var=last_discount_code value=$page_data[$smarty.section.pn.iteration].discount_code}
			    {/if}
			    <option value="{$smarty.section.pn.iteration}">
			    {$smarty.section.pn.iteration}
			    </option>
			{/section}
		</select>
</h3>
{/if}
</div>

<div style="float:right;">
	{if $config.monthly_report_additional_qty_sold}
		{assign var="rowspan" value="2"}
		{assign var="colspan" value="5"}
	{/if}
	
	<table class="report_table">
	    <tr class="header">
	        <th rowspan="{$rowspan|default:1}">&nbsp;</th>
	        <th rowspan="{$rowspan|default:1}">Open Qty</th>
	        <th rowspan="{$rowspan|default:1}">Open Amt</th>
	        <th rowspan="{$rowspan|default:1}">Qty In</th>
	        <th rowspan="{$rowspan|default:1}">Qty Out</th>
	        <th rowspan="{$rowspan|default:1}">Posted Adj</th>
	        <th rowspan="{$rowspan|default:1}">Adj</th>
	        <th rowspan="{$rowspan|default:1}">Adj Amt<br />({$currency_info.currency|default:$config.arms_currency.symbol})</th>
	        <th rowspan="{$rowspan|default:1}">Lost</th>
	        <th rowspan="{$rowspan|default:1}">Lost Amt<br />({$currency_info.currency|default:$config.arms_currency.symbol})</th>
	        <th rowspan="{$rowspan|default:1}">Over</th>
	        <th rowspan="{$rowspan|default:1}">Over Amt<br />({$currency_info.currency|default:$config.arms_currency.symbol})</th>
	        <th colspan="{$colspan|default:1}">Sold Qty</th>
	        <th rowspan="{$rowspan|default:1}">Sold Amt</th>
	        <th rowspan="{$rowspan|default:1}">Stock<br />Closing ({$currency_info.currency|default:$config.arms_currency.symbol})</th>
	    </tr>
		{if $config.monthly_report_additional_qty_sold}
		<tr class="header">
			<th width="80">Default</th>
			<th width="80" id="column_qty1_sold">{$additional_column.qty1_sold}</th>
			<th width="80" id="column_qty2_sold">{$additional_column.qty2_sold}</th>
			<th width="80" id="column_qty3_sold">{$additional_column.qty3_sold}</th>
			<th width="80" id="column_qty4_sold">{$additional_column.qty4_sold}</th>
		</tr>
		{/if}
	    {foreach from=$panel_row_list key=t item=r}
	    	<tr class="r panel_row_{$r.type}" style="{if $t eq 'page_total'}border-top:3px solid black;{/if}">
	    		<td>{$r.label}</td>
	    		<td class="total_col_fixed"><span id="span,opening_qty,{$t}">{$report_info.$t.opening_qty|number_format}</span></td>
	    		<td><span id="span,opening_amt,{$t}">{$report_info.$t.opening_amt|number_format:2}</span></td>
	    		<td class="total_col_fixed"><span id="span,grn,{$t}">{$report_info.$t.grn|number_format}</span></td>
		        <td class="total_col_fixed"><span id="span,qty_out,{$t}">{$report_info.$t.qty_out|number_format}</span></td>
		        <td class="total_col_fixed"><span id="span,adj2,{$t}">{$report_info.$t.adj2|number_format}</span></td>
		        <td><span id="span,adj,{$t}">{$report_info.$t.adj|number_format}</span></td>
		        <td><span id="span,adj_amount,{$t}">{$report_info.$t.adj_amt|number_format:2}</span></td>
		        <td><span id="span,lost,{$t}">{$report_info.$t.lost|number_format}</span></td>
		        <td><span id="span,lost_amount,{$t}">{$report_info.$t.lost_amt|number_format:2}</span></td>
		        <td><span id="span,over,{$t}">{$report_info.$t.over|number_format}</span></td>
		        <td><span id="span,over_amount,{$t}">{$report_info.$t.over_amt|number_format:2}</span></td>
		        <td><span id="span,qty,{$t}">{$report_info.$t.qty|number_format}</span> pcs</td>
				{if $config.monthly_report_additional_qty_sold}
				<td><span id="span,qty1,{$t}">{$report_info.$t.qty1|number_format}</span> pcs</td>
				<td><span id="span,qty2,{$t}">{$report_info.$t.qty2|number_format}</span> pcs</td>
				<td><span id="span,qty3,{$t}">{$report_info.$t.qty3|number_format}</span> pcs</td>
				<td><span id="span,qty4,{$t}">{$report_info.$t.qty4|number_format}</span> pcs</td>
				{/if}
		        <td><span id="span,amount,{$t}">{$report_info.$t.amt|number_format:2}</span></td>
		        <td><span id="span,stock_closing,{$t}">{$report_info.$t.stock_closing|number_format:2}</span></td>
	    	</tr>
	    {/foreach}
	</table>

</div>

<div style="clear:both;"><br /></div>

<div id="div_content">
	{include file='consignment.monthly_report.page.tpl'}
</div>
<br />
	{if !$read_only}
		<p align="center">
		    <button onClick="window.location='consignment.monthly_report.php'" style="font:bold 20px Arial; background-color:#09c; color:#fff;">Close</button>
		    <input type=button value="Delete" style="font:bold 20px Arial; background-color:#900; color:#fff;" onclick="do_delete()">
		    <button onClick="do_save(this);" style="font:bold 20px Arial; background-color:#f90; color:#fff;">Save</button>
		    <button onClick="do_confirm(this);" style="font:bold 20px Arial; background-color:#091; color:#fff;">Confirm</button>
		    <button onClick="print_draft();" style="font:bold 20px Arial; background-color:#09c; color:#fff;">Print Draft</button>
		</p>
	{else}
	    <p align="center">
	    <button onClick="window.location='consignment.monthly_report.php'" style="font:bold 20px Arial; background-color:#09c; color:#fff;">Close</button>
		{if $sessioninfo.privilege.CON_MONTHLY_REPORT}
		    {if $form.status eq 1}
		    	<button onClick="re_call(this);" style="font:bold 20px Arial; background-color:red; color:#fff;">ReCall</button>
		    {/if}
		    <button onClick="print_draft();" style="font:bold 20px Arial; background-color:#09c; color:#fff;">Print Draft</button>
	    {/if}
	    </p>
	{/if}
{/if}

{literal}
<script>
current_table = $('table_p');
reset_sku_autocomplete();
</script>
{/literal}
{include file=footer.tpl}
