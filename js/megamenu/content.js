/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
// all function have been showed and hide field in form

function showField(rowId){
    if($(rowId)){
        $(rowId).parentNode.parentNode.show();
        $(rowId).disabled = false;
        $(rowId).removeClassName('disabled');
    }
}
			
function hideField(rowId){
    if($(rowId)){
        $(rowId).parentNode.parentNode.hide();
        $(rowId).disabled = true;
        $(rowId).addClassName('disabled');
    }
}
			
function showMainProduct(rowId,proId){
    if($(rowId)){
        $(rowId).parentNode.parentNode.show();
        $(rowId).disabled = false;
        $(rowId).removeClassName('disabled');
    }
    if($(proId)){
        $(proId).parentNode.parentNode.hide();
        $(proId).disabled = true;
        $(proId).addClassName('disabled');
    }
}
			
function showMainCate(rowId,proId){
    if($(rowId)){
        $(rowId).parentNode.parentNode.hide();
        $(rowId).disabled = true;
        $(rowId).addClassName('disabled');
    }
    if($(proId)){
        $(proId).parentNode.parentNode.show();
        $(proId).disabled = false;
        $(proId).removeClassName('disabled');
    }
}
            
function hideMaincontentGroup(rowId){
    if($(rowId)){
        $(rowId).parentNode.hide();
        $(rowId).disabled = true;
        $(rowId).addClassName('disabled');
    }
}

function hideLayoutGroup(){
    $('layout').parentNode.parentNode.hide();
    $('layout').disabled = true;
    $('layout').addClassName('disabled');
}

function showLayoutGroup(){
    $('layout').parentNode.parentNode.show();
    $('layout').disabled = false;
    $('layout').removeClassName('disabled');
}
            
function showMaincontentGroup(rowId){
    if($(rowId)){
        $(rowId).parentNode.show();
        $(rowId).disabled = false;
        $(rowId).removeClassName('disabled');
    }
}

/**
 * load ajax all template for one menu type
*/
function reloadTemplate(value,url,template_value,el,load_url){
    if(!value){
        hideField('layout');
        hideField('size_megamenu');
        hideField('colum');
        hideField('size_colum');
        hideMaincontentGroup('megamenu_maincontent');
        hideMaincontentGroup('megamenu_featuredcategories');
        return;
    }
    
    if(value == 1){
        showField('size_megamenu');
        showField('layout');
        hideField('colum');
        hideField('size_colum');
        hideMaincontentGroup('megamenu_maincontent');
        hideMaincontentGroup('megamenu_featuredcategories');
        showField('template_id');
        showMaincontentGroup('megamenu_content_headerfooter');
    }

    if(value == 3){
        hideField('size_megamenu');
        showField('layout');
        showField('colum');
        showField('size_colum');
        hideField('products_box_title');
        hideField('categories_box_title');
        showMaincontentGroup('megamenu_featuredcategories');
        showMaincontentGroup('megamenu_maincontent');
        showMainCate('products','categories');
        showField('template_id');
        showMaincontentGroup('megamenu_content_headerfooter');
    }

    if(value == 2){
        hideField('size_megamenu');
        showField('layout');
        showField('colum');
        showField('size_colum');
        hideField('categories_box_title');
        showField('products_box_title');
        showMaincontentGroup('megamenu_featuredcategories');
        showMaincontentGroup('megamenu_maincontent');
        showMainProduct('products','categories');
        showField('template_id');
        showMaincontentGroup('megamenu_content_headerfooter');
    }
    
    if(value == 4){
        showField('size_megamenu');
        showField('layout');
        hideField('colum');
        hideField('size_colum');
        hideMaincontentGroup('megamenu_maincontent');
        hideMaincontentGroup('megamenu_featuredcategories');
        showField('template_id');
        showMaincontentGroup('megamenu_content_headerfooter');
    }
    
    if(value == 5){
        hideField('size_megamenu');
        showField('layout');
        showField('colum');
        showField('size_colum');
        showMaincontentGroup('featured_type');
        showMaincontentGroup('megamenu_maincontent');
        hideMaincontentGroup('megamenu_featuredcategories');
        showMainCate('products','categories');
        showField('template_id');
        showMaincontentGroup('megamenu_content_headerfooter');
    }
    
    if(value == 6){
        hideField('size_megamenu');
        hideField('layout');
        hideField('colum');
        hideField('size_colum');
        hideMaincontentGroup('megamenu_content_headerfooter');
        hideMaincontentGroup('megamenu_maincontent');
        hideMaincontentGroup('megamenu_featuredcategories');
    }
    
    this.variables = null;
    new Ajax.Request(url, {
        parameters: $('menu_type').serialize(true),
        onSuccess: function (transport) {
            if (transport.responseText.isJSON()) {
                var field = transport.responseText.evalJSON();
                var i=0;
                var el = document.getElementById('template_id');
                while( el.hasChildNodes() ){
                    el.removeChild(el.lastChild);
                }
                var firstOption = document.createElement('option');
                firstOption.className='option template_id';
                firstOption.text = '--Please choose template--';
                firstOption.value = '';
                $('template_id').appendChild(firstOption);
                for(i=0;i<field.length;i++){
                    var newOption = document.createElement('option');
                    newOption.className='option template_id';
                    newOption.text = field[i].name;
                    newOption.value = field[i].id;
                    $('template_id').appendChild(newOption);
                }
                if(template_value){
                    $('template_id').value = template_value;
                }
                if(template_value == 6){
                    loadTemplate('template_id',load_url,template_value);
                }
                if(!$('template_id').value){
                    hideLayoutGroup();
                }else{
                    showLayoutGroup();
                }
                if(el != null && load_url != null && template_value != null)
                    loadTemplate(el,load_url,template_value);
            }
        }.bind(this)
    });
}

