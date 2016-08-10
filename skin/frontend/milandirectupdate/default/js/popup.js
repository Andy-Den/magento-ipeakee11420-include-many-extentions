//SETTING UP OUR POPUP  
//0 means disabled; 1 means enabled;  
var popupStatus = 0;
//loading popup with jQuery magic!
function loadPopup(){
    //loads popup only if it is disabled
    hidePopupNewsletter();
    if(popupStatus==0){
        jQuery("#backgroundPopup").css({
        "opacity": "0.7"
        });
        jQuery("#backgroundPopup").fadeIn("slow");
        jQuery("#popupMainContainer").fadeIn("slow");
            popupStatus = 1;
        jQuery(".container-loading").hide();
    }
}
function hidePopupNewsletter(){
    if(jQuery('.form-subscribe-popup').length){
        popupStatus = 1;
        jQuery(".container-loading").hide();
        jQuery('.form-subscribe-popup').hide();
        jQuery("#backgroundPopup").fadeOut();
        jQuery("#popupMainContainer").fadeOut();
        jQuery('#popupMiddleContainer').innerHTML = '';
        return false;
    }else {
        popupStatus = 0;
    }
}//disabling popup with jQuery magic!
function disablePopup(){
    //disables popup only if it is enabled
    if(popupStatus == 1){
        jQuery("#backgroundPopup").fadeOut("slow");
        jQuery("#popupMainContainer").fadeOut("slow");
        jQuery('#popupMiddleContainer').innerHTML = '';
        popupStatus = 0;
        jQuery(".container-loading").show();
    }
}

function changePostcode(){
    jQuery('.postcode #post_code_est').val("");
    jQuery('.postcode #post_code_est').focus();
}
function changePostcodeCart(){
    jQuery('input#postcode').val("");
    jQuery('input#postcode').focus();
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
        jQuery("#backgroundPopup").css({
            "opacity": "0.7"
        });
        jQuery("#backgroundPopup").fadeIn("slow");
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

function closePopup() {
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
	//Press Escape event!
	jQuery(document).keypress(function(e){
		if(e.keyCode==27 && popupStatus==1){
			closePopup();
		}
	});
    jQuery('.fancybox').fancybox();
});