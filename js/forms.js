/*
3/8/2011 4:07:12 PM Andy
- Change function curtain to allow accept second parameter as curtain ID.

5/16/2011 3:56:37 PM Andy
- Add JS function number_format(number, decimal)

6/22/2011 3:09:27 PM Andy
- Add JS function toggle_select_all_opt(sel, selected)

7/18/2011 3:26:21 PM Justin
- Fixed the bugs that system unable login to sub branch from HQ that contains "&".

8/10/2011 3:15:50 PM Andy
- Add show_discount_help(), validate_discount_format(discount_pattern) and get_discount_amt(amt, discount_pattern, params) at form.js

9/14/2011 5:22:25 PM Andy
- Add JS function strtotime()

9/30/2011 5:02:13 PM Andy
- Add JS function "is_array".

10/7/2011 4:24:44 PM Andy
- Add can "show parent sales trend".

10/10/2011 11:36:36 AM Andy
- Add checking to ignore if user key in empty username to login as. 

10/24/2011 10:46:43 AM Justin
- Added rounding function.

10/27/2011 11:35:39 AM Andy
- Add function "open_cc2".

11/8/2011 1:22:42 PM Andy
- Add JS Function showLocalImage(file_input, target_img)

12/14/2011 1:30:26 PM Andy
- Add new JS function open_window(). same as window.open()

2/28/2012 1:56:54 PM Andy
- Add new JS function in_array()

3/2/2012 4:43:32 PM Justin
- Added new function "check_description".

4/10/2012 10:35:32 AM Justin
- Fixed the bugs where system still allow user to key in double quote at first character.

6/18/2012 2:10:00 PM Andy
- Add new JS function cloneEle(ele) and toHTML(ele)

7/23/2012 12:22 PM Andy
- Add new JS function day_diff(dt1, dt2)

8/2/2012 5:34 PM Andy
- Enhanced check_required_field to monitor radio and checkbox.

8/10/2012 11:38 PM Andy
- Add new JS function disable_sub_ele()

8/30/2012 3:29 PM Andy
- Fix center_div() to able to get correct width and height even not set inline css.

4:21 PM 9/13/2012 Justin
- Added new JS function set_changed_member_points.

9/14/2012 1:57 PM Kee Kee
- Add new JS function check_user_profile_allow_mprice_list to hide mprice list

3/13/2013 11:27 AM Justin
- Enhanced to redirect user to login page when click on login button instead of do nothing while their session has been expired.

4/2/2013 11:59 AM Justin
- Fixed the rounding error.

4/25/2013 4:28 PM Justin
- Added new function "positive_check".

5/20/2013 3:53 PM Justin
- Temporary disable positive_check.

12/19/2013 12:01 PM Andy
- Add new JS function show_sku_image_div()

2/5/2014 3:36 PM Fithri
- fix js bug of wrong sku image url when image is in another server

2/19/2014 3:54 PM Justin
- Added new js function "is_new_id".

3/31/2014 4:40 PM Andy
- Add new JS function Ajax.abort().

5/5/2015 3:35 PM Andy
- Added new JS Object "GLOBAL_MODULE".

5/7/2015 10:38 AM Andy
- Enhanced js rounding function.

1:58 PM 6/2/2015 Andy
- Enhanced function get_discount_amt() not to round2.

12/4/2015 9:21AM DingRen
- add check login

1/12/2016 4:37 PM Andy
- Enhance goto_branch() to have 1 parameter is_arms.

1/23/2017 3:18 PM Andy
- Add new js function check_exceed_max_timestamp()

4/10/2017 11:17 AM Qiu Ying
- Enhanced to add IBT GRN in Parent SKU Inventory & SKU Item Inventory

5/8/2017 11:31 AM Andy
- Add variable ARMS_CURRENCY.

5/12/2017 16:28 Qiu Ying
- Added function "check_receipt_desc_length"
- Added function "add_to_sku_receipt_desc"
- Added function "contain_unicode"
- Added function "update_sku_receipt_desc"

8/21/2017 10:22 AM Andy
- Add function show_markup_help().

11/30/2017 12:09 PM Andy
- Enhanced function check_login() to accept 2nd parameter "extra_params", and able to disable the popup login.

3/22/2019 5:05 PM Andy
- Added js function "toYMD".

5/24/2019 4:08 PM Andy
- Added new js GLOBAL_MODULE function "show_wait_popup" and "hide_wait_popup".

7/22/2019 1:58 PM Andy
- Added js function "htmlToElement".

7/31/2019 5:06 PM Andy
- Added js function "isAlphaNumeric".

8/15/2019 4:02 PM Andy
- Enhanced to prevent more than 1 check_login() call within 5 seconds.

9/24/2019 2:30 PM Andy
- Enhanced js function "show_sku_image_div" to auto center_div after image loaded.

10/2/2019 3:21 PM Andy
- Enhanced js function "mi" to accept 3rd parameter "positive_only".

11/4/2019 10:35 AM Andy
- Added js feature to show loading when ajax is running.

11/21/2019 9:33 AM Andy
- Fixed Ajax.Responders when got exception, onComplete is not trigger.

12/6/2019 5:14 PM Andy
- Added js function "calculate_text_length".

6/15/2020 2:22 PM Andy
- Added js function "my_setCookie" and "my_getCookie".

6/17/2020 3:55 PM Andy
- Added js function "scroll_to_child_obj".

9/3/2020 5:23 PM William
- Enhanced js function "show_sku_image_div" to able to pass file time to clear browser cache.

11/3/2020 11:55 AM William
- Enhanced to add style "z-index" to popup_div function.

2/4/2021 4:05 PM Shane
- Enhanced "popup_div" and "show_sku_image_div" function to able to pass zindex
*/
var _loading_ = '<img src=/ui/clock.gif align=absmiddle> Loading...';
var is_popup_login=false;
var ajax_url=null;
var ajax_option=null;
var login_callback=null;
var ARMS_CURRENCY = [];
var last_success_login_check = 0;

String.prototype.trim = function() { return this.replace(/^\s+|\s+$/g, ''); };
String.prototype.strip = function() { return this.replace(/[\n\r]+/g, ''); };
String.prototype.uczap = function() { return this.toUpperCase().replace(/[^A-Z0-9\-]/g, ''); };
String.prototype.regex = function(regx,replace) { return this.replace(regx, replace); };
Element.prototype.insertAfter = function(obj,target){
	var next = target.nextSibling;
	if (next!=undefined)
		this.insertBefore(obj, next);
	else
		this.appendChild(obj);
}

/**
 * Ajax.Request.abort
 * extend the prototype.js Ajax.Request object so that it supports an abort method
 */
Ajax.Request.prototype.abort = function() {
    // prevent and state change callbacks from being issued
    this.transport.onreadystatechange = Prototype.emptyFunction;
    // abort the XHR
    this.transport.abort();
    // update the request counter
    Ajax.activeRequestCount--;
};

open_window = window.open;

function check_receipt_desc_length(element_id)
{
	var desc = $(element_id).value.trim();
			
	var max_desc_length = 40;
	var have_unicode = contain_unicode(desc);

	if (have_unicode){
		max_desc_length = 13;
	}
	
	if(desc.length > max_desc_length){
		var str = " for";
		if(have_unicode){
			str = " for non";
		}
		alert("Maximum character" + str + " alphabetical character is " + max_desc_length + ".\nPlease shorten your receipt description.");
		$(element_id).focus();
		return false;
	}
	return true;
}

function add_to_sku_receipt_desc(element_id,element_id2)
{
	var description = $(element_id).value.trim();
	
	var have_unicode = contain_unicode(description);
	var max_desc_length = 40;
	if(have_unicode){
		max_desc_length = 13;
	}
	if($(element_id2) != null)
		$(element_id2).value=description.toUpperCase().substring(0,max_desc_length);
}

function contain_unicode(obj){
	for (var i = 0, n = obj.length; i < n; i++) {
		if (obj[i].charCodeAt() > 255) {
			return true; 
		}
	}
	return false;
}


