var Emailfriend = Class.create();
Emailfriend.prototype = {
	initialize: function(getPostUrl){
        this.getPostUrl = getPostUrl;
		this.x = '';
		this.y = '';
		this.containerSuffix = '';
	},
	
	
	getEmailFriendForm : function()
	{
		var request = new Ajax.Request(
			 this.getPostUrl,
			 {
				method: 'get',
				parameters: '',
				onComplete: function(transport) {
					$('share-email').each(function(el) {
							$('email-popup').style.left = el.offsetLeft + "px";
							$('email-popup').style.top  = el.offsetTop + "px";
					});
				},
				onFailure: function(transport){
					alert('An error occurred while processing your request');
				}
			 }
		);	
	}
}
