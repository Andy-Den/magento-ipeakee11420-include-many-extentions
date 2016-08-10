//SETTING UP OUR POPUP  
//0 means disabled; 1 means enabled;  
var popupStatus = 0;
//loading popup with jQuery magic!
function loadPopup(){
    //loads popup only if it is disabled
    if(popupStatus==0){
        jQuery("#backgroundPopup").css({
        "opacity": "0.7"
        });
        jQuery("#backgroundPopup").fadeIn("slow");
        jQuery("#popupMainContainer").fadeIn("slow");
            popupStatus = 1;
    }
}

//disabling popup with jQuery magic!
function disablePopup(){
    //disables popup only if it is enabled
    if(popupStatus == 1){
        jQuery("#backgroundPopup").fadeOut("slow");
        jQuery("#popupMainContainer").fadeOut("slow");
        jQuery('#popupMiddleContainer').innerHTML = '';
        popupStatus = 0;
    }
}

//centering popup 
function centerPopup(id){
    if(id == '')
        id = 'popupMainContainer';
    //request data for centering
    var windowWidth = jQuery(window).width();
    var windowHeight = jQuery(window).height();
    
    var popupHeight = jQuery("#"+id).height();
    var popupWidth = jQuery("#"+id).width();
    if(popupHeight == 0) {
    	popupHeight = jQuery('#upsell_popup_height').val();
    }
    
    //centering
    jQuery("#"+id).css({
        "position": "fixed",
        "top": windowHeight/2-popupHeight/2,
        "left": windowWidth/2-popupWidth/2
    });
    //only need force for IE6

    jQuery("#backgroundPopup").css({
        "height": windowHeight
    });
}

function showPopup(url) {
    try {
        jQuery('#popupMiddleContainer').load(url, function(responseText, textStatus, XMLHttpRequest) {
            //centering with css
            //centerPopup();
            //load popup
            if(textStatus != 'error')
                loadPopup();
            else {
                //alert('Coulnot load popup. Please try again.');
                checkForOtherUrl();
            }
        });
    } 
    catch (e) {
        alert('Coulnot load popup. Please try again.');
	}
}

function closePopup(url) {
    checkForCallback();
	disablePopup();   
}
function disabledLightbox() {
    jQuery('#lightbox').hide();
    jQuery('#overlay').hide();
    return false;
}
//CONTROLLING EVENTS IN jQuery
jQuery(document).ready(function(){
	
	//LOADING POPUP
	//Click the button event!
	jQuery("#button").click(function(){
		//centering with css
		centerPopup();
		//load popup
		loadPopup();
	});
				
	//CLOSING POPUP
	//Click the x event!
	jQuery("#popupClose").click(function(){
        checkForCallback();
		disablePopup();
	});
	//Click out event!
	jQuery("#backgroundPopup").click(function(){
		closePopup();
	});
	//Press Escape event!
	jQuery(document).keypress(function(e){
		if(e.keyCode==27 && popupStatus==1){
			closePopup();
		}
	});
    jQuery('.product-img-box div.onsale-product-container').hover(
        function(){
            if(jQuery(this).find('.onsale-product-container-inside').length){
                if(jQuery(this).find('.onsale-product-container-inside').width()){
                    var html = '<a onclick="disabledLightbox()" class="PIN_1434941771252_pin_it_button_20 PIN_1434941771252_pin_it_button_en_20_gray PIN_1434941771252_pin_it_button_floating_20" title="Pin it!" data-pin-log="button_pinit_floating" data-pin-href="//www.pinterest.com/pin/create/button/?guid=oz6ULM9MSok9&amp;url='+jQuery('.product-img-box a.btn-zoom-1').attr('href')+'&amp;description='+jQuery('.product-img-box a.btn-zoom-1').attr('title')+'" style="top: 513px; left: 501px; z-index: 8675309 !important; display: block;background-image: url(\'https://s-passets.pinimg.com/images/pidgets/pinit_bg_en_rect_gray_20_1.png\') !important;cursor: pointer !important;left: 5px;top: 5px;width: 40px;height: 20px;position: absolute;"></a>';
                    jQuery('.product-img-box div.onsale-product-container').append(html);
                }
            }
        },
        function(){
            jQuery('.product-img-box div.onsale-product-container a.PIN_1434941771252_pin_it_button_20').remove();
        }
    );
    jQuery('.fancybox').fancybox();
});