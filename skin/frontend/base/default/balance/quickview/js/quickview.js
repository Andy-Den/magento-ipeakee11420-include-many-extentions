jQuery.noConflict();
jQuery(document).ready(function(){
    jQuery(".quickview_icon").fancybox({
        maxWidth	: 900,
        maxHeight	: 700,
        fitToView	: false,
        width		: '80%',
        height		: '75%',
        autoSize	: false,
        closeClick	: false,
        openEffect	: 'none',
        closeEffect	: 'none'
    });

    jQuery("a.quicview_more_img").click(function( e ) {
        e.preventDefault();
        imgid = jQuery(this).attr('id');
        imgid = imgid.substring(0, imgid.indexOf("_"));
        jQuery('#'+imgid).attr('src', jQuery(this).attr('href'));
        return false;
      });
 });
