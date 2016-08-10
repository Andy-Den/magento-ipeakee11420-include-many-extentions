var Checkboxselect = Class.create();
Checkboxselect.prototype = {
	initialize: function(getPostUrl){
        this.getPostUrl = getPostUrl;
		this.checked = false;
	},
	getselectparams:function(name,email,checked)
	{
		
		var request = new Ajax.Request(
			 this.getPostUrl,
			{
				method: 'get',
				evalJS: 'force',
				parameters: {'name':name, 'email-address':email , 'checked':checked },
				onSuccess: function(response) {
					// you get the json back and can populate your html with it (e.g. your tab)
					//alert(response.responseText);
				},
				onFailure: function(response){
					//alert('An error occurred while processing your request');
				}
			}
		);	
	}	
}

function selectcheckbox(obj){
	var name= $F('receiver_name');
	var email= $F('receiver_email');
	var checked= $F('checkreference');
	checkboxSelect.getselectparams(name,email,checked);
}

