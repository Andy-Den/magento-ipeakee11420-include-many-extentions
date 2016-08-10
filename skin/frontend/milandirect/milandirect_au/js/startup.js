document.observe("dom:loaded", function() {
  if($('breadcrumb') != undefined && $('breadcrumbs') != null){
      dataBread =   $('breadcrumbs').innerHTML;
      $('breadcrumbs').remove();
      $('breadcrumb').update(dataBread);
  }


  var startUp = new Startup();
  startUp.load(BASE_UNSECURE_URL);
});

Event.observe(window, 'load', function() {
  //setTimeout("showOrderPopup()",1000);
    //jQuery("#order-popup-container").fadeIn("slow");
});

var Startup = Class.create();

Startup.prototype = {

  initialize: function(){
    this.showNewsletter = false;
    this.containerSuffix = '';
  },

  load: function(baseUrl){
    showNewsletterPopup();
  //  showFacebookPopup();
  //  this.setStore(baseUrl);
  },

  setLoadWaiting: function(enable) {
    if (enable) {
      Element.show('loading-mask'+this.containerSuffix);
    }
    else {
      Element.hide('loading-mask'+this.containerSuffix);
    }
  },

  setStore: function(baseUrl){
    var request = new Ajax.Request(
      baseUrl + 'geoip/index/?store=' + STORE,
      {
        evalJS: 'force',
        onFailure: function(response){
          alert('An error occurred while processing your request');
        },

        onSuccess: function(response){
          if (response && response.responseText){
            try{
              responseValues = eval('(' + response.responseText + ')');
              isSuccess = /Store is found for this country.$/.test(responseValues.message);

              if(isSuccess) {
                var startUp = new Startup();
                if(responseValues.storeUrl)
                  window.parent.location = responseValues.storeUrl;
                else {
                  if(responseValues.store)
                    $(responseValues.store + "_flag").addClassName('current');
                  showNewsletterPopup();
                }
              } else {
                showPopup(BASE_UNSECURE_URL + 'geoip/index/store/');
              }
            }
            catch (e) {
            }
          }
        }
      }
      );
  },

  switchStore: function(baseUrl, country){
    setLoadWaiting('-store-popup', true);
    var request = new Ajax.Request(
      baseUrl + 'geoip/index/store',
      {
        method: 'get',
        evalJS: 'force',
        onFailure: function(response){

          alert('An error occurred while processing your request');
        },

        onSuccess: function(response){
          if (response && response.responseText){
            try{
              responseValues = eval('(' + response.responseText + ')');

              isSuccess = /Store is set.$/.test(responseValues.message);
              if(isSuccess) {
                setLoadWaiting('-store-popup', false);
                $('messages').show();
                setTimeout("closePopup()", 1000);
                return true;
              } else {
                return false;
              }
            }
            catch (e) {
            }
          }
        },
        parameters: 'store='+country
      }
      );
  },

  getStore: function(){
    var urls = $F('STORE_URLS').split('~');
    var request = new Ajax.Request(
      BASE_UNSECURE_URL + 'geoip/index/get/',
      {
        evalJS: 'force',
        onFailure: function(response){
          alert('An error occurred while processing your request');
        },

        onSuccess: function(response){
          if (response && response.responseText){
            try{
              responseValues = eval('(' + response.responseText + ')');

              isSuccess = /Store is set.$/.test(responseValues.message);
              if(isSuccess) {
                window.parent.location = responseValues.storeUrl;
              } else {
                window.parent.location = BASE_UNSECURE_URL;
              }
            }
            catch (e) {
              window.parent.location = BASE_UNSECURE_URL;
            }
          } else {
            window.parent.location = urls[1];
          }
        }
      }
      );
  },
    createCookie: function(name,value,days) {
        if (days) {
            var date = new Date();
            date.setTime(date.getTime()+(days*24*60*60*1000));
            var expires = "; expires="+date.toGMTString();
        }
        else var expires = "";
        document.cookie = name+"="+value+expires+"; path=/";
    },
    getCookie: function(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for(var i=0; i<ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1);
            if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
        }
        return "";
    },
    isNewsletterSuggested: function(){
        return this.getCookie('IsNewsletterSuggested');
    },

    // Check if cookie is set (newsletter/subscriber/check), if so showPopup (newsletter/asbscriber/add)
    subscribeToNewletter: function(baseUrl){
        newletterSuggest  =   this.isNewsletterSuggested();
        if(newletterSuggest==""||newletterSuggest==false){
            this.createCookie('IsNewsletterSuggested',true,30);
            showPopup(BASE_UNSECURE_URL + 'newsletter/subscriber/add/');
        }
    },

  // Check if cookie is set (newsletter/subscriber/check), if so showPopup (newsletter/asbscriber/add)
  subscribeToFacebook: function(baseUrl){
    var request = new Ajax.Request(
      BASE_UNSECURE_URL + 'newsletter/subscriber/get',
      {
        method: 'post',
        evalJS: 'force',
        onFailure: function(response){

          alert('An error occurred while processing your request');
        },

        onSuccess: function(response){
          if (response && response.responseText){
            try{

              if (response.responseText == 1) {
                showPopup(BASE_UNSECURE_URL + 'newsletter/facebook/like/');
              }
              /*
              var newsletterObj = JSON.parse (response.responseText);
              var newsletterTimestamp = newsletterObj.Timestamp;
              alert(newsletterTimestamp);
              */
            }
            catch (e) {
            }
          }
        },
        parameters: 'cookie=get'
      }
      );

  },


  setNewletterCookie: function(){
    var request = new Ajax.Request(
      BASE_UNSECURE_URL + 'newsletter/subscriber/check/',
      {
        method: 'post',
        evalJS: 'force',
        onFailure: function(response){

          alert('An error occurred while processing your request');
        },

        onSuccess: function(response){
          if (response && response.responseText){
            try{
              responseValues = eval('(' + response.responseText + ')');
              isSuccess = /Cookie is set.$/.test(responseValues.message);

              if(isSuccess) {
                return true;
              } else {
                return false;
              }
            }
            catch (e) {
            }
          }
        },
        parameters: 'cookie=set'
      }
      );
  },

  setOrderPopupCookie: function(orderId){
    var request = new Ajax.Request(
      BASE_UNSECURE_URL + 'orderpopup/index/',
      {
        method: 'post',
        evalJS: 'force',
        onFailure: function(response){

          alert('An error occurred while processing your request');
        },

        onSuccess: function(response){
          if (response && response.responseText){
            try{
              responseValues = eval('(' + response.responseText + ')');
              isSuccess = /Cookie is set.$/.test(responseValues.message);

              if(isSuccess) {
                return true;
              } else {
                return false;
              }
            }
            catch (e) {
            }
          }
        },
        parameters: 'id='+orderId
      }
      );
  }
}

