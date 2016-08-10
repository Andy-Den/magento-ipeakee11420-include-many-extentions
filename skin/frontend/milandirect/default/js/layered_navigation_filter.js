/*
 * Used to select/deselect the filters in layered navigation
 */
function selectFilter(name, value)
{
	var elementValue = $F(name);
	if(elementValue != '') {
		var elementValueToArray = elementValue.split('_');
		if(elementValueToArray.indexOf(value) == -1) {
			$(name).value = $F(name) + '_' + value;
			$(name+'_'+value).toggleClassName('select');
		} else {
			elementValueToArray.splice(elementValueToArray.indexOf(value),1);
			$(name).value = elementValueToArray.join('_');
			$(name+'_'+value).toggleClassName('select');
		}
	} else {
		$(name).value = value;
		$(name+'_'+value).toggleClassName('select');
	}
	document.product_filter_form.submit();   
}

/*
 * Used to show/hide element
 */
function showhide(id)
{
	$(id).toggleClassName('active');
	$(id+'_content').toggle();
}