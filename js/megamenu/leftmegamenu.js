/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/*----- left menu ------*/
if(leftmenu){
    
    $mega(document).ready(function(){
        if(lisMobile != null && llabel_hassub.attr('href') != null){
            llabel_hassub.removeAttr('href');
        }
        if(leftmenu_effect == 3 || lisMobile != null){
            llabel.bind('click',function(){
                var $_this = $lmega(this);
                if($_this.hasClass('flag')){
                    $_this.removeClass('flag');
                    $_this.parent().removeClass('active');
                    $_this.next().fadeOut();
                }else{
                    llabel.removeClass('flag');
                    llabel.parent().removeClass('active');
                    llabel.next().hide();
                    $_this.addClass('flag');
                    $_this.parent().addClass('active');
                    $_this.next().fadeIn(150);
                 }
            });
        }else{
            lms_level0.bind('mouseenter',function(){
                var $_this = $lmega(this);
                lms_level0.removeClass('active');
                lms_level0.children('.msl-submenu').hide();
                lms_level0.children('.msl-label').removeClass('flag');
                $_this.children('.msl-label').addClass('flag');
                $_this.addClass('active');
                if(leftmenu_effect == 2){
                    $_this.children('.msl-submenu').stop().slideDown(150);
                }else{
                    $_this.children('.msl-submenu').stop().fadeIn(150);
                }
            });
            lms_level0.bind('mouseleave',function(){
                var $_this = $lmega(this);
                $_this.removeClass('active');
                $_this.children('.msl-label').removeClass('flag');
                $_this.children('.msl-submenu').hide();
            });
        }
        //---> Set Width auto resize and Position auto
        var width_main  = lmain.width();
        var left_width = leftmenu.width();
        var width_default = width_main - left_width;
        for( var i=0;i<llabel_hassub.length;i++){
            var lsubmenu = llabel_hassub[i].next();
            var lwidth_value = larr_width[i]*width_default/100+'px';
            $lmega(lsubmenu).css({
                width:lwidth_value,
                left:left_width-1+'px'
            });
        }
        $lmega( window ).resize(function() {
            width_main  = lmain.width();
            left_width = leftmenu.width();
            var lwidth_resize = width_main - left_width;
            //alert(lwidth_resize);
            //if(lwidth_resize > width_default) width_default = lwidth_resize;
            for( var i=0;i<llabel_hassub.length;i++){
                var lsubmenu = llabel_hassub[i].next();
                    var lwidth_value = larr_width[i]*lwidth_resize/100+'px';
                    $lmega(lsubmenu).css({
                        width:lwidth_value,
                        left:left_width-1+'px'
                });    
            } 
        });
         // Show/hide level 3 category
        if($mega('.ms-leftmenu .sub-level3')){
            $mega('.ms-leftmenu .col-level div.level2').bind('mouseenter',function(){
                var $_this =  $mega(this);
                $_this.addClass('active');
                $_this.children('.sub-level3').stop().fadeIn();
            });
            $mega('.ms-leftmenu .col-level div.level2').bind('mouseleave',function(){
                var $_this =  $mega(this);
                $_this.removeClass('active');
                $_this.children('.sub-level3').stop().hide();
            });
        }
    });
    
    
}


/* ----- Moibile version ------*/
if(lmbmenu){
    lanchor.bind('click',function(){
        var $_this= $mega(this);
        if($_this.hasClass('flag')){
            $_this.removeClass('flag');
            $_this.parent().removeClass('show-menu');
            lmbmenu.slideUp(200);
        }else{
            $_this.addClass('flag');
            $_this.parent().addClass('show-menu');
            lmbmenu.slideDown(200);
        }
    });
    if(lmobile_effect==1){
        lmclick.bind('click',function(){
            var $_this = $mega(this);
            if($_this.hasClass('flag')){
                $_this.removeClass('flag');
                $_this.children('span').removeClass('glyphicon-minus');
                $_this.next().slideUp(200);
            }else{
                lmclick.removeClass('flag');
                lmclick.children('span').removeClass('glyphicon-minus');
                lmclick.next().slideUp(200);
                $_this.addClass('flag');
                $_this.children('span').addClass('glyphicon-minus');
                $_this.next().slideDown(200);
            }
        });
    }else{
        lmclick.bind('click',function(){
            var $_this = $mega(this);
            lmclick.parent().removeClass('active');
            $_this.parent().addClass('active');
            $_this.next().animate({
                left:0
            },300);
        });
        lmreturn.bind('click',function(){
           var $_this = $mega(this);

           lmclick.next().animate({
               left:100+'%'
                },300,function(){
                    lmclick.parent().removeClass('active');  
            });
       });
    }
}
/*---- Change Mobile <---> LeftMenu  ----- */
if(ltype_menu == 2){
    $lmega( document ).ready(function() {
       var $_this = $lmega(this);
       if($_this.width() < lwidth_change){
           lpc_megamenu.hide();
           lmb_megamenu.show();
       }else{
           lpc_megamenu.show();
           lmb_megamenu.hide();
       }
   }); 
    $lmega( window ).resize(function() {
        var $_this = $lmega(this);   
        if($_this.width() < lwidth_change){
            lpc_megamenu.hide();
            lmb_megamenu.show();
        }else{
            lpc_megamenu.show();
            lmb_megamenu.hide();
        }
        //lresetAll();
    });   
  function lresetAll(){
    llabel.removeClass('flag');
    llabel.parent().removeClass('active');
    llabel.next().hide();
    lmclick.removeClass('flag');
    lmclick.children('span').removeClass('glyphicon-minus');
    lmclick.next().hide();
    lmclick.parent().removeClass('active');  
    if(lmobile_effect!=1){
        lmclick.next().css({
               left:100+'%'
                });
    }
  }
}





