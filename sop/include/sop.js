var _loading_ = '<img src="/ui/clock.gif" align="absmiddle"> Loading...';
var LOADING = '<img src="/ui/clock.gif" align="absmiddle">';
var custom_alert = undefined;

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
   
// dom ready load
$(function(){
	$('.ui-button, #menu a').live('mouseover',function(){
		$(this).addClass("ui-state-hover");
	}).live('mouseout',	function(){
		$(this).removeClass("ui-state-hover ui-state-active");
	}).live('mousedown', function(){
		$(this).addClass("ui-state-active");
	}).live('mouseup',function(){
		$(this).removeClass("ui-state-active");
	});


	// menu setup
	$('#menu ul, #menu a').addClass("ui-corner-all");

	$('#menu ul ul > li:has(ul) > a:first-child').prepend('<span class="ui-icon ui-icon-triangle-1-e right"></span>');

	$('#menu ul ul ul').each(function(){
		// auto margin for sub menus
		$(this).css('margin-left',$(this).parent().width());
	});

	// stupid IE require this
	$('#menu li ul ul ul ul').hide();
	$('#menu li ul ul ul').hide();
	$('#menu li ul ul').hide();
	$('#menu li ul').hide(); // other browser only need this.

	$('#menu li:has(ul)').mouseenter(function(){
		$(this).children('ul').show();
	}).mouseleave(function(){
		$(this).children('ul').hide();
	});
	$('#menu ul ul').mouseenter(function(){
		$(this).prev().addClass('ui-state-default');
	}).mouseleave(function(){
		$(this).prev().removeClass('ui-state-default');
	});

	/*$('a.ajax').live('click',function(){
		return launch_url(this);
	});*/
	
	// create the custom alert
	custom_alert = new notify_dialog('div_custom_alert');
	
	// global event when ajax start and stop
	$("#div_top_right_loading").ajaxStart(function(){
		var doc_center = int($(document).width())/2;    // get the document center point
		var ele_width = int($(this).width());   // get this element width
		var left_pos = doc_center-(ele_width/2);    // find the left position
		$(this).css('left', left_pos).show();   // move postion and show
	}).ajaxStop(function(){$(this).hide();});   // hide when all ajax is stop
});



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
        if(!title)  title = 'SOP Error';
        this.show_dialog(create_error_html(msg), title, callback);
	},
	info: function(msg, title, callback){
	    if(!title)  title = 'SOP Information';
	    this.show_dialog(create_info_html(msg), title, callback);
	},
	show_dialog: function(msg, title, callback){
        this.reset();

	    if(!title)  title = 'SOP Dialog';    // if no title given, assign the default title
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
	},
	prompt_in_progress: function(msg, title){  // custome notify popup
	    this.reset();
	    
	    if(!title)  title = 'In Progress...'    // if no title given, assign the default title
        $(this.dialog)
			.dialog("option", "title", title)   // assign title
			.dialog("option", "buttons", [])    // make no button so user cannot close it
			.html(msg)  // put the body message
			.dialog('open')    // prompt the dialog
			.focus();   // move focus to the dialog
	},
	close: function(){  // shortcut function to call dialog close
		$(this.dialog).dialog('close');    // close dialog
	}
}

