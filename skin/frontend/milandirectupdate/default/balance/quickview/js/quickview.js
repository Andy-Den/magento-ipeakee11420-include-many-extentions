jQuery.noConflict();
jQuery(document).ready(function(){
    jQuery(".quickview_icon").fancybox({
        maxWidth	: 914,
        maxHeight	: '90%',
        fitToView	: true,
        width		: '80%',
        height		: 'auto',
        autoSize	: false,
        scroll      : 'no',
        closeClick	: false,
        openEffect	: 'none',
        closeEffect	: 'none',
        type        : 'ajax',
        afterShow     : function(){

            jQuery("select:not(.super-attribute-select), input[type='checkbox'],  input[type='radio']").uniform({selectAutoWidth: false});
            if(jQuery('.fancybox-outer .more-views').length){
                var owl = jQuery('.fancybox-outer .product-image-thumbs');
                owl.owlCarousel({
                    lazyLoad: true,
                    items: 3,
                    itemsCustom: [ [0, 1], [320, 2], [480, 2], [768, 3], [1010, 3], [1280, 3] ],
                    responsiveRefreshRate: 50,
                    slideSpeed: 200,
                    paginationSpeed: 500,
                    scrollPerPage: true,
                    stopOnHover: true,
                    rewindNav: true,
                    rewindSpeed: 600,
                    //pagination: true,
                    navigation: true
                });
            }
            ProductMediaManager.initView();

            // Hide warranty option
            if(jQuery('.quickview_product_page .product-options').length){
                var size_dl = jQuery('.quickview_product_page .product-options dl').size();
                if(size_dl == 1){
                    jQuery('.quickview_product_page .product-options').hide();
                }
            }
            jQuery('.btn-review-count').on('click', function () {
                if(!jQuery('body').hasClass('catalog-product-view')){
                    var href = jQuery('.actions-redirect a').attr('href');
                    window.location.href = href+'#btn-review-count';
                }

            });
            updatePriceHtml();
            if( ( jQuery('.fancybox-outer .quickview_product_page').height() < (jQuery('.fancybox-skin .fancybox-inner').height() +10) ) || (jQuery('.fancybox-outer .quickview_product_page').height() < (jQuery('.fancybox-skin .fancybox-inner').height() -10)) ){
                jQuery('.fancybox-outer .fancybox-inner').css({'overflow':'visible'});
            }
            var myImage = $j('.quickview_product_page .product-view .product-img-box .product-image img');
            if(myImage.prop('complete')){
                jQuery('.fancybox-outer .fancybox-inner').css({'overflow':'auto'});
                if( ( jQuery('.fancybox-outer .quickview_product_page').height() < (jQuery('.fancybox-skin .fancybox-inner').height() +10) ) || (jQuery('.fancybox-outer .quickview_product_page').height() < (jQuery('.fancybox-skin .fancybox-inner').height() -10)) ){
                    jQuery('.fancybox-outer .fancybox-inner').css({'overflow':'visible'});
                }
            }else{
                /* Call the codes/function after the image is loaded */
                myImage.on('load',function(){
                    jQuery('.fancybox-outer .fancybox-inner').css({'overflow':'auto'});
                    if( ( jQuery('.fancybox-outer .quickview_product_page').height() < (jQuery('.fancybox-skin .fancybox-inner').height() +10) ) || (jQuery('.fancybox-outer .quickview_product_page').height() < (jQuery('.fancybox-skin .fancybox-inner').height() -10)) ){
                        jQuery('.fancybox-outer .fancybox-inner').css({'overflow':'visible'});
                    }
                });
            }
        }
    });

    jQuery("a.quicview_more_img").click(function( e ) {
        e.preventDefault();
        imgid = jQuery(this).attr('id');
        imgid = imgid.substring(0, imgid.indexOf("_"));
        jQuery('#'+imgid).attr('src', jQuery(this).attr('href'));
        return false;
      });
 });