function update_sku_receipt_desc(obj){
	var description = obj.value;
	
	var have_unicode = contain_unicode(description);
	var ret = description.length;
	var max_desc_length = 40;
	
	if (have_unicode){
		max_desc_length = 13;
	}
	
	if(ret > max_desc_length){
		obj.value = description.toUpperCase().substr(0, max_desc_length);
	}
}

// return radio button's value
function getRadioValue(objlist)
{
	var i;
	if (objlist)
	{
	    if(objlist.checked){
			return objlist.value;
		}else{
            for(i=0;i<objlist.length;i++)
			{
			    if (objlist[i].checked) return objlist[i].value;
			}
		}
		
	}
	return undefined;
}

// close this window or return to given url
function close_window(url)
{
	if (url == undefined) url = '/';
	if (window.opener!=undefined)
	{
		window.opener.focus();
		window.close();
	}
	else
	{
		document.location = url;
	}
}

function popup_div(div_id, innerhtml, zindex = 100)
{
	if (!$(div_id))
	{
	    var newdiv = document.createElement('div');
	    newdiv.id = div_id;
	    newdiv.style.position='absolute';
	    newdiv.style.left='0';
	    newdiv.style.top='0';
		newdiv.style.background="#fff";
	    newdiv.style.padding="10px";
	    newdiv.style.border="2px solid #000";
		// newdiv.style['z-index']='100';
	    //newdiv.style.width = 'auto';
	    
	    document.body.appendChild(newdiv);
	    new Draggable(div_id);
	}
	$(div_id).style['z-index'] = zindex;
	$(div_id).innerHTML = '<div style=text-align:right><img src=/ui/closewin.png vspace=4 hspace=4 onclick="Element.hide(this.parentNode.parentNode)"></div>'+innerhtml;
	$(div_id).style.left = ((document.body.scrollLeft)+mx) + 'px';
	$(div_id).style.top = ((document.body.scrollTop)+my) + 'px';
	$(div_id).show();
}

function default_curtain_clicked()
{
	$$('.curtain_popup').invoke('setStyle',{'display':'none'});
	curtain(false);
	try {
		curtain_clicked();
	} catch(e) {
	    // alert(e);
	}
}

function curtain(v, curtain_id, no_need_effect)
{
    if(!curtain_id) curtain_id = 'curtain';
    
	if (v)
	{
		// create a top top level div
		$(curtain_id).style.top = 0;
		$(curtain_id).style.left = 0;
		$(curtain_id).style.width = document.body.scrollWidth;
		$(curtain_id).style.height = document.body.scrollHeight;
		$(curtain_id).style.display = '';
		
		if(no_need_effect){
			$(curtain_id).style.opacity = 0.8;
			$(curtain_id).style.display = '';
		}else{
			new Effect.Opacity($(curtain_id),{duration:0.2, to:0.8});
		}
	}
	else
	{
		if(no_need_effect){
			$(curtain_id).style.opacity = 0;
			$(curtain_id).style.display = 'none';
		}else{
			// create a top top level div
			new Effect.Opacity($(curtain_id),{duration:0.2, to:0,
				afterFinish: function() { $(curtain_id).style.display='none'; }
			});
		}
	}
}

function center_div(div)
{
	var dimensions = $(div).getDimensions();
	
    $(div).style.top = int(document.body.scrollTop + (window.innerHeight-int(dimensions.height))/2)+'px';
	$(div).style.left = int(document.body.scrollLeft + (window.innerWidth-int(dimensions.width))/2)+'px';
}
function clear0(obj)
{
	if (obj.value==0) obj.value = '';
	obj.select();
}

function div_center_mouse(div)
{
	var x = (mx - parseInt($(div).style.width)/2 + parseInt(document.body.scrollLeft));
	if (x < 10) x = 10;
	var y = (my - parseInt($(div).style.height)/2 + parseInt(document.body.scrollTop));
	if (y < 10) y = 10;

	$(div).style.left = x + 'px';
	$(div).style.top = y + 'px';
}

function maximize_window(w) {
  if (window.screen) {
    var aw = screen.availWidth;
    var ah = screen.availHeight;
    w.moveTo(0, 0);
    w.resizeTo(aw, ah);
  }
}

function xml_getData(el, which)
{
    var stuff=el.getElementsByTagName(which);
    if (stuff == undefined)
		return new String('');
    else
		return stuff[0].firstChild.nodeValue;
}

function showdiv(id,hide)
{
	if (hide==true) return hidediv(id);
	
	var div = document.getElementById(id);
	if (div.style.display=='none') div.style.display='';

	if (div.style.position == 'absolute')
	    div.style.top = (parseInt(document.body.scrollTop)+50)+'px';
	//new Effect.Appear(id, { duration: 0.3,from: 0.1});
}

function hidediv(id)
{
	if (document.getElementById(id).style.display!='none')
	    document.getElementById(id).style.display='none';
		//new Effect.Fade(id, { duration: 0.3 });
}

function togglediv(id,expimg){
	
	if (document.getElementById(id).style.display == 'none')
	{
		if (expimg!=undefined) $(expimg).src = '/ui/collapse.gif';
		showdiv(id);// document.getElementById(id).style.display='none';
	}
	else
	{
		if (expimg!=undefined) $(expimg).src = '/ui/expand.gif';
		hidediv(id); //document.getElementById(id).style.display='';
	}
	return (document.getElementById(id).style.display == 'none');
}

// make all clicking highlight
function init_click_hilite(formobj)
{
    var x = formobj.getElementsByTagName('input'); //
	for (var i=0;i<x.length;i++)
	{
		if (x[i].type == 'text')
		{
            Event.observe(x[i], 'focus', function() { this.select() }, false);
			/*x[i].onfocus = function () {
                this.select();
			}*/
		}
	}
}

// input tracker - call init_chg to track which input fields has its value changed
var changed_input_tracker;
var dont_track = '';
function init_chg(formobj)
{
	var x = formobj.getElementsByTagName('input'); // skip password confirmation
	for (var i=0;i<x.length;i++)
	{
		if (x[i].alt.indexOf('confirm') >= 0) continue;
		if (dont_track.indexOf('|'+x[i].name+'|') >= 0) continue;
		// now we only recognize text-entry for its default value
		Event.observe(x[i], 'change', function() { chg(this.name) }, false);
		/*x[i].onchange = function () {
			chg(this.name);
		}*/
	}
	var x = formobj.getElementsByTagName('textarea'); // track textarea also
	for (var i=0;i<x.length;i++)
	{
		if (dont_track.indexOf('|'+x[i].name+'|') >= 0) continue;

		// now we only recognize text-entry for its default value
		Event.observe(x[i], 'change', function() { chg(this.name) }, false);
		/*x[i].onchange = function () {
			chg(this.name);
		}*/
	}
	var x = formobj.getElementsByTagName('select'); // track textarea also
	for (var i=0;i<x.length;i++)
	{
		if (dont_track.indexOf('|'+x[i].name+'|') >= 0) continue;
		// now we only recognize text-entry for its default value
		Event.observe(x[i], 'change', function() { chg(this.name) }, false);
		/*x[i].onchange = function () {
			chg(this.name);
		}*/
	}
	changed_input_tracker = document.createElement('input');
	changed_input_tracker.type = 'hidden';
	changed_input_tracker.name = 'changed_fields';
	formobj.appendChild(changed_input_tracker);
}


function clear_checkboxes(formobj){
	var x = formobj.getElementsByTagName('input');
	for (var i=0;i<x.length;i++)
	{
		if (x[i].type.indexOf('checkbox') >= 0)
		{
			x[i].checked = false;
		}
	}
}


function chg(n)
{
	changed_input_tracker.value += "|"+n;
}

function get_changed_fields(inp){
	var ret = {};
	if(!inp){
		alert('Invalid Element;')
		return false;
	}
	var tmp = inp.value.split("|");
	for(var i=0; i<tmp.length;i++){
		var fieldname = tmp[i].trim();
		if(fieldname){
			ret[fieldname] = 1;
		}
	}
	return ret;
}


var lastmn = '';
var thide = 0;

