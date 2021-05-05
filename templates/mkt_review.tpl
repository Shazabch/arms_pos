{include file=header.tpl}
{literal}
<style>
#table_sheet { font: 10px "MS Sans Serif" normal;}
td, th { white-space:nowrap; }
.border{
	background-color:black;
}
.small_border{
	background-color:grey;
}
.promote{
	background-color:#afc;
}
.display{
	background-color:white;
	text-align:right;
}
.keyin{
	background-color:yellow;
	text-align:right;
}
.optional{
	background-color:#ffc;
	text-align:right;
}
.weekly{
	background-color:#e4efff;
	text-align:right;
	FONT-WEIGHT: bold;
}
.monthly{
	background-color:#ccc;
	text-align:right;
	FONT-WEIGHT: bold;
}
.positive{
	color:blue;
	text-align:right;
}
.negative{
	color:red;
	text-align:right;
}
.zero{
	color:green;
	text-align:right;
}
</style>

<script>

function do_back(y,b){
	window.location='/mkt_annual.php?year='+y+'&branch_id='+b;
}

function list_sel(id,n,s){
	var i;
	for(i=1;i<=4;i++)
	{
		if (i==n)
		    $('lst'+i).className='active';
		else
		    $('lst'+i).className='';
	}
	$('category_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	var pg = '';
	if (s!=undefined) pg = 's='+s;

	new Ajax.Updater('category_list', 'mkt_review.php', {
		parameters: 'a=ajax_get_category&id='+id+'&ajax=1&t='+n+'&'+pg+'&'+Form.serialize(document.f_m_r),
		evalScripts: true
		});
}

function do_search(){
	document.f_m_r.a.value='search';
	document.f_m_r.submit();
}

var last_obj;
var g_field;
var g_day;
var g_cat_id;


var con_dept_default=new Array();
var con_temp_dept_amt=new Array();

/*
var con_total_normal=new Array();
var con_total_sales=new Array();
var con_total_amt=new Array();
var con_total_sa_amt=new Array();
var con_total_variant=new Array();

var con_week_normal=new Array();
var con_week_sales=new Array();
var con_week_amt=new Array();
var con_week_sa_amt=new Array();
var con_week_variant=new Array();
*/

function set_value(el,value){
	var cmpvalue = float(value)
	//alert(el.className);
	el.className=el.className.replace(/(negative|zero|positive)/g,'');
	if(cmpvalue<0)
		el.addClassName('negative');
	else if (cmpvalue==0)
		el.addClassName('zero');
	else
		el.addClassName('positive');

	el.innerHTML=value;
}

////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
function update_con_table(){

    var total_dept_con=new Array();
    var weekly_dept_con=new Array();  
     
	var e = $('table_sheet').getElementsByClassName('con_table');

	for(var i=0;i<e.length;i++)	{
	
        if (/^total_line_con/.test(e[i].id)) {
 		    total_line_con_val=float(e[i].innerHTML);
		}
	  	if (/^normal_/.test(e[i].id)) {
		    temp_normal=float(e[i].innerHTML);
		}
		if (/^sales_/.test(e[i].id)) {
		    temp_sales=float(e[i].innerHTML);
		}
		if (/^con_normal_/.test(e[i].id)) {
			con_normal_el = e[i];
		}
 		if (/^con_sales_/.test(e[i].id)) {
			con_sales_el = e[i];
		}
		if (/^con_amt_/.test(e[i].id)) {
			con_amount_el = e[i];
		}
 		if (/^con_pct_/.test(e[i].id)) {
			con_pct_el = e[i];
		}
		if (/^con_dept_\d+$/.test(e[i].id)){
			e[i].innerHTML=round(float(e[i].innerHTML),2)+'%';
			var line =e[i].title.split(",");
		    if (line[1]==0){
		        con_dept_default[line[2]] = float(e[i].innerHTML);
			}
			else{
 			    cur_value = float(e[i].innerHTML);
 			    if(!total_dept_con[line[2]]){
                    total_dept_con[line[2]]=0;
                    weekly_dept_con[line[2]]=0;
                 }
 	 			total_dept_con[line[2]]+=cur_value;
 	 			weekly_dept_con[line[2]]+=cur_value;
                if (cur_value==0){
					normal=(temp_normal*con_dept_default[line[2]]*total_line_con_val/10000);
					sales=(temp_sales*con_dept_default[line[2]]*total_line_con_val/10000);
				}
				else{
					normal=(temp_normal*cur_value*float($('total_line_con').innerHTML)/10000);
					sales=(temp_sales*cur_value*float($('total_line_con').innerHTML)/10000);
				}

				amt=round(sales,2)-round(normal,2);
				if(temp_normal>0){
					pct=(round(amt,2)/round(normal,2)*100);
				}
				else{
				    pct=0;
				}
				set_value(con_normal_el,round(normal,2));
				set_value(con_sales_el,round(sales,2));
				set_value(con_amount_el,round(amt,2));
				set_value(con_pct_el,round(pct,2)+'%');
			}
		}
 		if (/^con_dept_amt_/.test(e[i].id)){
 		    if(sales>0){
				temp_s_a_pct=(float(e[i].innerHTML)/sales*100);
			}
			else{
				temp_s_a_pct=0;
			}
			temp_con_variant=(float(e[i].innerHTML)-sales);
		}
		if (/^con_s_a_pct_/.test(e[i].id)) {
			set_value(e[i],round(temp_s_a_pct,2)+'%');
		}
		if (/^con_variant_/.test(e[i].id)) {
			set_value(e[i],round(temp_con_variant,2));
		}
 		/*var temp_1 =new RegExp('^week_con_dept_');
	    if (temp_1.test(e[i].id)) {
	    	set_value(e[i],round(weekly_dept_con[e[i].title],2)+'%');
	        weekly_dept_con[e[i].title]=0;
		}
 		var temp_2 =new RegExp('^total_con_dept_');
	    if (temp_2.test(e[i].id)) {
	    	set_value(e[i],round(total_dept_con[e[i].title],2)+'%');
	        total_dept_con[e[i].title]=0;
		}*/

	}
}

///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
function update_d_c(){
	
	var e = $('table_sheet').getElementsByClassName('con_table');
	if(g_day==0){
		new Ajax.Updater('reload', 'mkt_review.php',{
			parameters: ''+Form.serialize(document.f_m_r)+'&a=refresh_dept_default_val',
			evalScripts: true
		});
		update_total_target_row();
		for(var i=0;i<e.length;i++)	{
		
	        if (/^total_line_con/.test(e[i].id)) {
	 		    total_line_con_val=float(e[i].innerHTML);
			}
		  	if (/^normal_/.test(e[i].id)) {
			    temp_normal=float(e[i].innerHTML);
			}
			if (/^sales_/.test(e[i].id)) {
			    temp_sales=float(e[i].innerHTML);
			}

			var temp_2 =new RegExp('^con_normal_'+g_cat_id);
			if (temp_2.test(e[i].id)) {
				con_normal_el = e[i];
			}
			var temp_3 =new RegExp('^con_sales_'+g_cat_id);
	 		if (temp_3.test(e[i].id)) {
				con_sales_el = e[i];
			}
			var temp_4 =new RegExp('^con_amt_'+g_cat_id);
			if (temp_4.test(e[i].id)) {
				con_amount_el = e[i];
			}
			var temp_5 =new RegExp('^con_pct_'+g_cat_id);
	 		if (temp_5.test(e[i].id)) {
				con_pct_el = e[i];
			}

			var temp_1 =new RegExp('^con_dept_'+g_cat_id);
			if (temp_1.test(e[i].id)){
				e[i].innerHTML=round(float(e[i].innerHTML),2)+'%';
				var line =e[i].title.split(",");
			    if (line[1]==0){
			        con_dept_default = float(e[i].innerHTML);
				}
				else{
		 			cur_value = float(e[i].innerHTML);
		 			var_dept='dept_default_val_'+g_cat_id;
		            if (cur_value==0 || cur_value==$(var_dept).value){
		            	e[i].innerHTML=round(con_dept_default,2)+'%';
						normal=(temp_normal*con_dept_default*total_line_con_val/10000);
						sales=(temp_sales*con_dept_default*total_line_con_val/10000);
						amt=sales-normal;
						if(temp_normal>0){
							pct=(amt/normal*100);
						}
						else{
						    pct=0;
						}
						set_value(con_normal_el,round(normal,2));
						set_value(con_sales_el,round(sales,2));
						set_value(con_amount_el,round(amt,2));
						set_value(con_pct_el,round(pct,2)+'%');
					}
 				}

			}
			var temp_6 =new RegExp('^con_dept_amt_'+g_cat_id);
	 		if (temp_6.test(e[i].id)){
				var line =e[i].title.split(",");
				if(line[1]==g_day){
		 		    if(sales>0){
						temp_s_a_pct=(float(e[i].innerHTML)/sales*100);
					}
					else{
						temp_s_a_pct=0;
					}
					temp_con_variant=(float(e[i].innerHTML)-sales);
				}
			}
			var temp_7 =new RegExp('^con_s_a_pct_'+g_cat_id);
			if (temp_7.test(e[i].id)) {
				var line =e[i].title.split(",");
				if(line[1]==g_day)
					set_value(e[i],round(temp_s_a_pct,2)+'%');
			}
			var temp_8 =new RegExp('^con_variant_'+g_cat_id);
			if (temp_8.test(e[i].id)) {
				var line =e[i].title.split(",");
				if(line[1]==g_day)
					set_value(e[i],round(temp_con_variant,2));
			}

		}
	}
	else{
	//alert('dsfds');
		for(var i=0;i<e.length;i++)	{
	        if (/^total_line_con/.test(e[i].id)) {
	 		    total_line_con_val=float(e[i].innerHTML);
			}

			var temp_9 =new RegExp('^normal_'+g_day);
		  	if (temp_9.test(e[i].id)) {
			    temp_normal=float(e[i].innerHTML);
			}
			var temp_10 =new RegExp('^sales_'+g_day);
			if (temp_10.test(e[i].id)) {
			    temp_sales=float(e[i].innerHTML);
			}

			var temp_2 =new RegExp('^con_normal_'+g_cat_id);
			if (temp_2.test(e[i].id)) {
				var line =e[i].title.split(",");
				if(line[1]==g_day)
					con_normal_el = e[i];
			}
			var temp_3 =new RegExp('^con_sales_'+g_cat_id);
	 		if (temp_3.test(e[i].id)) {
				var line =e[i].title.split(",");
				if(line[1]==g_day)
					con_sales_el = e[i];
			}
			var temp_4 =new RegExp('^con_amt_'+g_cat_id);
			if (temp_4.test(e[i].id)) {
				var line =e[i].title.split(",");
				if(line[1]==g_day)
					con_amount_el = e[i];
			}
			var temp_5 =new RegExp('^con_pct_'+g_cat_id);
	 		if (temp_5.test(e[i].id)) {
				var line =e[i].title.split(",");
				if(line[1]==g_day)
					con_pct_el = e[i];
			}

			var temp_1 =new RegExp('^con_dept_'+g_cat_id);
			if (temp_1.test(e[i].id)){
				e[i].innerHTML=round(float(e[i].innerHTML),2)+'%';
				var line =e[i].title.split(",");
				//alert(line[1]);
			    if (line[1]==0){
			        con_dept_default = float(e[i].innerHTML);
				}
				else{
				    if(line[1]==g_day){
		 			    cur_value = float(e[i].innerHTML);
		                if (cur_value==0){
							normal=(temp_normal*con_dept_default*total_line_con_val/10000);
							sales=(temp_sales*con_dept_default*total_line_con_val/10000);
						}
						else{
							normal=(temp_normal*cur_value*total_line_con_val/10000);
							sales=(temp_sales*cur_value*total_line_con_val/10000);
						}
						//alert(normal+'==='+sales);
						amt=sales-normal;
						if(temp_normal>0){
							pct=(amt/normal*100);
						}
						else{
						    pct=0;
						}
						//alert(amt+'==='+pct);
						set_value(con_normal_el,round(normal,2));
						set_value(con_sales_el,round(sales,2));
						set_value(con_amount_el,round(amt,2));
						set_value(con_pct_el,round(pct,2)+'%');
					}
				}

			}
			var temp_6 =new RegExp('^con_dept_amt_'+g_cat_id);
	 		if (temp_6.test(e[i].id)){
				var line =e[i].title.split(",");
				if(line[1]==g_day){
		 		    if(sales>0){
						temp_s_a_pct=(float(e[i].innerHTML)/sales*100);
					}
					else{
						temp_s_a_pct=0;
					}
					temp_con_variant=(float(e[i].innerHTML)-sales);
				}
			}
			var temp_7 =new RegExp('^con_s_a_pct_'+g_cat_id);
			if (temp_7.test(e[i].id)) {
				var line =e[i].title.split(",");
				if(line[1]==g_day)
					set_value(e[i],round(temp_s_a_pct,2)+'%');
			}
			var temp_8 =new RegExp('^con_variant_'+g_cat_id);
			if (temp_8.test(e[i].id)) {
				var line =e[i].title.split(",");
				if(line[1]==g_day)
					set_value(e[i],round(temp_con_variant,2));
			}

		}
	}

}
function update_dc_week(){

	var weekly_normal=new Array();
	var total_normal=new Array();
	var weekly_amt=new Array();
	var total_amt=new Array();
	var weekly_d_amt=new Array();
	var total_d_amt=new Array();
	var weekly_dept_con=new Array();
	var total_dept_con=new Array();
	var total_sales=new Array();
	var weekly_sales=new Array();
	var total_variant=new Array();
	var weekly_variant=new Array();

	var e = $('table_sheet').getElementsByClassName('con_week_normal');

	for(var i=0;i<e.length;i++)	{
		var temp_1 =new RegExp('^con_normal_'+g_cat_id);
		if (temp_1.test(e[i].id)){
			var line =e[i].title.split(",");
			if(!total_normal[line[2]]){
				total_normal[line[2]]=0;
				weekly_normal[line[2]]=0;
			}
			total_normal[line[2]]+=float(e[i].innerHTML);
			weekly_normal[line[2]]+=float(e[i].innerHTML);
		}
 		var temp_2 =new RegExp('^con_amt_'+g_cat_id);
		if (temp_2.test(e[i].id)){
			var line =e[i].title.split(",");
			if(!total_amt[line[2]]){
				total_amt[line[2]]=0;
				weekly_amt[line[2]]=0;
			}
			total_amt[line[2]]+=float(e[i].innerHTML);
			weekly_amt[line[2]]+=float(e[i].innerHTML);
		}
 		var temp_21 =new RegExp('^con_dept_'+g_cat_id);
		if (temp_21.test(e[i].id)){
			var line =e[i].title.split(",");
			if(!total_dept_con[line[2]]){
				total_dept_con[line[2]]=0;
				weekly_dept_con[line[2]]=0;
			}
			total_dept_con[line[2]]+=float(e[i].innerHTML);
			weekly_dept_con[line[2]]+=float(e[i].innerHTML);
		}
 		var temp_3 =new RegExp('^con_dept_amt_'+g_cat_id);
		if (temp_3.test(e[i].id)){
			var line =e[i].title.split(",");
			if(!total_d_amt[line[2]]){
				total_d_amt[line[2]]=0;
				weekly_d_amt[line[2]]=0;
			}
			total_d_amt[line[2]]+=float(e[i].innerHTML);
			weekly_d_amt[line[2]]+=float(e[i].innerHTML);
		}
 		var temp_4 =new RegExp('^week_con_normal_'+g_cat_id);
	    if (temp_4.test(e[i].id)) {
	    	set_value(e[i],round(weekly_normal[e[i].title],2));
	        weekly_normal[e[i].title]=0;
		}
 		var temp_5 =new RegExp('^week_con_amount_'+g_cat_id);
	    if (temp_5.test(e[i].id)) {
	    	set_value(e[i],round(weekly_amt[e[i].title],2));
	        weekly_amt[e[i].title]=0;
		}
 	/*	var temp_55 =new RegExp('^week_con_dept_'+g_cat_id);
	    if (temp_55.test(e[i].id)) {
	    	set_value(e[i],round(weekly_dept_con[e[i].title],2)+'%');
	        weekly_dept_con[e[i].title]=0;
		}*/
 		var temp_6 =new RegExp('^week_con_sa_amount_'+g_cat_id);
	    if (temp_6.test(e[i].id)) {
	    	set_value(e[i],round(weekly_d_amt[e[i].title],2));
	        weekly_d_amt[e[i].title]=0;
		}
 		var temp_7 =new RegExp('^total_con_normal_'+g_cat_id);
		if (temp_7.test(e[i].id)) {
	    	set_value(e[i],round(total_normal[e[i].title],2));
		}
 		var temp_8 =new RegExp('^total_con_amount_'+g_cat_id);
		if (temp_8.test(e[i].id)) {
	    	set_value(e[i],round(total_amt[e[i].title],2));
		}
 		/*var temp_88 =new RegExp('^total_con_dept_'+g_cat_id);
		if (temp_88.test(e[i].id)) {
	    	set_value(e[i],round(total_dept_con[e[i].title],2)+'%');
		}*/
 		var temp_9 =new RegExp('^total_con_sa_amount_'+g_cat_id);
		if (temp_9.test(e[i].id)) {
	    	set_value(e[i],round(total_d_amt[e[i].title],2));
		}
	}
	var weekly_dept_con=new Array();
	var total_dept_con=new Array();
	var e = $('table_sheet').getElementsByClassName('con_week_sales');

	for(var j=0;j<e.length;j++)	{
 		var temp_10 =new RegExp('^con_sales_'+g_cat_id);
		if (temp_10.test(e[j].id)){
			var line =e[j].title.split(",");
			if(!total_sales[line[2]]){
				total_sales[line[2]]=0;
				weekly_sales[line[2]]=0;
			}
			total_sales[line[2]]+=float(e[j].innerHTML);
			weekly_sales[line[2]]+=float(e[j].innerHTML);
		}
		
 		var temp_101 =new RegExp('^con_dept_amt_'+g_cat_id);
		if (temp_101.test(e[j].id)){
			var line =e[j].title.split(",");
			if(!total_dept_con[line[2]]){
				total_dept_con[line[2]]=0;
				weekly_dept_con[line[2]]=0;
			}
			total_dept_con[line[2]]+=float(e[j].innerHTML);
			weekly_dept_con[line[2]]+=float(e[j].innerHTML);
		}		
		
 		var temp_11 =new RegExp('^con_variant_'+g_cat_id);
		if (temp_11.test(e[j].id)) {
			var line =e[j].title.split(",");
			if(!total_variant[line[2]]){
				total_variant[line[2]]=0;
				weekly_variant[line[2]]=0;
			}
			total_variant[line[2]]+=float(e[j].innerHTML);
			weekly_variant[line[2]]+=float(e[j].innerHTML);
		}
 		var temp_12 =new RegExp('^week_con_sales_'+g_cat_id);
	    if (temp_12.test(e[j].id)) {
	    	set_value(e[j],round(weekly_sales[e[j].title],2));
	        //weekly_sales[e[j].title]=0;
		}
        
 		var temp_121 =new RegExp('^week_con_sa_pct_'+g_cat_id);
	    if (temp_121.test(e[j].id)) {
	        if(weekly_sales[e[j].title]>0){
                temp_val=weekly_dept_con[e[j].title]*100/weekly_sales[e[j].title];
            }
            else{
                temp_val=0;
            }
	    	set_value(e[j],round(temp_val,2)+'%');
	        weekly_sales[e[j].title]=0;
            weekly_dept_con[e[j].title]=0;
		}        
        
 		var temp_13 =new RegExp('^week_con_variant_'+g_cat_id);
	    if (temp_13.test(e[j].id)) {
	    	set_value(e[j],round(weekly_variant[e[j].title],2));
	        weekly_variant[e[j].title]=0;
		}
  		var temp_14 =new RegExp('^total_con_sales_'+g_cat_id);
		if (temp_14.test(e[j].id)) {
	    	set_value(e[j],round(total_sales[e[j].title],2));
		}
		
  		var temp_141 =new RegExp('^total_con_sa_pct_'+g_cat_id);
		if (temp_141.test(e[j].id)) {
		    if(total_sales[e[j].title]>0){
                temp_val2=total_dept_con[e[j].title]*100/total_sales[e[j].title];        
            }
            else{
                temp_val2=0;
            }
	    	set_value(e[j],round(temp_val2,2)+'%');
		}

   		var temp_15 =new RegExp('^total_con_variant_'+g_cat_id);
		if (temp_15.test(e[j].id)) {
	    	set_value(e[j],round(total_variant[e[j].title],2));
		}

	}
}
///////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
function update_sales_row(){
	if(g_day>0){
		var var_amt='amount_'+g_day;
		var var_pct='pct_'+g_day;
		var var_s_a_pct='sa_pct_'+g_day;
		var var_variant='variant_'+g_day;
		var var_sales='sales_'+g_day;
		var var_normal='normal_'+g_day;

		var s_a='s_a_'+g_day;

		var amt_val=float($(var_sales).innerHTML)-float($(var_normal).innerHTML);
		set_value($(var_amt),round(amt_val,2));

		if(float($(var_normal).innerHTML)>0){
			var pct=(amt_val/float($(var_normal).innerHTML)*100);
		}
		else{
			var pct=0;
		}
		set_value($(var_pct),round(pct,2)+'%');

	 	if(float($(var_sales).innerHTML)>0){
			var s_a_pct=(float($(s_a).innerHTML)/float($(var_sales).innerHTML)*100);
		}
		else{
			var s_a_pct=0;
		}
		set_value($(var_s_a_pct),round(s_a_pct,2)+'%');

		var variant=float($(s_a).innerHTML)-float($(var_sales).innerHTML);
		set_value($(var_variant),round(variant,2));
	}
}

function update_sales_week_row(){
	var total_sales=0;
	var total_normal=0;
	var total_amount=0;
	var total_pct=0;
	var total_variant=0;
	var total_sa_pct=0;
	var total_sa=0;

	var weekly_sales=0;
	var weekly_normal=0;
	var weekly_amount=0;
	var weekly_pct=0;
	var weekly_variant=0;
	var weekly_sa_pct=0;
	var weekly_sa=0;

	var e = $('table_sheet').getElementsByClassName('master_week_row');

	for(var i=0;i<e.length;i++)	{

	 	if (/^sales_/.test(e[i].id)){
			total_sales+=float(e[i].innerHTML);
	  		weekly_sales+=float(e[i].innerHTML);
		}
 		if (/^normal_/.test(e[i].id)){
			total_normal+=float(e[i].innerHTML);
			weekly_normal+=float(e[i].innerHTML);
		}
	  	if (/^amount_/.test(e[i].id)){
			total_amount+=float(e[i].innerHTML);
	  		weekly_amount+=float(e[i].innerHTML);
		}
		if (/^s_a_/.test(e[i].id)) {
			total_sa+=float(e[i].innerHTML);
	  		weekly_sa+=float(e[i].innerHTML);
		}
		if (/^variant_/.test(e[i].id)){
			total_variant+=float(e[i].innerHTML);
	  		weekly_variant+=float(e[i].innerHTML);
		}
		if (/^total_w_sales_/.test(e[i].id)) {
		    set_value(e[i],round(weekly_sales,2));
		    //weekly_sales=0;
		}
		if (/^total_w_amount_/.test(e[i].id)) {
		    set_value(e[i],round(weekly_amount,2));
		    //weekly_amount=0;
		}
   		if (/^total_w_pct_/.test(e[i].id)) {
			if(weekly_normal>0){
                weekly_pct=weekly_amount*100/weekly_normal;
			}
			else{
				weekly_pct=0;
			}
		    set_value(e[i],round(weekly_pct,2)+'%');
		    weekly_pct=0;
			weekly_normal=0;
		}
		if (/^total_w_s_a_/.test(e[i].id)) {
		    set_value(e[i],round(weekly_sa,2));
		    //weekly_sa=0;
		}
		if (/^total_w_sa_pct_/.test(e[i].id)) {
			if(weekly_sales>0){
				weekly_sa_pct=weekly_sa*100/weekly_sales;
			}
			else{
	 			weekly_sa_pct=0;
			}
  		    set_value(e[i],round(weekly_sa_pct,2)+'%');
			weekly_sales=0;
			weekly_amount=0;
			weekly_sa=0;
		}
	 	if (/^total_w_variant_/.test(e[i].id)) {
		    set_value(e[i],round(weekly_variant,2));
		    weekly_variant=0;
		}
	}
	if(total_sales>0){
		total_sa_pct=total_sa*100/total_sales;
	}
	else{
		total_sa_pct=0;
	}
	set_value($('total_sa_pct'),round(total_sa_pct,2)+'%');
	if(total_normal>0){
        total_pct=total_amount*100/total_normal;
	}
	else{
		total_pct=0;
	}
	set_value($('total_pct'),round(total_pct,2)+'%');
	set_value($('total_sales'),round(total_sales,2));
	set_value($('total_amount'),round(total_amount,2));
	set_value($('total_variant'),round(total_variant,2));
}

function update_con_sales_row(){
	var e = $('table_sheet').getElementsByClassName('con_row_sales');

	for(var i=0;i<e.length;i++)	{
 		if (/^total_line_con/.test(e[i].id)) {
 		    total_line_con_val=float(e[i].innerHTML);
		}
  		if (/^sales_/.test(e[i].id)) {
 		    temp_sales=float(e[i].innerHTML);
		}
		if (/^con_normal_/.test(e[i].id)) {
 		    temp_normal=float(e[i].innerHTML);
		}
 		if (/^con_sales_/.test(e[i].id)) {
			con_sales_el = e[i];
		}
		if (/^con_amt_/.test(e[i].id)) {
			con_amount_el = e[i];
		}
 		if (/^con_pct_/.test(e[i].id)) {
			con_pct_el = e[i];
		}
		if (/^con_dept_\d+$/.test(e[i].id)){
			var line =e[i].title.split(",");
		    if (line[1]==0){
		        con_dept_default[line[2]] = float(e[i].innerHTML);
			}
			else{
 			    cur_value = float(e[i].innerHTML);
                if (cur_value==0){
					sales=(temp_sales*con_dept_default[line[2]]*total_line_con_val/10000);
				}
				else{
					sales=(temp_sales*cur_value*total_line_con_val/10000);
				}
				amt=sales-temp_normal;
				if(temp_normal>0){
					pct=(amt/temp_normal*100);
				}
				else{
				    pct=0;
				}
				set_value(con_sales_el,round(sales,2));
				set_value(con_amount_el,round(amt,2));
				set_value(con_pct_el,round(pct,2)+'%');
			}
		}
 		if (/^con_dept_amt_/.test(e[i].id)){
 		    if(sales>0){
				temp_s_a_pct=(float(e[i].innerHTML)/sales*100);
			}
			else{
				temp_s_a_pct=0;
			}
			temp_con_variant=(float(e[i].innerHTML)-sales);
		}
		if (/^con_s_a_pct_/.test(e[i].id)) {
			set_value(e[i],round(temp_s_a_pct,2)+'%');
		}
		if (/^con_variant_/.test(e[i].id)) {
			set_value(e[i],round(temp_con_variant,2));
		}
	}
}


function update_con_sales_week_row(){

	var total_sales=new Array();
	var weekly_sales=new Array();
	var total_amt=new Array();
	var weekly_amt=new Array();
	var total_dept_amt=new Array();
	var weekly_dept_amt=new Array();
	var total_variant=new Array();
	var weekly_variant=new Array();

	var e = $('table_sheet').getElementsByClassName('con_week_sales');

	for(var i=0;i<e.length;i++)	{
		if (/^con_sales_/.test(e[i].id)){
			var line =e[i].title.split(",");
			if(!total_sales[line[2]]){
				total_sales[line[2]]=0;
				weekly_sales[line[2]]=0;
			}
			total_sales[line[2]]+=float(e[i].innerHTML);
			weekly_sales[line[2]]+=float(e[i].innerHTML);
		}
		if (/^con_amt_/.test(e[i].id)){
			var line =e[i].title.split(",");
			if(!total_amt[line[2]]){
				total_amt[line[2]]=0;
				weekly_amt[line[2]]=0;
			}
			total_amt[line[2]]+=float(e[i].innerHTML);
			weekly_amt[line[2]]+=float(e[i].innerHTML);
		}
		if (/^con_dept_amt_/.test(e[i].id)){
			var line =e[i].title.split(",");
			if(!total_dept_amt[line[2]]){
				total_dept_amt[line[2]]=0;
				weekly_dept_amt[line[2]]=0;
			}
			total_dept_amt[line[2]]+=float(e[i].innerHTML);
			weekly_dept_amt[line[2]]+=float(e[i].innerHTML);
		}
		if (/^con_variant_/.test(e[i].id)) {
			var line =e[i].title.split(",");
			if(!total_variant[line[2]]){
				total_variant[line[2]]=0;
				weekly_variant[line[2]]=0;
			}
			total_variant[line[2]]+=float(e[i].innerHTML);
			weekly_variant[line[2]]+=float(e[i].innerHTML);
		}

	    if (/^week_con_sales_/.test(e[i].id)) {
	    	set_value(e[i],round(weekly_sales[e[i].title],2));
	        //weekly_sales[e[i].title]=0;
		}
	    if (/^week_con_amount_/.test(e[i].id)) {
	    	set_value(e[i],round(weekly_amt[e[i].title],2));
	        weekly_amt[e[i].title]=0;
		}
	    if (/^week_con_sa_pct_/.test(e[i].id)) {           
            if(weekly_sales[e[i].title]>0){
                temp_val=weekly_dept_amt[e[i].title]/weekly_sales[e[i].title]*100;
            }
            else{
                temp_val=0;           
            }
        	set_value(e[i],round(temp_val,2)+'%');
            weekly_sales[e[i].title]=0;
            weekly_dept_amt[e[i].title]=0;
		}
	    if (/^week_con_variant_/.test(e[i].id)) {
	    	set_value(e[i],round(weekly_variant[e[i].title],2));
	        weekly_variant[e[i].title]=0;
		}
		if (/^total_con_sales_/.test(e[i].id)) {
	    	set_value(e[i],round(total_sales[e[i].title],2));
		}
		if (/^total_con_amount_/.test(e[i].id)) {
	    	set_value(e[i],round(total_amt[e[i].title],2));
		}
		if (/^total_con_sa_pct_/.test(e[i].id)) {
            if(total_sales[e[i].title]>0){
                temp_val=total_dept_amt[e[i].title]/total_sales[e[i].title]*100;
            }
            else{
                temp_val=0;           
            }
        	set_value(e[i],round(temp_val,2)+'%');
		}
		if (/^total_con_variant_/.test(e[i].id)) {
	    	set_value(e[i],round(total_variant[e[i].title],2));
		}

	}
}
////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////
function update_normal_row(){
	if(g_day>0){
		var var_sales='sales_'+g_day;
		var var_amt='amount_'+g_day;
		var var_pct='pct_'+g_day;
		var var_normal='normal_'+g_day;

		if(float($(var_sales).innerHTML)==0){
			val=float($(var_normal).innerHTML);
			set_value($(var_sales),round(val,2));
			$(var_sales).className=$(var_sales).className.replace(/(negative|zero|positive)/g,'');
			$(var_sales).addClassName('keyin');
			//update_sales_row();
			update_sales_week_row();
			update_con_sales_row();
			update_con_sales_week_row();
		}
		var amt_val=float($(var_sales).innerHTML)-float($(var_normal).innerHTML);
		set_value($(var_amt),round(amt_val,2));

        if(float($(var_normal).innerHTML)>0){
			var pct=(amt_val/float($(var_normal).innerHTML)*100);
		}
		else{
			var pct=0;
		}
		set_value($(var_pct),round(pct,2)+'%');
	}
}

function update_normal_week_row(){

	var total_normal=0;
	var total_amount=0;
	var total_pct=0;
	var weekly_normal=0;
	var weekly_amount=0;
	var weekly_pct=0;

	var e = $('table_sheet').getElementsByClassName('master_week_row');

	for(var i=0;i<e.length;i++)	{

		if (/^normal_/.test(e[i].id)){
			total_normal+=float(e[i].innerHTML);
			weekly_normal+=float(e[i].innerHTML);
		}
	  	if (/^amount_/.test(e[i].id)){
			total_amount+=float(e[i].innerHTML);
	  		weekly_amount+=float(e[i].innerHTML);
		}
		if (/^total_w_normal_/.test(e[i].id)) {
		    set_value(e[i],round(weekly_normal,2));
		    //weekly_normal=0;
		}
		if (/^total_w_amount_/.test(e[i].id)) {
		    set_value(e[i],round(weekly_amount,2));
		    //weekly_amount=0;
		}
  		if (/^total_w_pct_/.test(e[i].id)) {
			if(weekly_normal>0){
                weekly_pct=weekly_amount*100/weekly_normal;
			}
			else{
				weekly_pct=0;
			}
		    set_value(e[i],round(weekly_pct,2)+'%');
		    weekly_pct=0;
			weekly_normal=0;
			weekly_amount=0;
		}
	}
	if(total_normal>0){
        total_pct=total_amount*100/total_normal;
	}
	else{
		total_pct=0;
	}
	set_value($('total_pct'),round(total_pct,2)+'%');
	set_value($('total_normal'),round(total_normal,2));
	set_value($('total_amount'),round(total_amount,2));

}

function update_con_normal_row(){

	//var con_dept_default=new Array();
	var total_line_con_val=0;
	var temp_normal=0;
	var temp_sales=0;

	var e = $('table_sheet').getElementsByClassName('con_row_normal');

	
	for(var i=0;i<e.length;i++)	{

 		if (/^total_line_con/.test(e[i].id)) {
 		    total_line_con_val=float(e[i].innerHTML);
		}
 		if (/^normal_/.test(e[i].id)) {
 		    temp_normal=float(e[i].innerHTML);
		}
		if (/^con_normal_/.test(e[i].id)) {
			con_normal_el = e[i];
		}
		if (/^con_sales_/.test(e[i].id)) {
 		    temp_sales=float(e[i].innerHTML);
		}
		if (/^con_amt_/.test(e[i].id)) {
			con_amount_el = e[i];
		}
 		if (/^con_pct_/.test(e[i].id)) {
			con_pct_el = e[i];
		}

		if (/^con_dept_\d+$/.test(e[i].id)){
			var line =e[i].title.split(",");

		    if (line[1]==0){
		        con_dept_default[line[2]] = float(e[i].innerHTML);
			}
			else{
 			    cur_value = float(e[i].innerHTML);
                if (cur_value==0){
					normal=(temp_normal*con_dept_default[line[2]]*total_line_con_val/10000);
				}
				else{
					normal=(temp_normal*cur_value*total_line_con_val/10000);
				}
				amt=temp_sales-normal;
				if(normal>0){
					pct=(amt/normal*100);
				}
				else{
				    pct=0;
				}
				set_value(con_normal_el,round(normal,2));
				set_value(con_amount_el,round(amt,2));
				set_value(con_pct_el,round(pct,2)+'%');
			}
		}
	}
}

function update_con_normal_week_row(){

	var total_normal=new Array();
	var weekly_normal=new Array();
	
	var total_sales=new Array();
	var weekly_sales=new Array();
	
	var weekly_amt=new Array();
	var total_amt=new Array();
	
	var weekly_d_amt=new Array();
	var total_d_amt=new Array();
	
	var weekly_pct=new Array();
	var total_pct=new Array();

	var el_week_amt=new Array();
	
	var e = $('table_sheet').getElementsByClassName('con_week_normal');

	for(var i=0;i<e.length;i++)	{
	
		if (/^con_normal_/.test(e[i].id)){
			var line =e[i].title.split(",");
			if(!total_normal[line[2]]){
				total_normal[line[2]]=0;
				weekly_normal[line[2]]=0;
			}
			total_normal[line[2]]+=float(e[i].innerHTML);
			weekly_normal[line[2]]+=float(e[i].innerHTML);
		}
		
		if (/^con_sales_/.test(e[i].id)){
			var line =e[i].title.split(",");
			if(!total_sales[line[2]]){
				total_sales[line[2]]=0;
				weekly_sales[line[2]]=0;
			}
			total_sales[line[2]]+=float(e[i].innerHTML);
			weekly_sales[line[2]]+=float(e[i].innerHTML);
		}
		
		if (/^con_dept_amt_/.test(e[i].id)){
			var line =e[i].title.split(",");
			if(!total_d_amt[line[2]]){
				total_d_amt[line[2]]=0;
				weekly_d_amt[line[2]]=0;
			}
			total_d_amt[line[2]]+=float(e[i].innerHTML);
			weekly_d_amt[line[2]]+=float(e[i].innerHTML);
		}
		
	    if (/^week_con_normal_/.test(e[i].id)) {
	    	set_value(e[i],round(weekly_normal[e[i].title],2));
	        //weekly_normal[e[i].title]=0;
		}
	    if (/^week_con_amount_/.test(e[i].id)) {
			el_week_amt[e[i].title]=e[i];
		}
 	    if (/^week_con_pct_/.test(e[i].id)) {
 	    
 	    	if(!total_amt[e[i].title]){
				total_amt[e[i].title]=0;
			}
	     	temp_amt=(weekly_sales[e[i].title])-(weekly_normal[e[i].title]);
	     	total_amt[e[i].title]+=temp_amt;
	     	weekly_amt[e[i].title]=temp_amt;
			set_value(el_week_amt[e[i].title],round(temp_amt,2));
			
			if(!weekly_pct[e[i].title]){
				weekly_pct[e[i].title]=0;
			}
 	        if(weekly_normal[e[i].title]>0){
				weekly_pct[e[i].title]=(round(temp_amt,2)*100/round(weekly_normal[e[i].title],2));
			}
			else{
				weekly_pct[e[i].title]=0;
			}
			
	    	set_value(e[i],round(weekly_pct[e[i].title],2)+'%');
	        weekly_pct[e[i].title]=0;
     	    weekly_sales[e[i].title]=0;
	        weekly_normal[e[i].title]=0;
	        weekly_amt[e[i].title]=0;
		}
	    if (/^week_con_sa_amount_/.test(e[i].id)) {
	    	set_value(e[i],round(weekly_d_amt[e[i].title],2));
	        weekly_d_amt[e[i].title]=0;
		}
		
		if (/^total_con_normal_/.test(e[i].id)) {
	    	set_value(e[i],round(total_normal[e[i].title],2));
		}
		if (/^total_con_amount_/.test(e[i].id)) {
	    	set_value(e[i],round(total_amt[e[i].title],2));
		}
		if (/^total_con_pct_/.test(e[i].id)) {
			if(total_normal[e[i].title]>0){
				total_pct[e[i].title]=total_amt[e[i].title]*100/total_normal[e[i].title];
			}
			else{
				total_pct[e[i].title]=0;
			}
	    	set_value(e[i],round(total_pct[e[i].title],2)+'%');
		}
		if (/^total_con_sa_amount_/.test(e[i].id)) {
	    	set_value(e[i],round(total_d_amt[e[i].title],2));
		}
	}

}
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
function update_sa_row(){
	if(g_day>0){
		var var_s_a_pct='sa_pct_'+g_day;
		var var_variant='variant_'+g_day;
		var var_sales='sales_'+g_day;

		var s_a='s_a_'+g_day;

	 	if(float($(var_sales).innerHTML)>0){
			var s_a_pct=(float($(s_a).innerHTML)/float($(var_sales).innerHTML)*100);
		}
		else{
			var s_a_pct=0;
		}
		set_value($(var_s_a_pct),round(s_a_pct,2)+'%');

		var variant=float($(s_a).innerHTML)-float($(var_sales).innerHTML);
		set_value($(var_variant),round(variant,2));
	}
}

function update_sa_week_row(){

	var total_sales=0;
	var total_s_a=0;
	var total_variant=0;
	var total_sa_pct=0;
	
	var weekly_sales=0;
	var weekly_s_a=0;
	var weekly_variant=0;
	var weekly_sa_pct=0;

	var e = $('table_sheet').getElementsByClassName('master_week_row');
	
	for(var i=0;i<e.length;i++)	{
	
	 	if (/^sales_/.test(e[i].id)){
			total_sales+=float(e[i].innerHTML);
	  		weekly_sales+=float(e[i].innerHTML);
		}
   	    if (/^s_a_/.test(e[i].id)){
			total_s_a+=float(e[i].innerHTML);
  			weekly_s_a+=float(e[i].innerHTML);
		}
		if (/^variant_/.test(e[i].id)){
			total_variant+=float(e[i].innerHTML);
  			weekly_variant+=float(e[i].innerHTML);
		}
	    if (/^total_w_s_a_/.test(e[i].id)) {
	        set_value(e[i],round(weekly_s_a,2));
	        //weekly_s_a=0;
		}
		if (/^total_w_sa_pct_/.test(e[i].id)) {
			if(weekly_sales>0){
				weekly_sa_pct=weekly_s_a*100/weekly_sales;
			}
			else{
	 			weekly_sa_pct=0;
			}
  		    set_value(e[i],round(weekly_sa_pct,2)+'%');
			weekly_sales=0;
			weekly_s_a=0;
		}
 	    if (/^total_w_variant_/.test(e[i].id)) {
	        set_value(e[i],round(weekly_variant,2));
	        weekly_variant=0;
		}

	}
	if(total_sales>0){
		total_sa_pct=total_s_a*100/total_sales;
	}
	else{
		total_sa_pct=0;
	}
	set_value($('total_sa_pct'),round(total_sa_pct,2)+'%');
	set_value($('total_s_a'),round(total_s_a,2));
	set_value($('total_variant'),round(total_variant,2));
}
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
function update_total_target_row(){

	var total_line=float($('total_target').innerHTML)*float($('total_line_con').innerHTML)/100;
	set_value($('subline_con'),round(total_line,2));

	var e = $('table_sheet').getElementsByClassName('dept_group');

	for(var i=0;i<e.length;i++)	{
 		if (/^subline_con/.test(e[i].id)) {
		    temp_amt_line=float(e[i].innerHTML);
		}
  		if (/^total_dept_amt_/.test(e[i].id)) {
			con_temp_dept_amt[e[i].title] = e[i];
		}
		if (/^con_dept_/.test(e[i].id)) {
			var line =e[i].title.split(",");
			dept_amt=(float(e[i].innerHTML)*temp_amt_line/100);
	        set_value(con_temp_dept_amt[line[2]],round(dept_amt,2));
	        con_temp_dept_amt[line[2]].style.background="white";
		}
	}
}
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
function update_sales_amt(){
	var e = $('table_sheet').getElementsByClassName('sales_amt_group');
	
	for(var j=0;j<e.length;j++)	{
 		var temp_1 =new RegExp('^con_sales_'+g_cat_id);
		if (temp_1.test(e[j].id)){
			var line =e[j].title.split(",");
			if(line[1]==g_day){
			    //alert(float(e[j].innerHTML));
		    	temp_sales=float(e[j].innerHTML);
			}
		}
 		var temp_2 =new RegExp('^con_dept_amt_'+g_cat_id);
		if (temp_2.test(e[j].id)){
			var line =e[j].title.split(",");
			if(line[1]==g_day)
		    	temp_amt=float(e[j].innerHTML);
		}
 		var temp_3 =new RegExp('^con_s_a_pct_'+g_cat_id);
		if (temp_3.test(e[j].id)){
			var line =e[j].title.split(",");
			if(line[1]==g_day){
			    if(temp_amt>0)
					temp_pct=(temp_amt/temp_sales*100);
				else
				    temp_pct=0;
	        set_value(e[j],round(temp_pct,2)+'%');
			}
		}
 		var temp_4 =new RegExp('^con_variant_'+g_cat_id);
		if (temp_4.test(e[j].id)){
			var line =e[j].title.split(",");
			if(line[1]==g_day){
		    	temp_variant=temp_amt-temp_sales;
				set_value(e[j],round(temp_variant,2));
			}

		}
	
	}
}

function update_sales_amt_week(){

	var total_sa=new Array();
	var weekly_sa=new Array();
	var total_sales=new Array();
	var weekly_sales=new Array();
	var total_variant=new Array();
	var weekly_variant=new Array();

	var e = $('table_sheet').getElementsByClassName('con_sales_amt');

	for(var i=0;i<e.length;i++)	{

 		var temp_0 =new RegExp('^con_sales_'+g_cat_id);
		if (temp_0.test(e[i].id)) {
			var line =e[i].title.split(",");
			if(!total_sales[line[2]]){
				total_sales[line[2]]=0;
				weekly_sales[line[2]]=0;
			}
			total_sales[line[2]]+=float(e[i].innerHTML);
			weekly_sales[line[2]]+=float(e[i].innerHTML);
		}
	
 		var temp_1 =new RegExp('^con_dept_amt_'+g_cat_id);
		if (temp_1.test(e[i].id)) {
			var line =e[i].title.split(",");
			if(!total_sa[line[2]]){
				total_sa[line[2]]=0;
				weekly_sa[line[2]]=0;
			}
			total_sa[line[2]]+=float(e[i].innerHTML);
			weekly_sa[line[2]]+=float(e[i].innerHTML);
		}
	
 		var temp_2 =new RegExp('^con_variant_'+g_cat_id);
		if (temp_2.test(e[i].id)) {
			var line =e[i].title.split(",");
			if(!total_variant[line[2]]){
				total_variant[line[2]]=0;
				weekly_variant[line[2]]=0;
			}
			total_variant[line[2]]+=float(e[i].innerHTML);
			weekly_variant[line[2]]+=float(e[i].innerHTML);
		}
		
 		var temp_3 =new RegExp('^week_con_sa_amount_'+g_cat_id);
	    if (temp_3.test(e[i].id)) {
	    	set_value(e[i],round(weekly_sa[e[i].title],2));
	        //weekly_sa[e[i].title]=0;
		}
        
 		var temp_30 =new RegExp('^week_con_sa_pct_'+g_cat_id);
	    if (temp_30.test(e[i].id)) {
	        if(weekly_sales[e[i].title]>0){
                temp_val=weekly_sa[e[i].title]*100/weekly_sales[e[i].title];
            }
            else{
                temp_val=0;
            }
	    	set_value(e[i],round(temp_val,2)+'%');
	        weekly_sa[e[i].title]=0;
	        weekly_sales[e[i].title]=0;
		}
        
 		var temp_4 =new RegExp('^week_con_variant_'+g_cat_id);
	    if (temp_4.test(e[i].id)) {
	    	set_value(e[i],round(weekly_variant[e[i].title],2));
	        weekly_variant[e[i].title]=0;
		}
  		var temp_5 =new RegExp('^total_con_sa_amount_'+g_cat_id);
		if (temp_5.test(e[i].id)) {
	    	set_value(e[i],round(total_sa[e[i].title],2));
		}
		
	   var temp_50 =new RegExp('^total_con_sa_pct_'+g_cat_id);
		if (temp_50.test(e[i].id)) {
		    if(total_sales[e[i].title]>0){
                temp_val1=total_sa[e[i].title]*100/total_sales[e[i].title];
            }
            else{
                temp_val1=0;
            }
	    	set_value(e[i],round(temp_val1,2)+'%');
		}
		
   		var temp_6 =new RegExp('^total_con_variant_'+g_cat_id);
		if (temp_6.test(e[i].id)) {
	    	set_value(e[i],round(total_variant[e[i].title],2));
		}
	}

}
////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
function update_table(cell,day_id){

	new Effect.Highlight(cell);
	if (g_field=='normal_forecast'){
		update_normal_row();
		update_normal_week_row();
		update_con_normal_row();
		update_con_normal_week_row();
	}
	else if (g_field=='sales_target'){
		update_sales_row();
		update_sales_week_row();
		update_con_sales_row();
		update_con_sales_week_row();
	}
	else if (g_field=='sales_achieve'){
		update_sa_row();
		update_sa_week_row();
	}
	else if (g_field=='total_target'|| g_field=='line_contribute'){
		update_total_target_row();
	}
	else if (g_field=='total_contribute'){
		update_d_c();
		update_dc_week();
	}
	else if (g_field=='sales_amount'){
	    update_sales_amt();
	    update_sales_amt_week();
	}
}

function do_edit(obj,dept_default_val){
    last_obj = obj;
	var line = obj.title.split(",");
	if(line[0]=='n')
		g_field='normal_forecast';
 	else if(line[0]=='s')
		g_field='sales_target';
  	else if(line[0]=='s_a')
		g_field='sales_achieve';
  	else if(line[0]=='t_t')
		g_field='total_target';
   	else if(line[0]=='l_c')
		g_field='line_contribute';
    else if(line[0]=='t_c')
		g_field='total_contribute';
     else if(line[0]=='a_s_a')
		g_field='sales_amount';

	g_day=line[1];
	g_cat_id=line[2];
	$('edit_text').value = float(obj.innerHTML.replace(/^&nbsp;/,''));
	Position.clone(obj, $('edit_popup'));
	Position.clone(obj, $('edit_text'));
	Element.show('edit_popup');
	$('edit_text').select();
	$('edit_text').focus();
}


function save(){
	Element.hide('edit_popup');
	if(g_field=='total_contribute'){
	    var total_value=0;
		var e = $('table_sheet').getElementsByClassName('dept_group');
		for(var i=0;i<e.length;i++)	{
	 		var temp_1 =new RegExp('^con_dept_');
		 	if (temp_1.test(e[i].id)){
		 		var line =e[i].title.split(",");
				if(line[2]!=g_cat_id){
					total_value+=float(e[i].innerHTML);
				}
			}
		}
		total_value=total_value+float($('edit_text').value);
		if(total_value>'100'){
			$('Msg').innerHTML='';
			alert('Selected month lines contribution over 100%');
			return;
		}
		else if(total_value<100){
			$('Msg').innerHTML =  'Total department(s) contribution is '+total_value+'% less than 100%';
		}
		else {
			$('Msg').innerHTML='';
		}
	}
	else if (g_field=='normal_forecast' || g_field=='sales_target'){
	    var total_normal=0;
	    var total_sales=0;
		var e = $('table_sheet').getElementsByClassName('con_table');
		
		for(var i=0;i<e.length;i++)	{
	 		var temp_1 =new RegExp('^normal_');
		 	if (temp_1.test(e[i].id)){
		 		var line =e[i].title.split(",");
				if(line[1]!=g_day){
					total_normal+=float(e[i].innerHTML);
				}
			}
	 		var temp_2 =new RegExp('^sales_');
		 	if (temp_2.test(e[i].id)){
		 		var line =e[i].title.split(",");
				if(line[1]!=g_day){
					total_sales+=float(e[i].innerHTML);
				}
			}
		}
		if(g_field=='normal_forecast'){
			total_normal=total_normal+float($('edit_text').value);	
		}
		else{
			total_sales=total_sales+float($('edit_text').value);			
		}
	

		total_target=float($('total_target').innerHTML);

		if(total_sales>total_target){
			$('Msg').innerHTML='';
			//alert('Total Normal Forecast and Sales Target is Over than Total Target');
			//return;		
		}
		else if(total_sales<total_target){
			$('Msg').innerHTML =  'Total Normal Forecast and Sales Target is '+total_value+'% less than Total Target';
		}
		else {
			$('Msg').innerHTML='';
		}
		if(total_normal>total_sales){
			//alert('Total Normal Forecast is Over than Total Sales Target');
		}
	}
	if(float(last_obj.innerHTML)!=float($('edit_text').value)){
		last_obj.innerHTML = 'Saving..';
		var newp = last_obj;
		new Ajax.Updater(newp,'mkt_review.php?field='+g_field+'&cat_id='+g_cat_id+'&day='+g_day+'&value='+float($('edit_text').value)+'&'+Form.serialize(document.f_m_r)+'&a=save_edit',{onComplete:function(){update_table(newp,g_day)}});

	}
}

function do_update_sales(){

	if (confirm('The system will take sometimes to update this month daily sales, are you sure want to continue?')){	
		document.f_m_r.a.value='update_sales';
		document.f_m_r.submit();
	}
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

<div id=edit_popup style="display:none;position:absolute;z-index:100;background:#fff;border:2px solid #000;margin:-2px 0 0 -2px;">
<input id=edit_text size=5 onblur=save()>
</div>


<form name=f_m_r method=post>
<input type=hidden name=a>
<input type=hidden name=branch_id value={$branch_id}>

<div class=stdframe style="background:#fff;">
{if $BRANCH_CODE eq 'HQ'}
	<b>Branch :</b> <select name="branch_id" onchange="do_search();">
	{foreach item="curr_Branch" from=$branches}
	<option value={$curr_Branch.id} {if $curr_Branch.id==$branch_id or $smarty.request.branch_id==$curr_Branch.id}selected{/if}>{$curr_Branch.code}</option>
	{/foreach}
	</select>
{/if}
	
	&nbsp;&nbsp;
	
	<b>Year</b>
	<select name="year" onchange="do_search()">
	<option value={$year-1} {if $year == ($year-1)}selected{/if}>{$year-1}</option>
	<option value={$year} 	{if $year == $year}selected{/if}>{$year}</option>
	<option value={$year+1} {if $year == ($year+1)}selected{/if}>{$year+1}</option>
	</select>

	&nbsp;&nbsp;

	<b>Month</b> <select name="month" onchange="do_search()">
	<option value="01"  {if $month == '01'}selected{/if}>January</option>
	<option value="02"  {if $month == '02'}selected{/if}>February</option>
	<option value="03"  {if $month == '03'}selected{/if}>March</option>
	<option value="04"  {if $month == '04'}selected{/if}>April</option>
	<option value="05"  {if $month == '05'}selected{/if}>May</option>
	<option value="06"  {if $month == '06'}selected{/if}>June</option>
	<option value="07" {if $month == '07'}selected{/if}>July</option>
	<option value="08"  {if $month == '08'}selected{/if}>August</option>
	<option value="09"  {if $month == '09'}selected{/if}>September</option>
	<option value="10" {if $month == '10'}selected{/if}>October</option>
	<option value="11" {if $month == '11'}selected{/if}>November</option>
	<option value="12" {if $month == '12'}selected{/if}>December</option>
	</select>

	&nbsp;&nbsp;

	<input type=button onclick="do_search()" value="Refresh">
	<input type=button onclick="do_back({$smarty.request.original_y|default:$year},{$branch_id})" value="Back to Annual View">
	<input type=button onclick="do_update_sales()" value="Update This Month Sales">
</div>
<br>
<div>
<span id="Msg" title="Message" class="negative"></span>
</div>
<br>
{if $category}
<div class=tab style="height:21px;white-space:nowrap;">
{section name=k loop=$category}
{assign var=p value=$smarty.section.k.iteration}
<a href="javascript:list_sel('{$category[k].id}',{$p})" id="lst{$p}">{$category[k].description}</a>
{/section}
</div>
{/if}

{if $branch_id>1}
<div id=category_list>
{include file=mkt_review.sheet.tpl}
</div>
{/if}

</form>

{include file=footer.tpl}
<script>
list_sel(1,1);
{if $smarty.request.a eq 'view'}
Form.disable(document.f_a);
{/if}
_init_enter_to_skip(document.f_a);
</script>
