;(function ($) {
    $(document).ready(function () {
        /**
         /**
         *  update decoration with uniform to element input
         */
        try {
            updatePriceHtml();
        } catch (err) {
        }
        customerAccount();
        $("select:not(.super-attribute-select), input[type='checkbox'],  input[type='radio']").uniform({selectAutoWidth: false});
        $(".super-attribute-select").each(function(){
            if(jQuery(this).outerHeight()>0 && jQuery(this).hasClass('no-display') == false){
                jQuery(this).uniform({selectAutoWidth: true});
                jQuery(this).on('change', function(){
                    if (jQuery(this).val() == '') {
                        jQuery('.not-available').removeClass('not-available');
                        jQuery('.product-view .price-box').html('');
                        jQuery('.product-view .price-box-configurable').show();
                    }
                });
            }
        });
        $('.ms-megamenu.megamenu-mobile .mb-content .col-category span.glyphicon').click(function () {
            if ($(this).hasClass('glyphicon-minus')) {
                $(this).parent().parent().find('div.row').hide();
                $(this).removeClass('glyphicon-minus');
            } else {
                $(this).parent().parent().find('div.row').show();
                $(this).addClass('glyphicon-minus');
            }
            return false;
        });
        $(document).on('mouseover','#minicart-header-containter', function () {
            jQuery(this).children('#header-cart,.skip-cart').addClass("skip-active")
        });
        $(document).on('mouseout','#minicart-header-containter', function () {
            jQuery(this).children('#header-cart,.skip-cart').removeClass("skip-active");
        });
        $(document).on('mouseover','#header-account-link', function () {
            jQuery(this).children('#header-account,.skip-account').addClass("skip-active")
        });
        $(document).on('mouseout','#header-account-link', function () {
            jQuery(this).children('#header-account,.skip-account').removeClass("skip-active");
        });
        $('.toolbar-bottom .toolbar .pager .pages li.current').prev().addClass('show');
        $('.toolbar-bottom .toolbar .pager .pages li.current').next().addClass('show');
        if (!$('.toolbar-bottom .toolbar .pager .pages li.previous-page').length) {
            $('.toolbar-bottom .toolbar .pager .pages li.current').next().next().addClass('show');
        }
        $('.view-all-category a').click(function(){
            window.location.href = $('.mb-version .mb-label.flag').attr('data-href');
        });
        $('.live-chat .live-chat-container').click(function () {
            LC_API.open_chat_window();
        })
        if ($(window).width() > 980) {
            var $container = $('.ms-content .ms-maincontent .row.ms-category');

            $container.imagesLoaded(function () {
                $container.masonry({
                    itemSelector: '.col-category.col-level',
                    columnWidth: 0,
                    isAnimated: !Modernizr.csstransitions
                });
            })
        }
        if($(window).width() < 980){
            $('.view-all-category').addClass('col-category');
        }
        $('.col-left div.block-layered-nav.amshopby-collapse-enabled dl .filter-colour-swatches').removeClass('current');
        $('.tab-login .tab-title').click(function () {
            if (!$(this).hasClass('active')) {
                if (!$(this).hasClass('member-login')) {
                    $('.tab-login .tab-title').removeClass('active');
                    $('.tab-login .tab-title.guest-login').addClass('active');
                    $('div.checkout-guest-checkout').css({'display': 'block'});
                    $('div.checkout-member-login').css({'display': 'none'});
                } else {
                    $('.tab-login .tab-title').removeClass('active');
                    $('.tab-login .tab-title.member-login').addClass('active');
                    $('div.checkout-guest-checkout').css({'display': 'none'});
                    $('div.checkout-member-login').css({'display': 'block'});
                }
            }
        });
        $('a.skip-link.skip-nav').click(function () {
            if ($(this).hasClass('flag')) {
                $(this).removeClass('flag');
                mbmenu.slideUp(200);
            } else {
                $(this).addClass('flag');
                mbmenu.slideDown(200);
            }
        });
        $('li.mobile-country').click(function () {
            if ($(this).hasClass('flag')) {
                $(this).removeClass('flag');
                $('.page .header-language-background .switches ul li').hide();
                $('.flag-country').hide();
            } else {
                $(this).addClass('flag');
                $('.page .header-language-background .switches ul li').show();
                $('.flag-country').show();
            }
        });
        $('.flag-country').click(function () {
            $('.page .header-language-background .switches ul li.mobile-country').removeClass('flag');
            $('.page .header-language-background .switches ul li').hide();
        });
        $('.empty-service .title-service .block-title span span').click(function () {
            LC_API.open_chat_window();
        });
        $('div.onestepcheckout-column-left .onestepcheckout-numbers').click(function () {
            if (parseInt($(window).width()) < 600) {
                if ($('.onestepcheckout-column-left').hasClass('hide-step-disable')) {
                    $('.onestepcheckout-column-middle').addClass('cloumn-disable');
                    $('.onestepcheckout-column-right').addClass('cloumn-disable');
                    $('.onestepcheckout-column-left').removeClass('cloumn-disable');
                }
            }
        });
        $('.login-toltip').click(function () {
            if ($(window).width() < 1200) {
                if ($(this).hasClass('active')) {
                    $(this).removeClass('active');
                } else {
                    $(this).addClass('active');
                }
            }
        });
        $('div.onestepcheckout-column-right .onestepcheckout-numbers').click(function () {
            if (parseInt($(window).width()) < 600) {
                if ($('.onestepcheckout-column-right').hasClass('hide-step-disable')) {
                    $('.onestepcheckout-column-middle').addClass('cloumn-disable');
                    $('.onestepcheckout-column-left').addClass('cloumn-disable');
                    $('.onestepcheckout-column-right').removeClass('cloumn-disable');
                }
            }
        });
        $('div.onestepcheckout-column-middle .onestepcheckout-numbers').click(function () {
            if (parseInt($(window).width()) < 600) {
                if ($('.onestepcheckout-column-middle').hasClass('hide-step-disable')) {
                    $('.onestepcheckout-column-right').addClass('cloumn-disable');
                    $('.onestepcheckout-column-left').addClass('cloumn-disable');
                    $('.onestepcheckout-column-middle').removeClass('cloumn-disable');
                }
            }
        });
        // set padding-bottom product info of products on category page

        //  Show estimate delivery
        if ($('.product-estimate-delivery').length) {
            $('.product-estimate-delivery #product-shipping-estimate').hide();
            $('.product-estimate-delivery a').on('click', function () {
                $(this).parent().toggleClass('expaned');
                $('.product-estimate-delivery #product-shipping-estimate').slideToggle();
            });
        }else {
            if($('.product-custom-stock-status').length){
                $('.product-custom-stock-status').addClass('border-bottom');
            }
        }
        if ($('.product-shop .ratings').length) {
            $('.btn-add-review').on('click', function () {
                $('.toggle-tabs li').removeClass('current');
                $('.toggle-tabs li:nth-child(4)').addClass('current');
                $('#collateral-tabs dd').removeClass('current');
                $('#collateral-tabs dd').find('#customer-reviews').parent().parent().addClass('current').css({'display':'block'});
                $('html, body').animate({
                    scrollTop: $('#review-form').offset().top
                }, 500);
            });
            $('.btn-review-count').on('click', function () {
                $('.toggle-tabs li').removeClass('current');
                $('.toggle-tabs li:nth-child(4)').addClass('current');
                $('#collateral-tabs dd').removeClass('current');
                $('#collateral-tabs dd').find('#customer-reviews').parent().parent().addClass('current').css({'display':'block'});
                $('html, body').animate({
                    scrollTop: $('div#customer-reviews').offset().top
                }, 500);
            });
            $('.btn-add-your-review').on('click', function () {
                $('html, body').animate({
                    scrollTop: $('#review-form').offset().top
                }, 500);
            });
        }
        if ($('#collateral-tabs').length && parseInt($(window).width()) < 767) {
            $('#collateral-tabs').children('dt#description').removeClass('current');
            $('.collateral-tabs').children('dt#description').next('dd').removeClass('current');
        }

        // more view images slider
        if ($('.more-views').length) {
            $(function ($) {
                var owl = $('.product-image-thumbs');
                owl.owlCarousel({
                    lazyLoad: true,
                    items: 5,
                    itemsCustom: [[0, 1], [320, 3], [480, 3], [768, 5], [1010, 5], [1280, 5]],
                    responsiveRefreshRate: 50,
                    slideSpeed: 200,
                    paginationSpeed: 500,
                    scrollPerPage: false,
                    stopOnHover: true,
                    rewindNav: true,
                    rewindSpeed: 600,
                    //pagination: true,
                    navigation: true
                });
            });
        }

        $(".rating-selectable li").click(function () {
            $(this).parent().find('span').removeClass('selected');
            $(this).has("span.checked").nextAll().find("span").addClass("selected");
        });
        $('.back-to-top').on('click', function () {
            $('html,body').animate({
                scrollTop: 0
            }, 1000);
            //return false;
        });
        // Hover product images
        if ($('.product-image').length) {
            $('.product-image').hover(function () {
                $(this).parent().find('.quickview_icon').addClass('over');
            }, function () {
                $(this).parent().find('.quickview_icon').removeClass('over');
            });
        }
        // Toggle tabs about page
        if ($('.cms-about-milan').length) {
            $('.Our-philosophy-text').hide();
            $('.titlTxt').on('click', function () {
                $(this).parent().find('.arrow').toggleClass('active');
                $(this).parent().parent().find('.Our-philosophy-text').slideToggle();
            });
            $('.arrow').on('click', function () {
                $(this).toggleClass('active');
                $(this).parent().parent().find('.Our-philosophy-text').slideToggle();
            });
        }
        // 'Ask a question' scroll to form
        if ($('#answered-questions').length) {
            $('.btn-ask-question').on('click', function () {
                $('html, body').animate({
                    scrollTop: $('#pq-question-form').offset().top
                }, 500);
            });
        }
        if ($('#block-shopby-content').length) {
            var titleCategory = $('#narrow-by-list dt');
            $('#narrow-by-list dd').each(function () {
                if ($(this).children('ol').hasClass('no-display-current')) {
                    $(this).children('ol').removeClass('no-display-current');
                    $(this).showMore({
                        speedDown: 300,
                        speedUp: 300,
                        height: '340px',
                        showText: 'Show more...',
                        hideText: 'Show less...'
                    });
                    $(this).children('ol').addClass('no-display-current');
                } else {
                    $(this).showMore({
                        speedDown: 300,
                        speedUp: 300,
                        height: '340px',
                        showText: 'Show more...',
                        hideText: 'Show less...'
                    });
                }

            });
            titleCategory.each(function (index) {
                if ($(this).text().trim() === 'Colour' || $(this).text().trim() === 'Color') {
                    $(this).next('dd').addClass('filter-colour-swatches');
                }
            });
            var click = 0;
            $('.filter-colour-swatches span.less').on('click', function(){
                if(click == 1){
                    $('.filter-colour-swatches .showmore_content').css('max-height','340px');
                    click--;
                }
            });
            $('.filter-colour-swatches span.more').on('click', function(){
                if(click == 0){
                    $('.filter-colour-swatches .showmore_content').css('max-height','none');
                }
                click++;
            });
        }
        $('.product-view .product-essential .products-share-this .social-block ul.share-links li a.tw-milan').click(function () {
            var href = $(this).attr('href');
            var url = $("<a />").attr("href", document.URL)[0].hash.replace(/#^/, "");
            href = href + '?original_referer=' + url + '&url=' + url;
            window.open(href, "Share a link on Twitter", "toolbar=yes, scrollbars=yes, resizable=yes, width=800, height=600");
            return false;
        });
        $('div.color-available').click(function () {
            $(this).prev().addClass('active-color');
            $(this).hide();
        });
        $('.color-available-tablet').click(function () {
            $(this).prev().addClass('active-color');
            $(this).hide();
        });
        var url = window.location.href;
        url = url.split('#');
        if(url.length>1){
            var parammsUrl = url[1];
            $('.'+parammsUrl).click();
        }

        // add seo text of category to category page
        if ($j('.seo-text-category').length) {
            $j('.seo-text-category').insertAfter('.col-main');
        }
    });
    $(window).scroll(function () {
        if ((parseInt($(window).scrollTop()) - parseInt($(window).height())) > 200) {
            $('div.back-to-top').show();
        } else {
            $('div.back-to-top').hide();
        }
    });
    $(window).resize(function () {
        customerAccount();
        hidePopupNewsletter();
    });
    function customerAccount() {
        if (parseInt($(window).width()) < 770) {
            $('.customer-account .customer-title h1').text('My Account');
            $('.customer-account .sidebar .block-title strong span').text($('.customer-account .sidebar .block-content .current strong').html());
        } else {
            $('.customer-account .customer-title h1').text('My Dashboard');
            $('.customer-account .sidebar .block-title strong span').text($('.customer-account .sidebar .block-title strong span').attr('data-label'));
        }
    }

    Validation.add('validate-phone-custom','Please enter a valid number excluding spaces and special characters.',function(value){
        return Validation.get('IsEmpty').test(value) || (!isNaN(parseNumber(value)) && /^\s*-?\d*(\.\d*)?\s*$/.test(value) && (value.length)>7);
    });
    // validate color swatches on product page
    Validation.add('validate-colour','Please select a colour',function(value){
        return !Validation.get('IsEmpty').test(value);
    });

})(jQuery);
jQuery(window).load(function() {
    jQuery('.products-grid .item-images').hover(
        function() {
            jQuery(this).children('.quickview_icon').addClass('quickview_icon_display');
        }, function() {
            jQuery(this).children('.quickview_icon').removeClass('quickview_icon_display');
        }
    )
});
