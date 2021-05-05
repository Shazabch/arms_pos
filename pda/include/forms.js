/*
10/11/2011 2:46:11 PM Justin
- Amended to have those required functions only.

4/25/2013 4:47 PM Justin
- Added new function "positive_check".

5/20/2013 3:53 PM Justin
- Temporary disable positive_check.
*/
var _loading_ = '<img src=/ui/clock.gif align=absmiddle> Loading...';

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

function trim (str) {
  while (str.charAt(0) == ' ')
    str = str.substring(1);
  while (str.charAt(str.length - 1) == ' ')
    str = str.substring(0, str.length - 1);
  return str;
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

function isNumeric(value) {
  if (value == null || !value.toString().match(/^[-]?\d*\.?\d*$/))
  	return false;
  return true;
}

function daysInMonth(iMonth, iYear)
{
	return 32 -  new Date(iYear, iMonth, 32).getDate();
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

function number_format(number, decimals, dec_point, thousands_sep) {
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

function is_array(input){
	return typeof(input)=='object'&&(input instanceof Array);
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