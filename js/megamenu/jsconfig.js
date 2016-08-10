/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
window.onload = function(){
    if($('megamenu_general_menu_type_config')){
    var $value = $('megamenu_general_menu_type_config').value;
    var top_config = $('megamenu_top_menu').parentNode;
    var left_config =  $('megamenu_left_menu').parentNode;
    var mobile_config = $('megamenu_mobile_menu').parentNode;
    //alert(top);
    if($value != 2){
        if($value == 0){
            mobile_config.hide();
        }else{
            top_config.hide();
            left_config.hide();
        }
    }
    $('megamenu_general_menu_type_config').onchange = function(){
        var $value =  this.value;
        if($value != 2){
            if($value == 0){
                mobile_config.hide();
                top_config.show();
                left_config.show();
            }else{
                top_config.hide();
                left_config.hide();
                mobile_config.show();
            }
        }else{
            mobile_config.show();
            top_config.show();
            left_config.show();
        }
    }
    }
};