function changesel(obj, val)
{
	//alert('change to ' + val + ' total options = ' + obj.options.length);
	for (i=0; i<obj.options.length; i++)
	{
		if (obj.options[i].value == val)
			obj.options[i].selected = true;
		else
			obj.options[i].selected = false;
	}
}

function mnShow(id){
	if (lastmn == id) return;
	if (lastmn != '')
		document.getElementById(lastmn).style.display='none';
	if (id != '')
		document.getElementById(id).style.display='';
	lastmn = id;
	if (thide != 0) clearTimeout(thide);
	thide = setTimeout('mnHide()', 5000)
}

function mnHide()
{
	if (lastmn != '')
		document.getElementById(lastmn).style.display='none';
	lastmn = '';
	thide = 0;
}


function trim (str) {
  while (str.charAt(0) == ' ')
    str = str.substring(1);
  while (str.charAt(str.length - 1) == ' ')
    str = str.substring(0, str.length - 1);
  return str;
}

function empty(e, msg)
{
	e.value = trim(e.value);
    if (e.value == '')
    {
        alert(msg);
        e.focus();
        return true;
    }

    return false;
}

function empty_or_zero(e, msg)
{
	e.value = trim(e.value);
    if (e.value == '' || float(e.value)==0)
    {
        alert(msg);
        e.focus();
        return true;
    }

    return false;
}

// convert an input box to uppercase
function uc(obj) {
	obj.value = obj.value.toUpperCase().trim();
	return obj.value;
}


function ucz(obj)
{
	obj.value = obj.value.uczap();
}


// convert an input box to lowercase
function lc(obj) {
	obj.value = obj.value.toLowerCase().trim();
}

// check / uncheck a field by ID
function check_field(id)
{
	document.getElementById(id).checked = true;
}

function uncheck_field(id)
{
	document.getElementById(id).checked = false;
}

// check radio/checkbox group status by id
// usage: rdcb_empty(Array("cb1", "cb2"), 'no option selected')

function rdcb_empty(arr, msg)
{
	for (i=0; i<arr.length; i++)
	{
		if (arr[i].checked)
		{
			return false;
		}
	}
	alert(msg);
	arr[0].focus();
	return true;
}

function rdcbn_empty(pref, n, msg)
{
	for (i=1; i<=n; i++)
	{
		if (document.getElementById(pref+i))
		{
			if (document.getElementById(pref+i).checked)
			{
				return false;
			}
		}
	}
	alert(msg);
	document.getElementById(pref+'1').focus();
	return true;
}

/*mouse tracking*/
var mx, my;
function mouse_trapper(evt){
	if (window.event){ evt = window.event; }
	if (evt){
		mx = evt.clientX;
		my = evt.clientY;
		//var pos = evt.clientX + ", " + evt.clientY;
		//window.status=pos;
	}
}


var hexVals = new Array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9",
              "A", "B", "C", "D", "E", "F");
var unsafeString = "\"<>%\\^[]`\+\$\,";
// deleted these chars from the include list ";", "/", "?", ":", "@", "=", "&" and #
// so that we could analyze actual URLs

// this function checks to see if a char is URL unsafe.
// Returns bool result. True = unsafe, False = safe
function isUnsafe(compareChar)
{
if (unsafeString.indexOf(compareChar) == -1 && compareChar.charCodeAt(0) > 32 && compareChar.charCodeAt(0) < 123)
   { return false; } // found no unsafe chars, return false
else
   { return true; }
}

function decToHex(num, radix)
// part of the hex-ifying functionality
{
var hexString = "";
while (num >= radix)
      {
       temp = num % radix;
       num = Math.floor(num / radix);
       hexString += hexVals[temp];
      }
hexString += hexVals[num];
return reversal(hexString);
}

function reversal(s) // part of the hex-ifying functionality
{
var len = s.length;
var trans = "";
for (i=0; i<len; i++)
    { trans = trans + s.substring(len-i-1, len-i); }
s = trans;
return s;
}

function convert(val) // this converts a given char to url hex form
{ return  "%" + decToHex(val.charCodeAt(0), 16); }


function URLEncode(val)
// changed Mar 25, 2002: added if on 122 and else block on 129 to exclude Unicode range
{
	var len     = val.length;
	var backlen = len;
	var i       = 0;

	var newStr  = "";
	var frag    = "";
	var encval  = "";
	var original = val;

	for (i=0;i<len;i++)
	{
	  if (val.substring(i,i+1).charCodeAt(0) < 255)  // hack to eliminate the rest of unicode from this
	     {
	      if (isUnsafe(val.substring(i,i+1)) == false)
	         { newStr = newStr + val.substring(i,i+1); }
	      else
	         { newStr = newStr + convert(val.substring(i,i+1)); }
	     }
	  else // woopsie! restore.
	     {
	       alert ("Found a non-ISO-8859-1 character at position: " + (i+1) + ",\nPlease eliminate before continuing.");
	       newStr = original; i=len;                // short-circuit the loop and exit
	     }
	}

	return newStr;
}

function round2(num)
{
	num = float(strip2(num));
	return round(num,2);
}


function round(num,n)
{
	if(!n)  n = 0;
	var num = float(strip2(num));
    	
	//return float(Math.round(num*Math.pow(10,n))/Math.pow(10,n)).toFixed(n);
	return (+(Math.round(num + "e"+n) + "e-"+n)).toFixed(n);

}

// convert an input box to integer
function mi(obj,z, positive_only) {
	if (obj.value=='') return;

	obj.value = int(obj.value);
	// Positive value only
	if(positive_only == 1 && obj.value < 0)	obj.value = 0;
	if (z == 1 && obj.value==0) obj.value = '';
}

// convert an input box to integer, empty if zero
function miz(obj) {
	mi(obj,1);
}

// convert an input box to float
function mf(obj,n,z) {
    if (obj.value=='') return;

	if (n == undefined)
		obj.value = round2(obj.value);
	else
	    obj.value = round(obj.value,n);

    if (z == 1 && obj.value==0) obj.value = '';
}

// convert an input box to float, empty if zero
function mfz(obj,n) {
	mf(obj,n,1);
}

function int(expr)
{
	expr = new String(expr).replace(/,/g, '');
	var i = parseInt(expr,10);
	if (isNaN(i))
	    return 0;
	else
	    return i;
}


function float(expr)
{
	expr = new String(expr).replace(/,/g, '');
	var i = parseFloat(expr);
	if (isNaN(i))
	    return 0;
	else
	    return i;
}


var _field_selected = 0;
var _loop_and_exit = 0;

function _prvfield()
{
	var el = _field_selected.form;
	var i;
	for (i=el.length-1;i>=0;i--)
	{
	    if (_loop_and_exit)
	    {
			if (!el[i].readOnly && !el[i].disabled && el[i].type != 'hidden')
   			{
				el[i].focus();
				if (el[i] && (el[i].type == 'text' || el[i].type == 'checkbox')) el[i].select();
				return;
			}
		}
		else
		{
			if (el[i] == _field_selected)
			{
				_loop_and_exit = 1;
			}
        }
	}
	_field_selected = el[el.length-1];
	_prvfield();
	return;
}

function _nextfield()
{
	var el = _field_selected.form;
	var i;
	for (i=0;i<el.length;i++)
	{
	    if (_loop_and_exit)
	    {
			if (!el[i].readOnly && !el[i].disabled && el[i].type != 'hidden')
   			{
				el[i].focus();
				if (el[i] && (el[i].type == 'text' || el[i].type == 'checkbox')) el[i].select();
				return;
			}
		}
		else
		{
			if (el[i] == _field_selected)
			{
				_loop_and_exit = 1;
			}
        }
	}
	_field_selected = el[0];
	_nextfield();
	return;
}

