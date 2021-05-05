<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title> {$PAGE_TITLE}</title>

{config_load file="site.conf"}
{config_load file="common.conf"}
<link rel="stylesheet" href="{#SITE_CSS#}" type="text/css">
<link type="text/css" href="sop/include/css/smoothness/jquery-ui-1.8.5.custom.css" rel="stylesheet" />
<script type="text/javascript" src="sop/include/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="sop/include/js/jquery-ui-1.8.5.custom.min.js"></script>


<style>
{literal}
.no_close a.ui-dialog-titlebar-close{
	display:none;
}
{/literal}
</style>

<script type="text/javascript">
var LOADING = '<img src="/ui/clock.gif" align="absmiddle">';
var custom_alert;

{literal}

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

// convert an input box to integer
function mi(obj,z) {
	if (obj.value=='') return;

	obj.value = int(obj.value);
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


// convert an input box to float
function mf(obj,n,z) { // n = rounding decimal place, z = whether return emtpy for zero
    if (obj.value=='') return;

	if (n == undefined)
		obj.value = round2(obj.value);
	else
	    obj.value = round(obj.value,n);

    if (z == 1 && obj.value==0) obj.value = '';
}

function round2(num)
{
	return round(num,2);
}


function round(num,n)
{
	if(!n)  n = 0;
	var num = float(num);
    return float(Math.round(num*Math.pow(10,n))/Math.pow(10,n)).toFixed(n);
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
};

function create_error_html(msg){
    var text ='<div class="ui-state-error ui-corner-all" style="padding: 0 .7em;">';
    text +=	'<p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>';
	text += msg +'</p></div>';
	return text;
}

function create_info_html(msg){
	var text = '<div class="ui-state-highlight ui-corner-all" style="padding: 0 .7em;">';
	text += '<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>';
	text += msg+'</p></div>';
	return text;
}

var Class = {
  create: function() {
    return function() {
      this.initialize.apply(this, arguments);
    }
  }
}

var notify_dialog = Class.create();
notify_dialog.prototype = {
	dialog: undefined,
	initialize: function(div_id){   // constructor accept script to pass in div id
	    var new_div = $("<div>");   // create a new <div>
	    if(div_id)  $(new_div).attr('id', div_id);  // assign ID if it is given
        this.dialog = $(new_div).dialog({
			autoOpen: false,    // default dont popup
			width: 500, // set the width
			minHeight: 150,    // set the height
			modal: true,    // if set to true, will auto create an overlay curtain behind this div
			resizable: false,
			closeOnEscape: false,
			dialogClass: 'no_close',
			close: function(event, ui){
			    event.stopPropagation();    // this is the final event, prevent the event to continue triggle
			}
		});
		return this;
	},
	reset: function(){
	    // reset options
        $(this.dialog).dialog( "option", "closeOnEscape", false)   // default cannot close by escape
                    .unbind( "dialogclose");    // unbind the after close event
	},
    alert: function(msg, title, callback){    // custom made alert()
        if(!title)  title = 'Error';
        this.show_dialog(create_error_html(msg), title, callback);
	},
	info: function(msg, title, callback){
	    if(!title)  title = 'Information';
	    this.show_dialog(create_info_html(msg), title, callback);
	},
	show_dialog: function(msg, title, callback){
        this.reset();

	    if(!title)  title = 'Dialog';    // if no title given, assign the default title
        $(this.dialog).dialog("option", "title", title)   // assign title
				.dialog("option", "buttons",  {
					"Close": function() {
						$(this).dialog("close");
					}
				})
				.bind( "dialogclose", function(event, ui) {
				  if(callback)    callback(); // triggle callback if given
				})
			    .dialog("option", "closeOnEscape", true)   // turn on close by escape
			    .html(msg)   // assign error message
			    .dialog('open')    // prompt the dialog
			    .focus();   // move focus to the dialog
		this.centerlize();
	},
	prompt_in_progress: function(msg, title){  // custome notify popup
	    this.reset();
	    
	    if(!title)  title = 'In Progress…'    // if no title given, assign the default title
        $(this.dialog)
			.dialog("option", "title", title)   // assign title
			.dialog("option", "buttons", [])    // make no button so user cannot close it
			.html(msg)  // put the body message
			.dialog('open')    // prompt the dialog
			.focus();   // move focus to the dialog
	},
	close: function(){  // shortcut function to call dialog close
		$(this.dialog).dialog('close');    // close dialog
	},
	centerlize: function(){
		$(this.dialog).dialog( "option", "position", { my: "center center" });
	}
}

$(function(){
	// set all date picker default settings
	$.datepicker.setDefaults({
		changeMonth: true,  // show montb dropdown
		changeYear: true,   // show year dropdown
		showOn: 'both',  // show calendar when click on input or image
		dateFormat: 'yy-mm-dd',
		buttonImageOnly: true,
		buttonImage: '/ui/calendar.gif',
		buttonText: 'Select Date',
		showAnim: 'show'
	});

	// global event when ajax start and stop
	$("#div_top_right_loading").ajaxStart(function(){
		var doc_center = int($(document).width())/2;    // get the document center point
		var ele_width = int($(this).width());   // get this element width
		var left_pos = doc_center-(ele_width/2);    // find the left position
		$(this).css('left', left_pos).show();   // move postion and show
	}).ajaxStop(function(){$(this).hide();});   // hide when all ajax is stop
	
	// create the custom alert
	custom_alert = new notify_dialog('div_custom_alert');
});

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

{/literal}
</script>
</head>

<body>
<div class="body">