function showPopupWindow(url)
{
  try {
    showPopup(url);
  } catch(e) {
    alert('Could not process the request. Please try again.');
  }
}

function checkForCallback()
{
  var callback_for = $F('CALLBACK_FOR');
  var startUp = new Startup();
  switch (callback_for) {
    case 'newsletter':
      startUp.setNewletterCookie();
      break;
    case 'order':
      startUp.setOrderPopupCookie();
      break;
    case 'store':
      startUp.getStore();
      break;
    default:
      return true;
      break;
  }
  return true;
}


function switchStore(baseUrl, country)
{
  var startUp = new Startup();
  startUp.switchStore(baseUrl, country);
}

function showNewsletterPopup()
{
  var startUp = new Startup();
  startUp.subscribeToNewletter();
}


function showFacebookPopup()
{
  var startUp = new Startup();
  startUp.subscribeToFacebook();
}



function showStorePopup(baseUrl)
{
  showPopup(BASE_UNSECURE_URL + 'geoip/index/store/');
}

function setLoadWaiting(suffix, status){
  var startUp = new Startup();
  startUp.containerSuffix = suffix;
  startUp.setLoadWaiting(status);
}

function updatePriceHtml()
{
  $$('span.regular-price span[class="price"]').each(function(item){
    var html = $(item).innerHTML;
    if(html.search('<sup>') == -1 && html.search('</sup>') == -1) {
      var html_currency = html.sub('$', '<sup>$</sup>');
      var html_price = html_currency.sub('.', '<sup>') + '</sup>';
      item.update(html_price);
    }
  });

  $$('p.special-price span[class="price"]').each(function(item){
    var html = $(item).innerHTML;
    if(html.search('<sup>') == -1 && html.search('</sup>') == -1) {
      var html_currency = html.sub('$', '<sup>$</sup>');
      var html_price = html_currency.sub('.', '<sup>') + '</sup>';
      item.update(html_price);
    }
  });
}