/**
 * load ajax when choose template
*/
function loadTemplate(el,url,template_value){
    var value= el.value;
    if(!value){
        hideLayoutGroup();
    }
     if($('menu_type').value == 6){// 6 is anchor text
        hideLayoutGroup();
    }
    if(value){
       if($('menu_type').value != 6){
            showLayoutGroup();
        }
        var request = new Ajax.Request(url,{
            parameters: $('template_id').serialize(true),
            onSuccess:function(transport){
                var result = JSON.parse(transport.responseText);
                
                if(result.template_map)
                    $('layout').innerHTML = result.template_map;
                if(result.headerfooter){
                    
                    if(typeof(template_value) == "undefined"){
                        $('header').innerHTML = result.headerfooter.header;
                        $('header').footer = result.headerfooter.footer;
//                        tinyMCE.get('header').setContent(result.headerfooter.header);  
//                        tinyMCE.get('footer').setContent(result.headerfooter.footer);
                    }
                }
            
                if(result.general_style){
                    var default_style = result.general_style;
                    var background_color = default_style.background_color.substr(1);
                    
                    if(typeof(template_value) == "undefined"){
                        $('background_color').value = background_color;   
                        
                    }
                    $('background_color_hide').value= background_color;
                    var border_color = default_style.border_color.substr(1); 
                    if(typeof(template_value) == "undefined"){
                        $('border_color').value = border_color;
                    }
                    $('border_color_hide').value = border_color;
                    var border_size = default_style.border_size;
                    if(typeof(template_value) == "undefined"){
                        $('border_size').value = border_size;
                    }
                    $('border_size_hide').value = border_size;
                }
                
                if(result.title_style){
                    var title_style = result.title_style;
                    var title_color = title_style.title_color.substr(1);
                    if(typeof(template_value) == "undefined"){
                        $('title_color').value = title_color ;    
                    }
                    $('title_color_hide').value = title_color ;    
                    var title_background_color = title_style.title_background_color.substr(1);
                    if(typeof(template_value) == "undefined"){
                        $('title_background_color').value = title_background_color;   
                    }
                    $('title_background_color_hide').value = title_background_color;     
                    var title_font = title_style.title_font;
                    if(typeof(template_value) == "undefined"){
                        $('title_font').value = title_font;   
                    }
                    $('title_font_hide').value = title_font;   
                    
                    var title_font_size = title_style.title_font_size;
                    if(typeof(template_value) == "undefined"){
                        $('title_font_size').value = title_font_size;
                    }
                    $('title_font_size_hide').value = title_font_size;
                }
                
                if(result.subtitle_style){
                    var subtitle_style = result.subtitle_style;
                    var subtitle_color = subtitle_style.subtitle_color.substr(1);  
                    if(typeof(template_value) == "undefined"){
                        $('subtitle_color').value = subtitle_color;
                    }
                    $('subtitle_color_hide').value = subtitle_color;
                    var subtitle_font = subtitle_style.subtitle_font;    
                    if(typeof(template_value) == "undefined"){
                        $('subtitle_font').value = subtitle_font;
                    }
                    $('subtitle_font_hide').value = subtitle_font;
                    var subtitle_font_size = subtitle_style.subtitle_font_size;
                    if(typeof(template_value) == "undefined"){
                        $('subtitle_font_size').value = subtitle_font_size;
                    }
                    $('subtitle_font_size_hide').value = subtitle_font_size;
                }
                
                if(result.link_style){
                    var link_style = result.link_style;
                    var link_color = link_style.link_color.substr(1);
                    if(typeof(template_value) == "undefined"){
                        $('link_color').value = link_color;
                    }
                    $('link_color_hide').value = link_color;
                    var hover_color = link_style.hover_color.substr(1);  
                    if(typeof(template_value) == "undefined"){
                        $('hover_color').value = hover_color;
                    }
                    $('hover_color_hide').value = hover_color;
                    var link_font = link_style.link_font;  
                    if(typeof(template_value) == "undefined"){
                        $('link_font').value = link_font;
                    }
                    $('link_font_hide').value = link_font;
                    var link_font_size = link_style.link_font_size;
                    if(typeof(template_value) == "undefined"){
                        $('link_font_size').value = link_font_size;
                    }
                    $('link_font_size_hide').value = link_font_size;
                }
                
                if(result.text_style){
                    var text_style = result.text_style;
                    var text_color =  text_style.text_color.substr(1);         
                    if(typeof(template_value) == "undefined"){
                        $('text_color').value = text_color;
                    }
                    $('text_color_hide').value = text_color;
                    var text_font = text_style.text_font; 
                    if(typeof(template_value) == "undefined"){
                        $('text_font').value = text_font;
                    }
                    $('text_font_hide').value = text_font;
                    var text_font_size = text_style.text_font_size;
                    if(typeof(template_value) == "undefined"){
                        $('text_font_size').value = text_font_size;
                    }
                    $('text_font_size_hide').value = text_font_size;
                }
            
                if(result.content_general){
                    var content_general = result.content_general;
                    if(typeof(template_value) == "undefined"){
                        $('size_megamenu').value = content_general.size_megamenu;
                    }            
                    if(typeof(template_value) == "undefined"){
                        $('colum').value = content_general.colum;
                    }
                    if(typeof(template_value) == "undefined"){
                        $('size_colum').value = content_general.size_colum;
                    }
                }
                loadColor('click');
                if(result.template_name){
                    var template_name = result.template_name;
                    if(template_name == 'product_listing_02'){
                        $('products_box_title').parentNode.parentNode.show();
                    }else{
                          $('products_box_title').parentNode.parentNode.hide();
                    }
                    if($('menu_type').value == 2){

                        if(template_name == 'product_listing_01'){
                            hideMaincontentGroup('megamenu_featuredcategories');
                        }

                        if(template_name == 'product_listing_02'){
                            showMaincontentGroup('megamenu_featuredcategories');
                        }
                    }
                    var template = new Array();
                    template = getTemplateImage();
                    viewTooltip($('template_id').value,template[$('template_id').value]);
                }
            }
        });
    }
    if(typeof(template_value) == "undefined"){
        $('background_color_default').checked = true;
        $('border_color_default').checked = true;
        $('border_size_default').checked = true;
        $('title_color_default').checked = true;
        $('title_background_color_default').checked = true;  
        $('title_font_default').checked = true;
        $('title_font_size_default').checked = true;
        $('subtitle_color_default').checked = true; 
        $('subtitle_font_default').checked = true;
        $('subtitle_font_size_default').checked = true;
        $('link_color_default').checked = true;
        $('hover_color_default').checked = true;
        $('link_font_default').checked = true;
        $('link_font_size_default').checked = true;
        $('text_color_default').checked = true;
        $('text_font_default').checked = true;
        $('text_font_size_default').checked = true;
    }
    if(typeof(template_value) != "undefined"){
        $('background_color_default').checked = false;
        $('border_color_default').checked = false;
        $('border_size_default').checked = false;
        $('title_color_default').checked = false;
        $('title_background_color_default').checked = false;  
        $('title_font_default').checked = false;
        $('title_font_size_default').checked = false;
        $('subtitle_color_default').checked = false; 
        $('subtitle_font_default').checked = false;
        $('subtitle_font_size_default').checked = false;
        $('link_color_default').checked = false;
        $('hover_color_default').checked = false;
        $('link_font_default').checked = false;
        $('link_font_size_default').checked = false;
        $('text_color_default').checked = false;
        $('text_font_default').checked = false;
        $('text_font_size_default').checked = false;
    }
    
    if(typeof(template_value) == "undefined"){
        $('background_color').disabled = true;
        $('border_color').disabled = true;
        $('border_size').disabled = true;
        $('title_color').disabled = true;
        $('title_background_color').disabled = true;
        $('title_font').disabled = true;
        $('title_font_size').disabled = true;
        $('subtitle_color').disabled = true;
        $('subtitle_font').disabled = true;
        $('subtitle_font_size').disabled = true;
        $('link_color').disabled = true;
        $('hover_color').disabled = true;
        $('link_font').disabled = true;
        $('link_font_size').disabled = true;
        $('text_color').disabled = true;
        $('text_font').disabled = true;
        $('text_font_size').disabled = true;
    }
}

