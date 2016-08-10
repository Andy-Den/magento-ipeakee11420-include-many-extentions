jQuery(function(){
        jQuery('.slide-out-div').tabSlideOut({
            tabHandle: '#handle',                     //class of the element that will become your tab
            
            imageHeight: '37px',                     //height of tab image           //Optionally can be set using css
            imageWidth: '22px',                       //width of tab image            //Optionally can be set using css
            
			tabLocation: 'right',                      //side of screen where tab lives, top, right, bottom, or left
            speed: 200,                               //speed of animation
            action: 'click',                          //options: 'click' or 'hover', action to trigger animation
            topPos: '140px',                          //position from the top/ use if tabLocation is left or right
            leftPos: '0px',                          //position from left/ use if tabLocation is bottom or top
            fixedPosition: false,                      //options: true makes it stick(fixed position) on scroll
            onLoadSlideOut: (jQuery(window).width() >= 1580) ? true : false
        });
		getScreenResolution();
    });

jQuery(document).ready(function(){
	jQuery(window).resize(function(){
		var screenWidth = jQuery(window).width();
		if(screenWidth < 1580)
			jQuery('.slide-out-div').hide();
		else {
			jQuery('.slide-out-div').show();
			jQuery(".slide-out-div").css('right','-3px');
			jQuery(".slide-out-div").addClass('open');
		}
	});
});

function getScreenResolution()
{
	var screenWidth = jQuery(window).width();
    var screenHeight = jQuery(window).height();
    
	if(screenWidth < 1580)
		jQuery('.slide-out-div').hide();
		
	jQuery("#handle").css('left','-20px');
	if(jQuery("#handle").hasClass('handle-cart')){
		jQuery("#handle").css('top','100px');
	} else {
		jQuery("#handle").css('top','182px');
	}
}