function updateValue(id, delta) {
  var newValue = parseInt($F('qty_'+id)) + delta;
  $('qty_'+id).value = Math.max(newValue, 1);
}

function updateCartValue(id, delta) {
  var newValue = parseInt($F('qty')) + delta;
  $('qty').value = Math.max(newValue, 1);
}

function showOrderPopup()
{
	var date = new Date();
	var timeNow = date.getTime();
	var seconds = 0;
	var myCookieRun = document.cookie.replace(/(?:(?:^|.*;\s*)OrderPopupUpdatedAt\s*\=\s*([^;]*).*$)|^.*$/, "$1");
	var runPopup  = false;
	if(myCookieRun!='' && myCookieRun!=null){
		myCookieRun = parseInt(myCookieRun);
		seconds = Math.floor((date - (new Date(myCookieRun))) / 1000);
		if(parseInt(seconds)>=30){
			runPopup = true;
			stringCookie = "OrderPopupUpdatedAt="+timeNow;
			document.cookie = stringCookie;
		}
	}else{
		stringCookie = "OrderPopupUpdatedAt="+timeNow;
		document.cookie = stringCookie;
		runPopup = true;
	}
	if(runPopup){
		var request = new Ajax.Request(
	    BASE_UNSECURE_URL + 'orderpopup/index/show/',
	    {
	      method: 'post',
	      evalJS: 'force',
	      onFailure: function(response){

	        alert('An error occurred while processing your request');
	      },

	      onSuccess: function(response){
	        if (response && response.responseText){
	          try{
	            responseValues = eval('(' + response.responseText + ')');
	            isFailure = /No details available.$/.test(responseValues.order_content);
	            if(!isFailure) {
	              $('order-popup-container').update(responseValues.order_content);
	              jQuery("#order-popup-container").fadeIn("slow");
	            } else {
	            }
	          }
	          catch (e) {
	          }
	        }
	      }
	    }
	    );
	}else{
		seconds = parseInt(seconds)*1000;
		setTimeout(function(){
			showOrderPopup();
		},seconds)
	}
}

/*function closeOrderPopup(orderId)
{
  var startUp = new Startup();
  //startUp.setOrderPopupCookie(orderId);
  jQuery("#order-popup").fadeOut("slow");
}*/

function closeOrderPopup()
{
    jQuery("#order-popup").fadeOut("slow");
}

function showStoreAsSelected(store)
{
  if(typeof($("#" + store + "_flag"))=='object'){
    var element = $("#" + store + "_flag");
    if (element !== null) {
        element.addClassName('current');
    }
  }
}

function checkForOtherUrl() {
  jQuery('#popupMiddleContainer').load(BASE_UNSECURE_URL + 'store-switcher-popup/', function(responseText, textStatus, XMLHttpRequest) {
    if(textStatus != 'error')
      loadPopup();
    else {
      alert('Coulnot load popup. Please try again.');
    }
  });
}

function disableTextSelect() {
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
}