// load color for input text in custom style

function loadColor(click_id){
    chooseColor('background_color','color_background_color',click_id);
    chooseColor('border_color','color_border_color',click_id);
    chooseColor('title_color','color_title_color',click_id);
    chooseColor('title_background_color','color_title_background_color',click_id);
    chooseColor('subtitle_color','color_subtitle_color',click_id);
    chooseColor('link_color','color_link_color',click_id);
    chooseColor('hover_color','color_hover_color',click_id);
    chooseColor('text_color','color_text_color',click_id);
}

// choose color for custom style

function chooseColor(input_id,button_id,click_id){
    var input1 = new colorPicker(input_id,{
        previewElement:input_id
    });
    var cp1 = new colorPicker(button_id,{
        color:'#'+$(input_id).value,
        previewElement:input_id,
        inputElement:input_id,
        eventName:click_id ,
        onShow:function(picker){
            new Effect.Appear(picker.cp);
            return false;
        },
        onHide:function(picker){
            new Effect.Fade(picker.cp);
            return false;
        },
        origColor:'#000000',
        livePreview: true,
        hideOnSubmit:false,
        updateOnChange:false,
        flat: false,
        hasExtraInfo:true,
        extraInfo:function(picker){
            var colors = $A([
                '000000', '993300', '333300', '003300', '003366', '000080', '333399', '333333',
                '800000', 'FF6600', '808000', '008000', '008080', '0000FF', '666699', '808080',
                'FF0000', 'FF9900', '99CC00', '339966', '33CCCC', '3366FF', '800080', '969696',
                'FF00FF', 'FFCC00', 'FFFF00', '00FF00', '00FFFF', '00CCFF', '993366', 'C0C0C0',
                'FF99CC', 'FFCC99', 'FFFF99', 'CCFFCC', 'CCFFFF', '99CCFF', 'CC99FF', 'FFFFFF'
                ]);

            var div = Builder.node('DIV').setStyle({
                padding:'10px 12px'
            });
            colors.each(function(color){
                var div_inner = Builder.node('DIV').setStyle({
                    backgroundColor:'#'+color,
                    cursor:'pointer',
                    width:'10px',
                    height:'10px',
                    'float':'left',
                    border:'2px solid #'+color,
                    margin:'1px'
                });
                div.insert(div_inner);
                div_inner.observe('click',function(ev){
                    picker.setColor(color);
                });
                div_inner.observe('mouseover',function(ev){
                    ev.element().setStyle({
                        border:'2px solid #000'
                    });
                });
                div_inner.observe('mouseout',function(ev){
                    ev.element().setStyle({
                        border:'2px solid #'+color
                    });
                });
            });
            picker.extraInfo.update(div);
        }
    });
}
      

