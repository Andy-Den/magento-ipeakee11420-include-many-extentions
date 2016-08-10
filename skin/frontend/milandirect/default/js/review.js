/*
 * Used to select/deselect the star rating in popup submit review page
 */
function selectRating(id)
{
	for(index=1; index<=5;index++) {
		if($('rating-star-'+id).hasClassName('rating-unselect')) {
			if(index <= id) {
				$('rating-star-'+index).addClassName('rating-select');
				$('rating-star-'+index).removeClassName('rating-unselect');
			} else {
				$('rating-star-'+index).addClassName('rating-unselect');
				$('rating-star-'+index).removeClassName('rating-select');
			}
			$('Quality_'+index).checked = false;
			$('Price_'+index).checked = false;
			$('Value_'+index).checked = false;
		}
		else {
			$('rating-star-'+index).addClassName('rating-unselect');
			$('rating-star-'+index).removeClassName('rating-select');
		}
	}

	if($('rating-star-'+id).hasClassName('rating-select')) {		
		$('Quality_'+id).checked = true;
		$('Price_'+id).checked = true;
		$('Value_'+id).checked = true;
	}
}

/*
 * Used to select/deselect the star rating in review listing page
 */
function selectReviewRating(id)
{
	for(index=1; index<=5;index++) {
		if(index == id) {
			$('Quality_'+id).checked = true;
			$('Price_'+id).checked = true;
			$('Value_'+id).checked = true;			
		}
		else {
			$('Quality_'+index).checked = false;
			$('Price_'+index).checked = false;
			$('Value_'+index).checked = false;
		}
	}
}