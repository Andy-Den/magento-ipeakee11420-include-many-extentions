/*
 *  Easy Paginate 1.0 - jQuery plugin
 *  written by Alen Grakalic
 *  http://cssglobe.com/
 *
 *  Copyright (c) 2011 Alen Grakalic (http://cssglobe.com)
 *  Dual licensed under the MIT (MIT-LICENSE.txt)
 *  and GPL (GPL-LICENSE.txt) licenses.
 *
 *  Built for jQuery library
 *  http://jquery.com
 *
 */

(function($) {

    $.fn.easyPaginate = function(options){

        var defaults = {
            step: 6,
            delay: 2500,
            numeric: true,
            nextprev: true,
            auto:true,
            pause:4000,
            clickstop:true,
            controls: 'pagination',
            current: 'current'
        };

        var options = $.extend(defaults, options);
        var step = options.step;
        var lower, upper;
        var children = $(this).children();
        var count = children.length;
        var obj, next, prev;
        var page = 1;
        var timeout;
        var clicked = false;


          if ($.browser.mozilla) {
            $.fn.disableTextSelect = function() {
              return this.each(function() {
                $(this).css({
                  'MozUserSelect': 'none'
                });
              });
            };
            $.fn.enableTextSelect = function() {
              return this.each(function() {
                $(this).css({
                  'MozUserSelect': ''
                });
              });
            };
          } else if ($.browser.msie) {
            $.fn.disableTextSelect = function() {
              return this.each(function() {
                $(this).bind('selectstart.disableTextSelect', function() {
                  return false;
                });
              });
            };
            $.fn.enableTextSelect = function() {
              return this.each(function() {
                $(this).unbind('selectstart.disableTextSelect');
              });
            };
          } else {
            $.fn.disableTextSelect = function() {
              return this.each(function() {
                $(this).bind('mousedown.disableTextSelect', function() {
                  return false;
                });
              });
            };
            $.fn.enableTextSelect = function() {
              return this.each(function() {
                $(this).unbind('mousedown.disableTextSelect');
              });
            };
          }


        function show(){
            clearTimeout(timeout);
            lower = ((page-1) * step);
            upper = lower+step;
            $(children).each(function(i){
                var child = $(this);
                child.hide();
                if(i>=lower && i<upper){ setTimeout(function(){ child.fadeIn('slow') }, ( i-( Math.floor(i/step) * step) )*options.delay ); }
                if(options.nextprev){
                    if(upper > count) {
                        page = 0;
                        page++; show();
                    } else {
                        next.fadeIn('slow');
                    };
                    if(lower >= 1) {
                        prev.fadeIn('slow');
                    } else {
                        prev.fadeIn('slow');
                        if(page < 1) {
                            page = count;
                            show();
                        }
                    };
                };
            });
            $('li','#'+ options.controls).removeClass(options.current);
            $('li[data-index="'+page+'"]','#'+ options.controls).addClass(options.current);

            if(options.auto){
                if(options.clickstop && clicked){}else{ timeout = setTimeout(auto,options.pause); };
            };
        };

        function auto(){
            //while(1){
                if(page < count) {
                    page++; show();
                } else {
                    page = 0;
                    page++; show();
                }
            //}
            //alert(count);

        };

        this.each(function(){

            obj = this;

            if(count>step){

                var pages = Math.floor(count/step);
                if((count/step) > pages) pages++;

                var ol = $('<ol id="'+ options.controls +'"></ol>').insertAfter(obj);

                if(options.nextprev){
                    prev = $('<li class="prev">Previous</li>')
                        .hide()
                        .disableTextSelect()
                        .appendTo(ol)
                        .click(function(){
                            clicked = true;
                            page--;
                            show();
                        });
                };

                if(options.numeric){
                    for(var i=1;i<=pages;i++){
                    $('<li data-index="'+ i +'">'+ i +'</li>')
                        .appendTo(ol)
                        .click(function(){
                            clicked = true;
                            page = $(this).attr('data-index');
                            show();
                        });
                    };
                };

                if(options.nextprev){
                    next = $('<li class="next">Next</li>')
                        .hide()
                        .disableTextSelect()
                        .appendTo(ol)
                        .click(function(){
                            clicked = true;
                            page++;
                            show();
                        });
                };
                show();
            };
        });

    };

})(jQuery);