function toggleCustomValueElements(checkbox, container, excludedElements, checked){
    if(container && checkbox){
        var ignoredElements = [checkbox];
        if (typeof excludedElements != 'undefined') {
            if (Object.prototype.toString.call(excludedElements) != '[object Array]') {
                excludedElements = [excludedElements];
            }
            for (var i = 0; i < excludedElements.length; i++) {
                ignoredElements.push(excludedElements[i]);
            }
        }
        //var elems = container.select('select', 'input');
        var elems = Element.select(container, ['select', 'input', 'textarea', 'button', 'img']);
        var isDisabled = (checked != undefined ? checked : checkbox.checked);
        elems.each(function (elem) {
            if (checkByProductPriceType(elem)) {
                var isIgnored = false;
                for (var i = 0; i < ignoredElements.length; i++) {
                    if (elem == ignoredElements[i]) {
                        isIgnored = true;
                        break;
                    }
                }
                if (isIgnored) {
                    return;
                }
                elem.disabled=isDisabled;
                if (isDisabled) {
                    elem.addClassName('disabled');
                } else {
                    elem.removeClassName('disabled');
                }
                if(elem.tagName == 'IMG') {
                    isDisabled ? elem.hide() : elem.show();
                }
            }
        })
    }
} 

    
function json_decode(str_json) {
    // Decodes the JSON representation into a PHP value  
    // 
    // version: 901.2515
    // discuss at: http://phpjs.org/functions/json_decode
    // +      original by: Public Domain (http://www.json.org/json2.js)
    // + reimplemented by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: json_decode('[\n    "e",\n    {\n    "pluribus": "unum"\n}\n]');
    // *     returns 1: ['e', {pluribus: 'unum'}]
    /*
        http://www.JSON.org/json2.js
        2008-11-19
        Public Domain.
        NO WARRANTY EXPRESSED OR IMPLIED. USE AT YOUR OWN RISK.
        See http://www.JSON.org/js.html
    */

    var cx = /[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;
    var j;
    var text = str_json;

    var walk = function(holder, key) {
        // The walk method is used to recursively walk the resulting structure so
        // that modifications can be made.
        var k, v, value = holder[key];
        if (value && typeof value === 'object') {
            for (k in value) {
                if (Object.hasOwnProperty.call(value, k)) {
                    v = walk(value, k);
                    if (v !== undefined) {
                        value[k] = v;
                    } else {
                        delete value[k];
                    }
                }
            }
        }
        return reviver.call(holder, key, value);
    }

    // Parsing happens in four stages. In the first stage, we replace certain
    // Unicode characters with escape sequences. JavaScript handles many characters
    // incorrectly, either silently deleting them, or treating them as line endings.
    cx.lastIndex = 0;
    if (cx.test(text)) {
        text = text.replace(cx, function (a) {
            return '\\u' +
            ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
        });
    }

    // In the second stage, we run the text against regular expressions that look
    // for non-JSON patterns. We are especially concerned with '()' and 'new'
    // because they can cause invocation, and '=' because it can cause mutation.
    // But just to be safe, we want to reject all unexpected forms.

    // We split the second stage into 4 regexp operations in order to work around
    // crippling inefficiencies in IE's and Safari's regexp engines. First we
    // replace the JSON backslash pairs with '@' (a non-JSON character). Second, we
    // replace all simple value tokens with ']' characters. Third, we delete all
    // open brackets that follow a colon or comma or that begin the text. Finally,
    // we look to see that the remaining characters are only whitespace or ']' or
    // ',' or ':' or '{' or '}'. If that is so, then the text is safe for eval.
    if (/^[\],:{}\s]*$/.
        test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, '@').
            replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
            replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {

        // In the third stage we use the eval function to compile the text into a
        // JavaScript structure. The '{' operator is subject to a syntactic ambiguity
        // in JavaScript: it can begin a block or an object literal. We wrap the text
        // in parens to eliminate the ambiguity.

        j = eval('(' + text + ')');

        // In the optional fourth stage, we recursively walk the new structure, passing
        // each name/value pair to a reviver function for possible transformation.

        return typeof reviver === 'function' ?
        walk({
            '': j
        }, '') : j;
    }

    // If the text is not JSON parseable, then a SyntaxError is thrown.
    throw new SyntaxError('json_decode');
}