function _init_enter_to_skip(formobj)
{
	if (!formobj) return;
	var el = formobj.getElementsByTagName('input');
	var i;
	if (!el) return;
	for (i=0;i<el.length;i++)
	{
	    Event.observe(el[i], 'focus', function() {  _loop_and_exit = 0;_field_selected = this; }, false);
	    Event.observe(el[i], 'keypress', _skp, false);

	    /*el[i].onkeypress = _skp;
		el[i].onfocus = function () { _loop_and_exit = 0;_field_selected = this;}*/
		if (el[i].type == 'submit')
		{
		    el[i].type == 'button';
		}
		if (el[i].type == 'button')
		{
		    el[i].title =  'Press (*) to activate';
		}
	}
}

function _skp(event)
{
	if (event == undefined) event = window.event;
	if (event.keyCode==13 || event.keyCode==43)
	{
		if (_field_selected.type == 'textarea') return true;
		_nextfield();
		return false;
	}
	if(event.keyCode==45)
	{
		_prvfield();
		return false;
	}

	if(event.keyCode==42)
	{
	    if (_field_selected.type == 'button')
		{
			_field_selected.onclick();
	    }

		if (_field_selected.type == 'checkbox')
	    {
	        _field_selected.checked = !_field_selected.checked;
		}
		return false;
	}
}

function _init_focus_input_class(selector,parent)
{
	// if parent is given, select from there
	if (parent != undefined)
	{
	    el = document.getElementsByTagName(selector,parent)
	}
	var el = $$(selector);
	for (var i=0;i<el.length;i++)
	{
	    Event.observe(el[i], 'focus', function() { this.select(); this.addClassName('focused'); }, false);
	    Event.observe(el[i], 'blur', function() { this.removeClassName('focused'); }, false);
	}
}

/* shared function - popup price history */
function price_history(element,id,bid,type)
{
	if (type==undefined) type='';

	// All The Lines Are Commented beacause we Reaplaced popup with the modal 
	 // new Draggable('history_popup', {starteffect:undefined,endeffect:undefined});
	
	 // if ($('history_popup').style.display=='none')
	 // {
	 // 	// // move to position if hidden
	 // 	// Position.clone(element,$('history_popup'),{setHeight: false, setWidth:false, offsetLeft:20});
	 // 	// Element.show('history_popup');
	 // }
	// loading
	$('history_popup_content').innerHTML = _loading_;
	new Ajax.Updater('history_popup_content','/masterfile_sku_items_price.php?a=history&id='+id+'&branch_id='+bid+'&type='+type,{evalScripts:true});
}

function toggle_dept(obj)
{
	var input = $('department_option').getElementsByTagName('input');
	var count = 0;
	
	for(i=0;i<input.length;i++)
	{
		if (obj != undefined) input[i].checked = obj.checked;
		if (input[i].checked) count++;
	}
	
	if (count == input.length) 
	$('dept_all').checked = true;
	else
	$('dept_all').checked = false;
}

/* inventory popup */

function inventory_find(type, id_type, sku_id, id, dt, bid, is_ibt){
	var str = 'a=sku_inventory_find&branch_id='+bid+'&type='+type+'&dt='+dt+'&id='+id+'&sku_id='+sku_id+'&id_type='+id_type;
	if (is_ibt != undefined){
		str += '&is_ibt='+is_ibt;
	}
	curtain(true);
	center_div('misc_popup');
	Element.show('misc_popup');
	$('misc_popup').style.zIndex = 10000;
	$('misc_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	new Ajax.Updater('misc_list','ajax_sku_popups.php',{
		    parameters: str,
		    evalScripts:true
	});
}

function show_inventory(id_type,id,bid)
{
	if (bid == undefined || bid=='')
	{
		curtain(true);
		center_div('inv_popup');
		$('inv_popup').style.display = '';
		$('inv_popup').style.zIndex = 10000;
		$('inv_list').innerHTML = '<img src=/ui/clock.gif align=absmiddle> Loading...';
		new Ajax.Updater('inv_list','ajax_sku_popups.php?a=sku_get_inventory&'+id_type+'='+id+'&branch_id='+bid,{evalScripts:true});
	
	}
	else
	{
		curtain(true);
		center_div('inv_popup2');
		$('inv_popup2').style.display = '';
		$('inv_popup2').style.zIndex = 10000;
		$('inv_list2').innerHTML = '<img src=/ui/clock.gif align=absmiddle> Loading...';
		new Ajax.Updater('inv_list2','ajax_sku_popups.php?a=sku_get_inventory&'+id_type+'='+id+'&branch_id='+bid,{evalScripts:true});
	}
}

function hide_inventory_popups()
{
	Element.hide('inv_popup');//.style.display = 'none';
	Element.hide('inv_popup2');//.style.display = 'none';
	Element.hide('misc_popup'); //.style.display = 'none';
}



function get_item_sales_trend(id, use_parent){
	use_parent = use_parent || 0;
	
	curtain(true);
	center_div('misc_popup');
	Element.show('misc_popup');
	$('misc_popup').style.zIndex = 10000;
	$('misc_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	new Ajax.Updater('misc_list','ajax_sku_popups.php',{
		    parameters: 'a=sku_sales_trend&id='+id+'&use_parent='+use_parent,
		    evalScripts:true
	});
}

