/*
5/27/2011 2:49:07 PM Alex
- created by me
7/5/2011 3:07:47 PM Alex
- clear artno if error occur
9/9/2011 11:56:57 AM Alex
- only change article no if empty value
*/

//masterfile category
check_category_code = function(){
		var obj=$('cat_code_id');
		if (obj.value.length>1){
			obj.value="";
			alert("Error: only allow 1 digit alphabet letter.");
			return;
		}
		
		if (obj.value.toUpperCase() == 'O' || obj.value.toUpperCase() == 'I'){
			obj.value="";
			alert("Error: Not allow to use alphabet 'O' and 'I'");
			return;
		}
}

if ($('cat_code_id'))	$('cat_code_id').observe('change', check_category_code);

//masterfile sku applpication
function pad(number, length){
    var str = '' + number;
    while (str.length < length) {
        str = '0' + str;
    }

    return str;
}

additional_function = function (){
	reenter_artno();
}

reenter_artno = function(){

	if (!$('max_artno_id').value || $('max_num_id').value==''){
/*		$$('.input_artno').each(function(ele,obj){
			ele.value='';
		});
*/		return;
	}
	
	var artno = $('max_artno_id').value;
	var num = int($('max_num_id').value);
	var patt = eval('/^'+artno+'/g');
	$$('.input_artno').each(function(ele,obj){
		if (patt.test(ele.value)){
			var str=int(ele.value.replace(patt,''));
			num=str;
		}
		
		if (!ele.value){
			num+=1;
			var auto_num=pad(num,3);
			ele.value=artno+auto_num;
		}
	});
}

ajax_get_max_artno = function(){
	
	if ($('sku_default_trade_discount_code').value != 'OF'){
		if (!$('sku_category_id').value || !$('sku_vendor_id').value || !$('sku_default_trade_discount_code').value)	return;
	}
	
	new Ajax.Request('custom/justkids/masterfile_sku_application.php',
	{
		method: 'post',
		parameters:{
			a : "ajax_get_max_artno",
			category_id : $('sku_category_id').value,
			vendor_id : $('sku_vendor_id').value,
			default_trade_discount_code: $('sku_default_trade_discount_code').value
		},
		onComplete: function(m){
			eval("var json="+m.responseText);
			if (json['error'] != undefined){
				alert(json['error']);
				$('max_artno_id').value='';
				$('max_num_id').value='';
			}else{
				$('max_artno_id').value=json['code'];
				$('max_num_id').value=json['num'];				
			}
			reenter_artno();
		}
	});
}

if ($('sku_category_id') && $('sku_vendor_id') && $('sku_default_trade_discount_code')){
	$('sku_category_id').onchange= ajax_get_max_artno;
	$('sku_vendor_id').onchange= ajax_get_max_artno;
	$('sku_default_trade_discount_code').onchange+= ajax_get_max_artno;
	$('sku_default_trade_discount_code').observe('change',ajax_get_max_artno);
	
/*
	if ($('max_num_id').value && $('max_artno_id').value){	
		reenter_artno();
	}
*/	
}