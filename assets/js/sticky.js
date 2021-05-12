jQuery(document).ready(function(){

  var stickyElement = jQuery(".sticky"),
      stickyClass = "sticky-pin",
      stickyPos = stickyElement.offset().top, //Distance from the top of the window.
      stickyHeight;

  

  ///Create a negative margin to prevent content 'jumps':
  stickyElement.after('<div class="jumps-prevent"></div>');
  function jumpsPrevent() {
    stickyHeight = stickyElement.innerHeight();
    stickyElement.css({"margin-bottom":"-" + stickyHeight + "px"});
    stickyElement.next().css({"padding-top": + stickyHeight + "px"}); 
  };
  jumpsPrevent(); //Run.

  //Function trigger:
  jQuery(window).resize(function(){
    jumpsPrevent();
  });

  //Sticker function:
  function stickerFn() {
    var winTop = jQuery(this).scrollTop();
    //Check element position:
    winTop >= stickyPos ?
      stickyElement.addClass(stickyClass):
      stickyElement.removeClass(stickyClass) //Boolean class switcher.
  };
  stickerFn(); //Run.

  //Function trigger:
  jQuery(window).scroll(function(){
    stickerFn();
  });

});