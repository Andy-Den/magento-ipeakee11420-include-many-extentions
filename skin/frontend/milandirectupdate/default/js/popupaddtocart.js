var Productselect = Class.create();
Productselect.prototype = {
	initialize: function(getPostUrl){
        this.getPostUrl = getPostUrl;
	},
	getproductparams:function(parentId,childId,childType)
	{
		var request = new Ajax.Request(
			 this.getPostUrl,
			 {
				method: 'get',
				evalJS: 'force',
				parameters: {'productId':parentId, 'childProductId':childId,'childProductType':childType },
				onSuccess: function(transport) {
					// Evaluating Ajax Response Text Which is JSON Object
                    var jsonResponse = eval('(' + transport.responseText + ')');
                    //Checking JSON Objects property and performing related action
                    if(jsonResponse.error){
                        alert("Error Occured");
                        return false;
                    }
                    else{
                        //Rediect in the Parent window.
                        parent.location.href = jsonResponse.url;
                    }
				},
				onFailure: function(response){
					alert('An error occurred while processing your request');
				}
			 }
		);	
	}
}