function goto_branch(is_arms) {
	var can_pop = true;
	if(is_arms){
		can_pop = check_login('goto_branch');
	}
	if (can_pop) {
        curtain(true);
		center_div('goto_branch_popup');
		Element.show('goto_branch_popup');
		$('goto_branch_popup').style.zIndex = 10000;
		$('goto_branch_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
		new Ajax.Updater('goto_branch_list', 'ajax_autocomplete.php',{
			parameters: 'a=ajax_allowed_login_branches',
			evalScripts:true,
			onComplete:function(){
				center_div('goto_branch_popup');
				Element.show('goto_branch_popup');
			}
		});
    }
}

function goto_branch_select()
{
	var url = "";
	if($('goto_branch_select') == undefined) url = '/login.php';
	else url = '/login.php?server='+encodeURIComponent($('goto_branch_select').value);
	document.location = url;
}

function login_as()
{
	var u = prompt('Enter username');
	if (u==undefined || u.trim()=='') return false;
	var url = '/login.php?login_as='+u;
	//document.location = url;
	new Ajax.Request(url,{
		onComplete:function(m)
		{
			if (m.responseText=='OK')
				document.location='/';
			else
				alert(m.responseText);
		}
	});
	return false;
}

function open_cc(url,id,branch)
{

	newwindow=window.open(url+"/counter_collection.php?remote=1&id="+id+"&branch="+branch,"Counter Collection","scrollbars=1,width=800,height=600");

	if (window.focus) {newwindow.focus()}
	return false;
}

function open_cc2(cc_server, url,branch_id,uid){
	var cc_url = cc_server+"/counter_collection.php?remote=1&id="+uid+"&branch="+branch_id+"&redir="+encodeURIComponent(url);
	alert(cc_url)
	var newwindow=window.open(cc_url,"Counter Collection","scrollbars=1,width=800,height=600");
	if (window.focus) {newwindow.focus()}
}

function open_from_dc(url,id,branch,title)
{

	newwindow=window.open(url+"&remote=1&id="+id+"&branch="+branch,title,"scrollbars=1,width=800,height=600");

	if (window.focus) {newwindow.focus()}
	return false;
}


function isNumeric(value) {
  if (value == null || !value.toString().match(/^[-]?\d*\.?\d*$/))
  	return false;
  return true;
}

function daysInMonth(iMonth, iYear)
{
	return 32 -  new Date(iYear, iMonth, 32).getDate();
}
 
function upper_lower_limit(dt){

	text=dt.value;
	var a = text.split("-");

	if(text && isNumeric(text) && text.length=='6'){
		var	year=text.slice(0,2);
		var	month=text.slice(2,4);
		var	day=text.slice(4,6);
		var	year='20'+year;

		var	cmonth=int(month)-1;

		if( day<=daysInMonth(cmonth, year) && month<13 && day>0 && month>0){
			dt.value=year+'-'+month+'-'+day;
		}
		else{
			alert('Invalid day/month format.');
			dt.value=date_now;
			dt.focus();
		}
		upper_lower_limit(dt);
	}
	else if(text && text.length>='8' && a.length==3){

		var	year=a[0];
		var	month=a[1];
		var	day=a[2];

		var	cmonth=int(month)-1;

		if( day<=daysInMonth(cmonth, year) && month<13 && day>0 && month>0){
       	    if (month.length==1) month="0"+month;
       	    if (day.length==1) day="0"+day;

			var input_date=new Date(dt.value);

			if(typeof lower_date_limit != 'undefined'){


				var lower_date = new Date(date_now);
					lower_date = lower_date.setDate(lower_date.getDate()-lower_date_limit);
					lower_date = new Date(lower_date);

				if (input_date<lower_date) {
					dt.value=date_now;
					var d = lower_date.getDate();
					var m = lower_date.getMonth()+1;
					var y = lower_date.getFullYear();
					alert("Cannot enter a date less than "+y+"-"+m+"-"+d );
					return;
				}


			}

			if(typeof upper_date_limit != 'undefined'){
				var upper_date = new Date(date_now);
					upper_date = upper_date.setDate(upper_date.getDate()+upper_date_limit);
					upper_date = new Date(upper_date);

				if (input_date>upper_date){
					dt.value=date_now;
					var d = upper_date.getDate();
					var m = upper_date.getMonth()+1;
					var y = upper_date.getFullYear();
					alert("Cannot enter a date more than "+y+"-"+m+"-"+d );
					return;
				}
			}
			
			
			dt.value=year+"-"+month+"-"+day;
			
		}
		else{
			alert('Invalid day/month format.');
			dt.value=date_now;
			dt.focus();
		}

	}
	else{
		alert('Invalid day/month format.');
		dt.value=date_now;
		dt.focus();
	}
}

function check_required_field(form){
	if(!form){  // if no give form, cannot validate
        alert('Un-expected error occur: no form element is given.');
        return false;
	}

	var passed = true;
	var radio_checkbox_list = {};
	
	$(form).getElementsBySelector('input.required[type="text"]', 'select.required', 'input.required[type="radio"]', 'input.required[type="checkbox"]').each(function(ele){    // loop all required <input>
		if(ele.type=='radio' || ele.type=='checkbox'){            		
    		if(!radio_checkbox_list[ele.name])	radio_checkbox_list[ele.name] = [];	// declare array
    		radio_checkbox_list[ele.name].push(ele);	// put element into array
    		return;
    	}else{
    		if($(ele).value.trim()=='' && passed && !ele.disabled){  // found value is empty
	            passed = false;
	            var key_in_method = 'enter';
	
	            if(ele.tagName.toLowerCase()=='select') key_in_method = 'select';
	            alert('Please '+key_in_method+' '+ele.title);   // prompt user error
	            $(ele).focus();    // move focus to input
	            return false;
			}
    	}		
	});
	
	if(passed && radio_checkbox_list){	// only perform this check if passed and got list
		for(var ele_name in radio_checkbox_list){	// loop each input name
			var ele_list = radio_checkbox_list[ele_name];	// loop input list
			var got_checked = false;
			for(var i=0; i<ele_list.length; i++){
				if(ele_list[i].checked){
					got_checked = true;
					break;
				}
			}
			if(!got_checked){
				alert('Please select '+ele_list[0].title);
				return false;
			}
		}
	}
	
	return passed;  // return validate result
}

// swap 2 element position
function swap_ele(a, b){
	// create a temporary element call "t" and put it in-front of "a" element
	var t = a.parentNode.insertBefore(document.createTextNode(''), a);
	// move "a" to in-front of "b"
	b.parentNode.insertBefore(a, b);
	// move "b" to in-front of "t"
	t.parentNode.insertBefore(b, t);
	// remove "t"
	t.parentNode.removeChild(t);

	return this;
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

function number_format (number, decimals, dec_point, thousands_sep) {
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

function toggle_select_all_opt(sel, selected){
	selected = selected ? true : false;
	for(var i=0; i<sel.options.length; i++){
		sel.options[i].selected = selected;
	}
}

function show_discount_help()
{
	msg = '';
	msg += "Sample input\n";
	msg += "-------------------------------------------------------------------------\n";
	msg += "10% => discount of 10 percent\n";
	msg += "10  => discount of "+ARMS_CURRENCY['symbol']+"10\n";
	msg += "10%+10 => discount 10%, follow by "+ARMS_CURRENCY['symbol']+"10\n";
	msg += "10+10% => discount "+ARMS_CURRENCY['symbol']+"10, then discount 10%\n";
	msg += "10%+10% => discount 10%, then follow discount again 10% (This is not 20%)\n";
	
	alert(msg);
}

function show_markup_help()
{
	msg = '';
	msg += "Sample input\n";
	msg += "------------\n";
	msg += "10% => add 10 percent\n";
	msg += "10  => add "+ARMS_CURRENCY['symbol']+"10\n";
	msg += "10%+10 => add 10%, follow by "+ARMS_CURRENCY['symbol']+"10\n";
	msg += "10+10% => add "+ARMS_CURRENCY['symbol']+"10, then 10%\n";

	alert(msg);
}

function validate_discount_format(discount_pattern){
	if(!discount_pattern)	return '';
	
	discount_pattern = discount_pattern.regex(/[^0-9\.%+]/g,'');
    discount_pattern = discount_pattern.regex(/\+$/,'');
    return discount_pattern;
}

function get_discount_amt(amt, discount_pattern, params){
	var total_discount_amt = 0;
	var discount_amt = 0;
	if(discount_pattern == '')	return 0;
	if(!params)	params = {};
	var currency_multiply = 0;
	var discount_by_value_multiply = 0;
	if(params['currency_multiply'])	currency_multiply = float(params['currency_multiply']);
	if(params['discount_by_value_multiply'])	discount_by_value_multiply = float(params['discount_by_value_multiply']);
	
	// check discount pattern
	discount_pattern = validate_discount_format(discount_pattern);
    var original_amt = amt;
    
	if (discount_pattern != ''){
		$A(discount_pattern.split("+")).each( function(r,idx) {
			if (r.indexOf("%")>0){	// discount by percentage
		        discount_amt = float(amt * float(r)/100);
			}
			else{	// discount by value
			    discount_amt = float(r);
				if(currency_multiply>0){	// multiply currency rate
					discount_amt = float(discount_amt*currency_multiply);
				}
				
				if(discount_by_value_multiply>0){	// maybe more than 1 branch
					discount_amt = float(discount_amt*discount_by_value_multiply);
				}
			}
			
			total_discount_amt += discount_amt;
			amt -= discount_amt;
		});
	}
	
	if(total_discount_amt > original_amt)	total_discount_amt = original_amt;	// cannot discount more than amt
	return total_discount_amt;
}

function strtotime (str, now) {
    // Convert string representation of date and time to a timestamp  
    // 
    // version: 1107.2516
    // discuss at: http://phpjs.org/functions/strtotime
    // +   original by: Caio Ariede (http://caioariede.com)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: David
    // +   improved by: Caio Ariede (http://caioariede.com)
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Wagner B. Soares
    // +   bugfixed by: Artur Tchernychev
    // %        note 1: Examples all have a fixed timestamp to prevent tests to fail because of variable time(zones)
    // *     example 1: strtotime('+1 day', 1129633200);
    // *     returns 1: 1129719600
    // *     example 2: strtotime('+1 week 2 days 4 hours 2 seconds', 1129633200);
    // *     returns 2: 1130425202
    // *     example 3: strtotime('last month', 1129633200);
    // *     returns 3: 1127041200
    // *     example 4: strtotime('2009-05-04 08:30:00');
    // *     returns 4: 1241418600
    var i, match, s, strTmp = '',
        parse = '';
 
    strTmp = str;
    strTmp = strTmp.replace(/\s{2,}|^\s|\s$/g, ' '); // unecessary spaces
    strTmp = strTmp.replace(/[\t\r\n]/g, ''); // unecessary chars
    if (strTmp == 'now') {
        return (new Date()).getTime() / 1000; // Return seconds, not milli-seconds
    } else if (!isNaN(parse = Date.parse(strTmp))) {
        return (parse / 1000);
    } else if (now) {
        now = new Date(now * 1000); // Accept PHP-style seconds
    } else {
        now = new Date();
    }
 
    strTmp = strTmp.toLowerCase();
 
    var __is = {
        day: {
            'sun': 0,
            'mon': 1,
            'tue': 2,
            'wed': 3,
            'thu': 4,
            'fri': 5,
            'sat': 6
        },
        mon: {
            'jan': 0,
            'feb': 1,
            'mar': 2,
            'apr': 3,
            'may': 4,
            'jun': 5,
            'jul': 6,
            'aug': 7,
            'sep': 8,
            'oct': 9,
            'nov': 10,
            'dec': 11
        }
    };
 
    var process = function (m) {
        var ago = (m[2] && m[2] == 'ago');
        var num = (num = m[0] == 'last' ? -1 : 1) * (ago ? -1 : 1);
 
        switch (m[0]) {
        case 'last':
        case 'next':
            switch (m[1].substring(0, 3)) {
            case 'yea':
                now.setFullYear(now.getFullYear() + num);
                break;
            case 'mon':
                now.setMonth(now.getMonth() + num);
                break;
            case 'wee':
                now.setDate(now.getDate() + (num * 7));
                break;
            case 'day':
                now.setDate(now.getDate() + num);
                break;
            case 'hou':
                now.setHours(now.getHours() + num);
                break;
            case 'min':
                now.setMinutes(now.getMinutes() + num);
                break;
            case 'sec':
                now.setSeconds(now.getSeconds() + num);
                break;
            default:
                var day;
                if (typeof(day = __is.day[m[1].substring(0, 3)]) != 'undefined') {
                    var diff = day - now.getDay();
                    if (diff == 0) {
                        diff = 7 * num;
                    } else if (diff > 0) {
                        if (m[0] == 'last') {
                            diff -= 7;
                        }
                    } else {
                        if (m[0] == 'next') {
                            diff += 7;
                        }
                    }
                    now.setDate(now.getDate() + diff);
                }
            }
            break;
 
        default:
            if (/\d+/.test(m[0])) {
                num *= parseInt(m[0], 10);
 
                switch (m[1].substring(0, 3)) {
                case 'yea':
                    now.setFullYear(now.getFullYear() + num);
                    break;
                case 'mon':
                    now.setMonth(now.getMonth() + num);
                    break;
                case 'wee':
                    now.setDate(now.getDate() + (num * 7));
                    break;
                case 'day':
                    now.setDate(now.getDate() + num);
                    break;
                case 'hou':
                    now.setHours(now.getHours() + num);
                    break;
                case 'min':
                    now.setMinutes(now.getMinutes() + num);
                    break;
                case 'sec':
                    now.setSeconds(now.getSeconds() + num);
                    break;
                }
            } else {
                return false;
            }
            break;
        }
        return true;
    };
 
    match = strTmp.match(/^(\d{2,4}-\d{2}-\d{2})(?:\s(\d{1,2}:\d{2}(:\d{2})?)?(?:\.(\d+))?)?$/);
    if (match != null) {
        if (!match[2]) {
            match[2] = '00:00:00';
        } else if (!match[3]) {
            match[2] += ':00';
        }
 
        s = match[1].split(/-/g);
 
        for (i in __is.mon) {
            if (__is.mon[i] == s[1] - 1) {
                s[1] = i;
            }
        }
        s[0] = parseInt(s[0], 10);
 
        s[0] = (s[0] >= 0 && s[0] <= 69) ? '20' + (s[0] < 10 ? '0' + s[0] : s[0] + '') : (s[0] >= 70 && s[0] <= 99) ? '19' + s[0] : s[0] + '';
        return parseInt(this.strtotime(s[2] + ' ' + s[1] + ' ' + s[0] + ' ' + match[2]) + (match[4] ? match[4] / 1000 : ''), 10);
    }
 
    var regex = '([+-]?\\d+\\s' + '(years?|months?|weeks?|days?|hours?|min|minutes?|sec|seconds?' + '|sun\\.?|sunday|mon\\.?|monday|tue\\.?|tuesday|wed\\.?|wednesday' + '|thu\\.?|thursday|fri\\.?|friday|sat\\.?|saturday)' + '|(last|next)\\s' + '(years?|months?|weeks?|days?|hours?|min|minutes?|sec|seconds?' + '|sun\\.?|sunday|mon\\.?|monday|tue\\.?|tuesday|wed\\.?|wednesday' + '|thu\\.?|thursday|fri\\.?|friday|sat\\.?|saturday))' + '(\\sago)?';
 
    match = strTmp.match(new RegExp(regex, 'gi')); // Brett: seems should be case insensitive per docs, so added 'i'
    if (match == null) {
        return false;
    }
 
    for (i = 0; i < match.length; i++) {
        if (!process(match[i].split(' '))) {
            return false;
        }
    }
 
    return (now.getTime() / 1000);
}

function is_array(input){
	return typeof(input)=='object'&&(input instanceof Array);
}

function rounding(amount){
	return round(amount * 2, 1)/2;
}

function showLocalImage(file_input, target_img){
	if(!file_input || !target_img)	return false;	// no file input or img element
	if(!FileReader)	return false;	// browser not supported
	
	var oFReader = new FileReader(), rFilter = /^(image\/bmp|image\/cis-cod|image\/gif|image\/ief|image\/jpeg|image\/jpeg|image\/jpeg|image\/pipeg|image\/png|image\/svg\+xml|image\/tiff|image\/x-cmu-raster|image\/x-cmx|image\/x-icon|image\/x-portable-anymap|image\/x-portable-bitmap|image\/x-portable-graymap|image\/x-portable-pixmap|image\/x-rgb|image\/x-xbitmap|image\/x-xpixmap|image\/x-xwindowdump)$/i;  
      
    oFReader.onload = function (oFREvent) {  
      target_img.src = oFREvent.target.result;  
    };  
    
    if (file_input.files.length === 0) { return; }  
    var oFile = file_input.files[0];  
    if (!rFilter.test(oFile.type)) { alert("You must select a valid image file!"); return; }  
    oFReader.readAsDataURL(oFile);
}

function in_array (needle, haystack, argStrict) {
    // Checks if the given value exists in the array  
    // 
    // version: 1109.2015
    // discuss at: http://phpjs.org/functions/in_array
    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: vlado houba
    // +   input by: Billy
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)
    // *     example 1: in_array('van', ['Kevin', 'van', 'Zonneveld']);
    // *     returns 1: true
    // *     example 2: in_array('vlado', {0: 'Kevin', vlado: 'van', 1: 'Zonneveld'});
    // *     returns 2: false
    // *     example 3: in_array(1, ['1', '2', '3']);
    // *     returns 3: true
    // *     example 3: in_array(1, ['1', '2', '3'], false);
    // *     returns 3: true
    // *     example 4: in_array(1, ['1', '2', '3'], true);
    // *     returns 4: false
    var key = '',
        strict = !! argStrict;
 
    if (strict) {
        for (key in haystack) {
            if (haystack[key] === needle) {
                return true;
            }
        }
    } else {
        for (key in haystack) {
            if (haystack[key] == needle) {
                return true;
            }
        }
    }
 
    return false;
}

function check_description(obj){
	
	var description = obj.value;
	if(description.indexOf('"')>=0){
		alert("Description cannot contains quote(\").");
		obj.value = description.replace(/"/g, "");
	}
}

function cloneEle(element) {
	var new_clone = document.createElement(element.tagName);
	new_clone = $(new_clone);
	
	$A(element.attributes).each(function(attribute) { 
		//if( attribute.name != 'style' ) 
		new_clone[attribute.name] = attribute.value; 
	});
	
	new_clone.className = element.className;
	
	//new_clone.setStyle( element.getStyles() );
	new_clone.update(element.innerHTML);
	
	return new_clone;
}

function toHTML(element) {
  if (typeof element=='string') element = $(element);  // IE needs that check with XML
  return Try.these(
    function() {
      var xmlSerializer = new XMLSerializer();
      return  element.nodeType == 4 ? element.nodeValue : xmlSerializer.serializeToString(element);
    },
    function() {
      return element.xml || element.outerHTML || cloneEle($(element)).wrap().up().innerHTML;
    }
  ) || '';
}

function day_diff(dt1, dt2){
	var diff = strtotime(dt2) - strtotime(dt1) //unit is seconds
	diff = int(diff/60/60/24) //contains days passed
	
	return int(diff);
}

function disable_sub_ele(ele, enable){
	var d = enable ? false : true;
	$(ele).getElementsBySelector("input","select","textarea").each(function(tmp){
		tmp.disabled = d;
	});
}

function set_changed_member_points(nric){
	if(!nric) return false;
	
	if(!confirm("Are you sure want to recalculate points for "+nric+"?")) return false;
	
	var url = "membership.php?a=member_points_changed&nric="+nric;
	
	new Ajax.Request(url,{
		onComplete:function(m)
		{
			if (m.responseText=='OK'){
				alert("Member ["+nric+"] has been marked as points recalculate.\n\nPlease check again in next 10 minutes.");
				$('span_points_'+nric).style.display = "";
			}else alert(m.responseText);
		}
	});
}

function check_user_profile_allow_mprice_list(obj)
{
	var arr_list = $$("li.user_profile_mprice_list");
	
	for(var i=0; i<arr_list.length; i++){
		if(obj.checked)
		{
			$(arr_list[i]).hide();			
		}
		else
		{
			$(arr_list[i]).show();
		}
	}
	
}

function validateTimestamp(timestamp){
	if (!/\d{4}\-\d{1,2}\-\d{1,2}/.test(timestamp)) {
        return false;
    }

    var temp = timestamp.split(/[^\d]+/);

    var year = parseFloat(temp[0]);
    var month = parseFloat(temp[1]);
    var day = parseFloat(temp[2]);
   
	if(month==4||month==6||month==9||month==11){
		if(day>30)	return false;
	}
	if(month==2){
		if(day>28&&year%4!=0)	return false;
	}

    return (month<13 && month>0) && (day<32 && day>0);
}

function strip2(number) {
	number = parseFloat(number);
    return number.toPrecision(12);
}

// check field must be positive figure
function positive_check(obj) {
	return true;
	
	if (obj.value=='') return;
	
	obj.value = float(obj.value);
	if(obj.value < 0){
		alert("Please provide positive figure.");
		obj.value = '';
	}
}

function show_sku_image_div(path, time=0, zindex=100){
	path = escape(path);
	//to solve problem if img src is on another server/host
	path = path.replace(/%3A/g,':');
	if(time) path = path+"?t="+time;
	var img_str = "<img src='"+path+"' width='640' onload=\"center_div('img_full');\">";
	popup_div('img_full', img_str, zindex);
}

function is_new_id(tmp_id){
	if(tmp_id > 1000000000) return true;
	else return false;
}
function goto_offline(url) {
	if (!confirm('View document in offline server. Proceed?')) return false;
	window.open(url+'&skip_login_check=1');
}

/*function ajax_search_vendor_gst_status(vid, callback){
	if(!vid) return false;
	
	var parms = "a=ajax_search_vendor_gst_status&vendor_id="+vid;
	new Ajax.Request("ajax_autocomplete.php",{
		method:'post',
		parameters: parms,
	    evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);						
		},
		onSuccess: function (m) {
			try{
				eval("var json = "+m.responseText);
			}catch(ex){
				alert(m.responseText);
				return;
			}

			if(typeof(callback) != undefined){
				callback(json);
				return;
			}
			
			if(json['gst_register'] != undefined){
				return json['gst_register'];
			}else{
				return false;
			}
		},
	});
}*/

function mst_gst_info_changed(){
	if(is_gst_active == 0) return;

	// found it's inherit, output tax use from category
	var dtl_input_tax_code, dtl_input_tax_rate;
	if(document.f_a['mst_input_tax'].value == -1){
		dtl_input_tax_code = category_gst['input_tax_code'];
		dtl_input_tax_rate = category_gst['input_tax_rate'];
	}else{
		var mst_input_tax = document.f_a['mst_input_tax'].value;
		dtl_input_tax_code = gst_code_list[mst_input_tax];
		dtl_input_tax_rate = gst_rate_list[mst_input_tax];
	}
	
	$('new_items').getElementsByClassName("dtl_input_tax").each(function(ele){
		if(dtl_input_tax_code != undefined && dtl_input_tax_code != null) ele.options[0].text = "Inherit (Follow SKU: "+dtl_input_tax_code+" ["+dtl_input_tax_rate+"%])";
		else ele.options[0].text = "Inherit (Follow SKU)";
	});
	
	// found it's inherit, input tax use from category
	var dtl_output_tax_code, dtl_output_tax_rate;
	if(document.f_a['mst_output_tax'].value == -1){
		dtl_output_tax_code = category_gst['output_tax_code'];
		dtl_output_tax_rate = category_gst['output_tax_rate'];
	}else{
		var mst_output_tax = document.f_a['mst_output_tax'].value;
		dtl_output_tax_code = gst_code_list[mst_output_tax];
		dtl_output_tax_rate = gst_rate_list[mst_output_tax];
	}
	
	$('new_items').getElementsByClassName("dtl_output_tax").each(function(ele){
		if(dtl_output_tax_code != undefined && dtl_output_tax_code != null) ele.options[0].text = "Inherit (Follow SKU: "+dtl_output_tax_code+" ["+dtl_output_tax_rate+"%])";
		else ele.options[0].text = "Inherit (Follow SKU)";
	});
	
	var dtl_inclusive_tax;
	if(document.f_a['mst_inclusive_tax'].value == "inherit"){
		dtl_inclusive_tax = category_gst['inclusive_tax'].toUpperCase();
	}else{
		dtl_inclusive_tax = document.f_a['mst_inclusive_tax'].value.toUpperCase();
	}
	
	$('new_items').getElementsByClassName("dtl_inclusive_tax").each(function(ele){
		if(dtl_inclusive_tax != undefined && dtl_inclusive_tax != null) ele.options[0].text = "Inherit (Follow SKU: "+dtl_inclusive_tax+")";
		else ele.options[0].text = "Inherit (Follow SKU)";
	});
}

function extractNumber(obj, decimalPlaces, allowNegative){
	var temp = obj.value;
	
	// avoid changing things if already formatted correctly
	var reg0Str = '[0-9]*';
	if (decimalPlaces > 0) {
		reg0Str += '\\.?[0-9]{0,' + decimalPlaces + '}';
	} else if (decimalPlaces < 0) {
		reg0Str += '\\.?[0-9]*';
	}
	reg0Str = allowNegative ? '^-?' + reg0Str : '^' + reg0Str;
	reg0Str = reg0Str + '$';
	var reg0 = new RegExp(reg0Str);
	if (reg0.test(temp)) return true;

	// first replace all non numbers
	var reg1Str = '[^0-9' + (decimalPlaces != 0 ? '.' : '') + (allowNegative ? '-' : '') + ']';
	var reg1 = new RegExp(reg1Str, 'g');
	temp = temp.replace(reg1, '');

	if (allowNegative) {
		// replace extra negative
		var hasNegative = temp.length > 0 && temp.charAt(0) == '-';
		var reg2 = /-/g;
		temp = temp.replace(reg2, '');
		if (hasNegative) temp = '-' + temp;
	}
	
	if (decimalPlaces != 0) {
		var reg3 = /\./g;
		var reg3Array = reg3.exec(temp);
		if (reg3Array != null) {
			// keep only first occurrence of .
			//  and the number of places specified by decimalPlaces or the entire string if decimalPlaces < 0
			var reg3Right = temp.substring(reg3Array.index + reg3Array[0].length);
			reg3Right = reg3Right.replace(reg3, '');
			reg3Right = decimalPlaces > 0 ? reg3Right.substring(0, decimalPlaces) : reg3Right;
			temp = temp.substring(0,reg3Array.index) + '.' + reg3Right;
		}
	}
	
	obj.value = temp;
}

function ajax_request(url,option) {
	if (is_popup_login) return;
	ajax_url=url;
	ajax_option=option;

	if (check_login()) {
		new Ajax.Request(url, option);

		ajax_url=null;
		ajax_option=null;
    }
}

function check_login(callback, extra_params) {
	if (is_popup_login) return;
	if (callback) login_callback=callback;
	if(extra_params == undefined)	extra_params = {};
	
	var now = strtotime('now');
	
	// Last success check was 5 sec ago, return true
	if(last_success_login_check > 0 && now - last_success_login_check < 5){
		return true;
	}
	
	var result=true;
	var params = 'a=check_login';
	if(extra_params['str_params'])	params += '&'+extra_params['str_params'];
	
	new Ajax.Request('ajax_login.php', {
		asynchronous: false,
		parameters: params,
		onComplete: function(msg){
			var str = msg.responseText.trim();
			if (str!='OK') {
                result=false;
            }
		}
	});

	if (!result && !extra_params['no_pop_login']) popup_login(true);

	if(result){
		// Record last successful login check
		last_success_login_check = strtotime('now');
	}
	
	return result;
}

function popup_login(is_show) {

	if (document.getElementById('popup_login')==undefined) {
        var str='<div id="popup_login" style="display:none;border: 3px solid rgb(0, 0, 0);padding: 10px;background:rgb(255, 255, 255) none repeat scroll 0% 0%;position:absolute;z-index:10002;">'+
			'<font id="popup_login_errmsg" color="red"></font>'+
			'<form method="post" name="ajax_login" onSubmit="return do_ajax_login();">'+
				'<table cellpadding="0" cellspacing="10" border="0" style="border:1px solid #ccc;width:280px;">'+
				'<tr>'+
				'<th colspan="2"><h3>Session Timeout. Please Login</h3></th>'+
				'</tr>'+
				'<tr>'+
				'	<th align="left">Login ID</th><td><input name="u" size="20" type="password" autocomplete="off"></td>'+
				'</tr>'+
				'<tr>'+
				'	<th align="left">Password</th><td><input name="p" size="20" type="password" autocomplete="off"></td>'+
				'</tr>'+
				'<tr>'+
				'	<th colspan="2"><input type="submit" value="Login"></th>'+
				'</tr>'+
				'</table>'+
			'</form>'+
		'</div>';

		new Insertion.Top(document.getElementsByTagName('body')[0], str);
		new Insertion.Top(document.getElementsByTagName('body')[0], '<div id="curtain_login" style="position:absolute;display:none;z-index:10001;background:#000;opacity:0.1;"></div>');
    }

	document.ajax_login['u'].value="";
	document.ajax_login['p'].value="";

	$('popup_login_errmsg').update('');
	if(is_show){
		center_div($('popup_login').show());
		curtain(true,'curtain_login');
		document.ajax_login['u'].focus();
		is_popup_login=true;
	}else{
		$('popup_login').hide();
		curtain(false,'curtain_login');
		is_popup_login=false;
	}
}

function do_ajax_login() {
	var u=document.ajax_login['u'].value;
	var p=document.ajax_login['p'].value;

	var result=true;
	new Ajax.Request('ajax_login.php', {
		asynchronous: false,
		parameters: 'a=login&u='+u+'&p='+p,
		onComplete: function(msg){
			var str = msg.responseText.trim();
			if (str!='OK') {
				$('popup_login_errmsg').update(str);
            }
			else{
				popup_login(false);
				if(ajax_url!=null) ajax_request(ajax_url,ajax_option);

				if(login_callback!=null){
					if (typeof window[login_callback] == 'function') { window[login_callback]() };
					login_callback=null;
				}
			}
		}
	});

	return false;
}

function check_exceed_max_timestamp(date){
	var max_date = new Date(2037,11,31);
	if((date.getTime() > max_date.getTime())){
		return true;	// exceeded
	}
	return false;
}

function toYMD(date_object){
	var y = date_object.getFullYear();
	var m = date_object.getMonth()+1;
	var d = date_object.getDate();
	
	if(m < 10)	m = '0'+m;
	if(d < 10) d = '0'+d;
	
	return y+'-'+m+'-'+d;
}

function htmlToElement(html) {
    var template = document.createElement('template');
    html = html.trim(); // Never return a text node of whitespace as the result
    template.innerHTML = html;
    return template.content.firstChild;
}

function isAlphaNumeric(str) {
  var code, i, len;

  for (i = 0, len = str.length; i < len; i++) {
    code = str.charCodeAt(i);
    if (!(code > 47 && code < 58) && // numeric (0-9)
        !(code > 64 && code < 91) && // upper alpha (A-Z)
        !(code > 96 && code < 123)) { // lower alpha (a-z)
      return false;
    }
  }
  return true;
}

function calculate_text_length(obj){
	var msg = $(obj).value;
	var have_unicode = contain_unicode(msg);
	/*for (var i = 0, n = msg.length; i < n; i++) {
		if (msg[i].charCodeAt() > 255) {
			have_unicode = true; 
			break;
		}
	}*/
	var ret = msg.length;
	if (have_unicode && ret > 63) ret += 7;
	if (!have_unicode && ret > 153) ret += 7;	
	return ret;
}

function my_setCookie(cname, cvalue, exdays) {
  var d = new Date();
  d.setTime(d.getTime() + (exdays*24*60*60*1000));
  var expires = "expires="+ d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function my_getCookie(cname) {
  var name = cname + "=";
  var decodedCookie = decodeURIComponent(document.cookie);
  var ca = decodedCookie.split(';');
  for(var i = 0; i <ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

function scroll_to_child_obj(parent_div, child_obj){
	var topPos = child_obj.offsetTop;
	parent_div.scrollTop = topPos;
}

var GLOBAL_MODULE = {
	show_trans_detail: function trans_detail(receipt_ref_no, counter_id, date, pos_id, branch_id)
	{
		var params = {a: 'item_details'};
		if(receipt_ref_no)	params['receipt_ref_no'] = receipt_ref_no;
		else{
			params['counter_id'] = counter_id;
			params['pos_id'] = pos_id;
			params['date'] = date;
			if(branch_id)	params['branch_id'] = branch_id;
		}
		
		curtain(true);
		center_div('div_item_details_popup');

		$('div_item_details_popup').show();
		$('div_item_details_popup_content').update(_loading_+' Please wait...');

		
		new Ajax.Updater('div_item_details_popup_content', 'counter_collection.php',
		{
			method: 'post',
			parameters: params
		});
	},
	hide_item_details: function(){
		if(typeof(hide_item_details) == 'undefined'){
			default_curtain_clicked();
		}else{
			hide_item_details();
		}
		
	},
	show_wait_popup: function(params){
		if(!params)	params = {};
		no_need_effect = 0;
		if(params['no_need_effect'])	no_need_effect = 1;
		curtain(true, 'curtain2', no_need_effect);
		center_div($('div_global_wait_popup').show());
	},
	hide_wait_popup: function(params){
		if(!params)	params = {};
		no_need_effect = 0
		if(params['no_need_effect'])	no_need_effect = 1;
		$('div_global_wait_popup').hide();
		curtain(false, 'curtain2', no_need_effect);
	}
}

Ajax.Responders.register({
	onCreate: function () {
		if(Ajax.activeRequestCount > 0){
			var div = $('div_top_ajax_loading');
			var dimensions = $(div).getDimensions();

			//$(div).style.top = int(document.body.scrollTop)+'px';
			$(div).style.left = int(document.body.scrollLeft + (window.innerWidth-int(dimensions.width))/2)+'px';
			$(div).show();
		}
	},
	onException: function(){
		 //console.log("onException");
		 //console.log("activeRequestCount = "+Ajax.activeRequestCount);
		 Ajax.activeRequestCount--;
		 this.onComplete();
	},
	onComplete: function () {
	  //console.log("onComplete");
	  //console.log("activeRequestCount = "+Ajax.activeRequestCount);
	  if(Ajax.activeRequestCount <= 0){
		  $('div_top_ajax_loading').hide();
	  }
	}
});
