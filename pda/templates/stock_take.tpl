{*
5/24/2013 11:56 AM Justin
- Enhanced to activate save function while press enter on date, location or shelf.

2/24/2014 4:24 PM Andy
- Fix the variable bug. (sometime "loc" sometime "location").

1/24/2015 3:58 PM Justin
- Bug fixed on system will allow user to save whitespacing date, location or shelf. 

9/22/2020 10:09 AM William
- Enhanced to show error message.

11/04/2020 10:22 AM Sheila
- Fixed title, table and form css

11/05/2020 11:55 AM Sheila
- Fixed breadcrumbs

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields
*}

{include file='header.tpl'}


<script type="text/javascript">
var do_type = '{$do_type}';
var php_self = '{$smarty.server.PHP_SELF}';
{literal}
function submit_form(){
	document.f_a['date_t'].value = document.f_a['date_t'].value.trim();
	document.f_a['location'].value = document.f_a['location'].value.trim();
	document.f_a['shelf'].value = document.f_a['shelf'].value.trim();
	if(document.f_a['date_t'].value==''){
		notify('error','Please enter Date','center')
		document.f_a['date_t'].focus();
		return false;
	}else if(document.f_a['location'].value=='')
	{
			notify('error','Please key in Location','center')
      document.f_a['location'].focus();
  		return false;
  }else if(document.f_a['shelf'].value=='')
	{
			notify('error','Please key in Shelf','center')
      document.f_a['shelf'].focus();
  		return false;
  }
  
  var result =  validateTimestamp(document.f_a['date_t'].value);
  if(!result)
  {
  		notify('error','Invalid Date Format','center')
      return;
  }
  document.f_a.submit();
  //chk_date();
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
function back_home()
{
  window.location="home.php";
}

// function when user press enter
function form_keypress(event){
	if (event == undefined) event = window.event;
	if(event.keyCode==13){  // enter
		this.pagenum = 1;
		
		submit_form();
	}
}

{/literal}
</script>
<!-- BreadCrumbs -->
<div class="breadcrumb-header justify-content-between mt-3 mb-2 ">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-1">{$LNG.STOCK_TAKE}</h4>
		</div>
	</div>
</div>
<nav aria-label="breadcrumb m-0 mb-2">
	<ol class="breadcrumb bg-white ">
		<li class="breadcrumb-item">
			<a href="home.php">{$LNG.DASHBOARD}</a>
		</li>
		<li class="breadcrumb-item">
			<a href="home.php?a=menu&id=stock_take">{$module_name}</a>
		</li>
	</ol>
</nav>
<!-- /BreadCrumbs -->

<!-- Error Message -->
{if $err}
	{foreach from=$err item=e}
	<div class="alert alert-danger mg-b-0 " role="alert">
		<button aria-label="Close" class="close" data-dismiss="alert" type="button">
			<span aria-hidden="true">&times;</span>
		</button>
		{$e}
	</div>
    {/foreach}
{/if}
<!-- /Error Message -->

<!-- row -->
<div class="row ">
	<div class="col-lg-12 col-md-12">
		<div class="card">
			<!-- Form -->
			<form name="f_a" method="post" onSubmit="return false">			
				<input type="hidden" name="a" value="save_setting" />
				<input type="hidden" name="id" value="{$form.id}" />
				<input type="hidden" name="branch_id" value="{$branch_id}" />
				<input type="hidden" name="do_type" value="{$do_type}" />
				<div class="card-body">
					<div class="pd-15 pd-sm-20">
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.DATE}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<input class="form-control" type="text" id="inp_do_date" name="date_t" value="{$form.date_t|default:$smarty.now|date_format:'%Y-%m-%d'}" size="10"  onkeypress="form_keypress(event);">
								<small class="help-block text-muted">(YYYY-MM-DD)</small>
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.LOCATION}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<input class="form-control" type="text" name="location" {if $location}value="{$location}"{/if} onkeypress="form_keypress(event); ">
							</div>
						</div>
						<div class="row row-xs align-items-center mg-b-20">
							<div class="col-md-2">
								<label class="font-weight-bold mg-b-0">{$LNG.SHELF}</label>
							</div>
							<div class="col-md-6 mg-t-5 mg-md-t-0">
								<input class="form-control" name="shelf" type="text" {if $shelf}value="{$shelf}"{/if} onkeypress="form_keypress(event);">
							</div>
						</div>
						<button class="btn btn-main-primary btn-block-sm pd-x-30 mg-r-5 mg-t-5" name="submit_btn" onclick="submit_form();">{$LNG.SAVE}</button>
					</div>
				</div>
			</form>
			<!-- / Form -->
		</div>
	</div>
</div>
<!-- /row -->

{literal}
<script>
document.f_a['date_t'].focus();
</script>
{/literal}

{include file='footer.tpl'}
