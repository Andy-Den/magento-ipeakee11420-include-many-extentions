/**
 * fetch view from backend
 * @param id string
 * @param reloadurl string
 */

if(typeof Balance=='undefined') {
    var Balance = {};
}
var productIds = '';
var global_real_key;
//create ajax class
Balance.Ajax = Class.create();
//create ajax class method
Balance.Ajax.prototype = {
	//define loading url
	initialize: function(){
		this.views = [];
	},
	encodeBase64: function(what){
	    var base64_encodetable = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
	    var result = "";
	    var len = what.length;
	    var x, y;
	    var ptr = 0;

	    while( len-- > 0 )
	    {
	        x = what.charCodeAt( ptr++ );
	        result += base64_encodetable.charAt( ( x >> 2 ) & 63 );

	        if( len-- <= 0 )
	        {
	            result += base64_encodetable.charAt( ( x << 4 ) & 63 );
	            result += "==";
	            break;
	        }

	        y = what.charCodeAt( ptr++ );
	        result += base64_encodetable.charAt( ( ( x << 4 ) | ( ( y >> 4 ) & 15 ) ) & 63 );

	        if ( len-- <= 0 )
	        {
	            result += base64_encodetable.charAt( ( y << 2 ) & 63 );
	            result += "=";
	            break;
	        }

	        x = what.charCodeAt( ptr++ );
	        result += base64_encodetable.charAt( ( ( y << 2 ) | ( ( x >> 6 ) & 3 ) ) & 63 );
	        result += base64_encodetable.charAt( x & 63 );

	    }

	    return result;
	},
	setLoadUrl:function(url){
		this.url = url;
		return this;
	},
	addView: function(view){
		this.views.push(view);
		return this;
	},
	//get view by id
	getViewById: function(id){
		for (var i in this.views){
			var view = this.views[i];
			if(view.id == id){
				return view;
			}
		}
		return false;
	},
	//get view by tag
	getViewByTag: function(tag){
		var views = [];
		for (var i in this.views){
			var view = this.views[i];
			if(view.tag == tag){
				views.push(view);
			}
		}
		if(views.length){
			return views;
		}else{
			return false;
		}
	},
	load: function(){
		var views = this.views;                
		this.loadViews(views);
	},
	reloadView: function(view){
		var views = [];
		views.push(view);
		this.loadViews(views);
	},
	reloadViews: function(views){
		this.loadViews(views);
	},
	loadViews: function(views){
		var blocks = [], dt = new Object, allowJsExecute= [];
		if(views.length){
			views.sort(function(a, b){return a.sortNumber - b.sortNumber;});			
			for (var i = 0; i < views.length; i++){
				var view = views[i];
				blocks[view.id] = $(view.id);
				dt[view.id] = view.data;
				allowJsExecute[view.id] = view.allowJsExecute;
			}
			var ref = this.encodeBase64(window.location.href.toString());
			var myhost = "" + this.url.match(/:\/\/(.[^/]+)/)[1];
			if (myhost != window.location.hostname) {
				this.url = window.location.protocol + '//' + window.location.hostname + '/ajax/index/fetchview';
			}                        
			new Ajax.Request(this.url, {
				method: 'post',
				parameters: {data:Object.toJSON(dt),uenc:ref},
				onSuccess: function(transport){
					var htmls = transport.responseText.evalJSON();
                                        
					for(var property in htmls){
						if ('real_form_key' === property) {
							var real_key = htmls[property] + '';
                                                        global_real_key = real_key;
							$$('input[name="form_key"]').each(function(e){e.value=real_key});
							$$('form[action*="form_key"]').each(function(e){
								actionurl=e.action;
								actionurl=actionurl.replace(/[\/]+form_key\/[^\/]+/, '/form_key/'+real_key);
								actionurl=actionurl.replace('([\&\?])form_key=([^\&])', '$1form_key='+real_key + '$2');
								//console.log(actionurl);
								e.action=actionurl;
							});
							$$('a[href*="form_key"]').each(function(e){
								link=e.href;
								link=link.replace(/[\/]+form_key\/[^\/]+/, '/form_key/'+real_key);
								link=link.replace('([\&\?])form_key=([^\&])', '$1form_key='+real_key + '$2');
								//console.log(link);
								e.href=link;
							});
							$$('form').each(function(e){e.enable();});
							continue;
						}
                                                if(typeof blocks[property] === 'undefined' || !blocks[property]) continue;

						block = blocks[property];
						js = allowJsExecute[property];
						newHtml = htmls[property];
						newHtml = newHtml.replace('criteoProductIds', productIds);
						block.innerHTML = newHtml;
                        //blocks[property].innerHTML = htmls[property];
						if(js){
							var html = block.innerHTML, scripts = html.extractScripts();
							scripts.map(function(script){
								return jQuery.globalEval(script);
							});
						}
					}
					jQuery("select:not(.super-attribute-select), input[type='checkbox'], input[type='radio']").uniform({selectAutoWidth: false});
					rwd.skipLinks('#top-cart');
				}
			});
		}
	}
},

//create ajax view class
Balance.AjaxView = Class.create();

Balance.AjaxView.prototype = {
	initialize: function(id, data, sortNumber, allowJsExecute, tag){
		if(tag == 'undefined') tag = null;
		if(typeof allowJsExecute == 'undefined') allowJsExecute = true;
		this.id = id;
		this.data = data;
		this.sortNumber = sortNumber;
		this.allowJsExecute = allowJsExecute;
		this.tag = tag;
	}
};
//alert(allowJsExecute);
var biAjax = new Balance.Ajax();