var AUTOCOMPLETE_USER = Class.create();
AUTOCOMPLETE_USER.prototype = {
	selected_user_id: 0,
	user_info: {},
	form: undefined,
	user_id_input_name: 'user_id',
	search_input: undefined,
	add_button: undefined,
	container: undefined,
	user_templates: '',
	current_list: [],
	view_only: 0,
	initialize: function(params){
	    /*if(!params['form']){    // must have form
			custom_alert.alert('Cannot found form element.', 'User Autocomplete Error');
			return false;
		}else   this.form = params['form'];*/

		if(!params['search_input']){    // must have search_input
            custom_alert.alert('Cannot found search input.', 'User Autocomplete Error');
			return false;
		}else   this.search_input = params['search_input'];

		if(!params['add_button']){  // must have add_button
            custom_alert.alert('Cannot found add button.', 'User Autocomplete Error');
			return false;
		}else   this.add_button = params['add_button'];

		if(!params['container']){   // must have container
            custom_alert.alert('Cannot found item container.', 'User Autocomplete Error');
			return false;
		}else   this.container = params['container'];

	    if(params['user_id_input_name'])	this.user_id_input_name = params['user_id_input_name'];
	    if(params['user_templates']){   // custom templates
	        // remember it must have following element in custom templates, else error will occur in runtime
			// <div class="div_autocomplete_user">
			// <input class="inp_user_id">
			// <span class="span_autocomplete_username">
			// <img class="img_autocomplete_delete">
            this.user_templates = params['user_templates'];
		}else{  // default templates
            this.user_templates = '<div class="div_autocomplete_user"><input type="hidden" name="'+this.user_id_input_name+'[]" class="inp_user_id" /><span class="span_autocomplete_username">user</span><div style="float:right;"><img src="/ui/closewin.png" class="clickable img_autocomplete_delete" title="Delete" /></div></div>';
		}
		if(params['view_only'])	this.view_only = params['view_only'];
		
		// disabled the input first, loading user list by ajax
		$(this.add_button).attr('disabled', true);
		$(this.search_input).attr('disabled', true)
		                    .val('Loading...');
        if(params['current_list']){ // store preset list
		    this.current_list = params['current_list'];
		}
		this.load_autocomplete_user_list(); // call ajax to load userlist
		
		if(this.view_only){ // view only mode
		    
		}else{   // edit mode
		    // event when user click delete
            $(this.container).find('img.img_autocomplete_delete').live('click', function(){
			    var parent_ele = $(this).parent().get(0);   // get the parent
			    while(parent_ele){    // loop parebt until it found the real div container
				    if($.trim(parent_ele.tagName).toLowerCase()=='div'){    // check is it <div>
		                if($(parent_ele).hasClass('div_autocomplete_user')){    // found the container
							break;  // break the loop
						}
					}
		            parent_ele = $(parent_ele).parent().get(0);
				}
				if(parent_ele)	$(parent_ele).remove(); // remove the container
			});
		}
		
		return this;
	},
	load_autocomplete_user_list: function(){
	    var module = this;
		var view_only = this.view_only;
		
		$.get('autocomplete.php?a=load_autocomplete_user_list', function(data){
            var ret = {};
		    var err_msg = '';

		    try{
                ret = $.parseJSON(data); // try decode json object

                if(ret['ok'] && ret['user_list']){ // got ok return mean save success
                    var user_list_obj = ret['user_list'];
                    for(var i=0; i<user_list_obj.length; i++){  // loop data and store in javascript object
		        	    var r = user_list_obj[i];

						module.user_info[r['id']] = {
							'id': r['id'],
							'u': r['value']
						};
					}

					
					if(view_only){
                        // remove the loading word
						$(module.search_input).val('');
					}else{
					    // initialize autocomplete
                        $(module.search_input).autocomplete({
							source: user_list_obj,  // source list to be search, pattern [{value:'x', label:'x'}, {value:'x', label:'x'}]
							select: function(event, ui){    // after user select a user
							    var selected_user_id = 0;
							    if(ui.item){    // if user got select user, not click other place
				                    selected_user_id = ui.item.id;
								}
							    module.selected_user_id = selected_user_id;  // store the selected user info
							}
						});

						// event when user click add user
						$(module.add_button).click(function(){
				            module.add_user();    // call function to add currently selected user
						});
						
						// enable back the input
						$(module.add_button).attr('disabled', false);
						$(module.search_input).attr('disabled', false).val('');
					}
					
					if(module.current_list){    // add current user list
					    var current_list = module.current_list;
						for(var i=0; i<current_list.length; i++){
							module.add_user(current_list[i]);
						}
					}
	                return;
				}else{  // save failed
				    if(ret['failed_reason'])	err_msg = ret['failed_reason'];
				    else    err_msg = data;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = data;
			}

		    // prompt the error
		    alert(err_msg);
		});
	},
	add_user: function(user_id){   // event when user click on "Add" button
	    if(!user_id)    user_id = this.selected_user_id;
		if(!user_id){ // no user id is select
			custom_alert.alert('Please search and select an user first.', 'User Autocomplete Error');
			return false;
		}

		var username = this.user_info[user_id]['u'];    // get username by id

		//alert(user_id+' > '+username);
		// check duplicate
		var added_user = $(this.container).find('input.inp_user_id[value="'+user_id+'"]').get(0);
		if(added_user){
            custom_alert.alert('Selected user "'+username+'" already in the list.', 'User Autocomplete Duplicated');
			return false;
		}
		// create new element
		var new_user_div = $(this.user_templates);
		$(new_user_div).find('input.inp_user_id').val(user_id); // assign user id
		$(new_user_div).find('span.span_autocomplete_username').text(username); // assign user name

		if(this.view_only){ // view only mode
            $(new_user_div).find('img.img_autocomplete_delete')
						.css('opacity', 0.2)
						.attr('title','')
						.removeClass('clickable');
		}  
		$(this.container).append(new_user_div);   // append to the container list
		this.selected_user_id = 0;  // reset the selected user
		if(!$(this.search_input).attr('disabled'))  $(this.search_input).val('').focus();   // empty the input box and reselect it
	},
	delete_user: function(user_id, params){ // function to delete user
	    if(!params) params = {};    // initial object, so it won't facing error in later

		// use user id to find element to delete
		if(!user_id){   // no user id give
		    if(!params['hide_error'])	custom_alert.alert('Cannot found user id to delete.', 'User Autocomplete Error');
            return false;   
		}    
		
		// find user input in container
		var inp_user = $(this.container).find('input.inp_user_id[value="'+user_id+'"]').get(0);
		if(!inp_user){  // cannot find this user in the list
            if(!params['hide_error'])	custom_alert.alert('Invalid user id to delete.', 'User Autocomplete Error');
            return false;
		}
		
		var parent_ele = $(inp_user).parent().get(0);   // get the parent
	    while(parent_ele){    // loop parent until it found the real div container
		    if($.trim(parent_ele.tagName).toLowerCase()=='div'){    // check is it <div>
                if($(parent_ele).hasClass('div_autocomplete_user')){    // found the container
					break;  // break the loop
				}
			}
            parent_ele = $(parent_ele).parent().get(0);
		}
		if(parent_ele)	$(parent_ele).remove(); // remove the user element from container
	}
};


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

function curtain(v)
{
	if(v){
		// create a top top level div
		$('#curtain').css({
			top: 0,
			left: 0,
			width: document.body.scrollWidth,
			height: document.body.scrollHeight,
		}).fadeIn('slow');
	}
	else{
	    $('#curtain').fadeOut('slow');
	}
}

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

function check_required_input(form){
	if(!form){  // if no give form, cannot validate
        custom_alert.alert('Un-expected error occur: no form element is given.');
        return false;
	}
	
	var passed = true;
	$(form).find(":input[type=text].required, select.required").each(function(i){    // loop all required <input>
		if($.trim($(this).val())==''){  // found value is empty
            passed = false;
            var key_in_method = 'enter';
            
            if($.trim(this.tagName).toLowerCase()=='select') key_in_method = 'select';
            alert('Please '+key_in_method+' '+$(this).attr('title'));   // prompt user error
            $(this).focus();    // move focus to input
            return false;
		}
	});
	return passed;  // return validate result
}

function form_disable(form){
	if(!form)   return false;
	$(form).find('input, textarea, select').attr('disabled', true);
}

function form_enable(form){
	if(!form)   return false;
	$(form).find('input, textarea, select').attr('disabled', false);
}

function blink_element(ele, color, callback){
    if(!ele) return false;   // no element to handle
    if(!color)  color = 'red';  // default color is red

	var default_color = $(ele).css('color');    // save default color first
    $(ele).animate({'color': color}, 'slow', function(){   // show blink color
		$(this).animate({'color': default_color}, 'slow', function(){  // show back the default color
		    if(callback)    callback(this); // fire the callback function if provided
		});
	});
}

function RGBToHex(rgb) {
	var hex = [
		rgb.r.toString(16),
		rgb.g.toString(16),
		rgb.b.toString(16)
	];
	$.each(hex, function (nr, val) {
		if (val.length == 1) {
			hex[nr] = '0' + val;
		}
	});
	return hex.join('');
}
