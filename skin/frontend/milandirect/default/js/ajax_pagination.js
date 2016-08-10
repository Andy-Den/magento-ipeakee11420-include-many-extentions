jQuery(document).ready(function(){
    init('header', 3);
    init('sidebar', 4);
});

function init(id, items)
{
    //how much items per page to show
    var show_per_page = items;
    //getting the amount of elements inside content div
    var number_of_items = jQuery('#pagination_container_'+id).children().size();

    //calculate the number of pages we are going to have
    var number_of_pages = Math.ceil(number_of_items/show_per_page);

    //set the value of our hidden input fields
    jQuery('#current_page_'+id).val(0);
    jQuery('#show_per_page_'+id).val(show_per_page);

    //now when we got all we need for the navigation let's make it

    /*
    what are we going to have in the navigation?
        - link to previous page
        - links to specific pages
        - link to next page
    */
    if(number_of_items > show_per_page){    var navigation_html = '<ul class="cart-pagination"><li class="previous_link"><a href="javascript:previous(\''+  id +'\');">Previous</a></li>';
        var current_link = 0;
        while(number_of_pages > current_link){
            navigation_html += '<a style="display:none;" class="page_link_'+id+'" href="javascript:go_to_page(' + current_link +')" longdesc="' + current_link +'"><b>'+ (current_link + 1) +'</b></a>';
            current_link++;
        }
        navigation_html += '<li id="pagination_info_'+ id +'">'+updatePaginationInfo(id, 1)+'</li><li class="next_link"><a href="javascript:next(\''+ id +'\');">Next</a></li></ul>';
        jQuery('#page_navigation_'+id).html(navigation_html);
    }

    //add active_page class to the first page link
    jQuery('#page_navigation_'+ id +' .page_link_'+id+':first').addClass('active_page_'+id);

    //hide all the elements inside content div
    jQuery('#pagination_container_'+id).children().css('display', 'none');

    //and show the first n (show_per_page) elements
    jQuery('#pagination_container_'+id).children().slice(0, show_per_page).css('display', 'block');
}

function previous(id){
    new_page = parseInt(jQuery('#current_page_'+id).val()) - 1;
    //if there is an item before the current active link run the function
    if(jQuery('.active_page_'+id).prev('.page_link_'+id).length==true){
        goToPage(id, new_page);
    }

}

function next(id){
    new_page = parseInt(jQuery('#current_page_'+id).val()) + 1;
    //if there is an item after the current active link run the function
    if(jQuery('.active_page_'+id).next('.page_link_'+id).length==true){
        goToPage(id, new_page);
    }

}
function goToPage(id, page_num){
    //get the number of items shown per page
    var show_per_page = parseInt(jQuery('#show_per_page_'+id).val());

    //get the element number where to start the slice from
    start_from = page_num * show_per_page;

    //get the element number where to end the slice
    end_on = start_from + show_per_page;

    //hide all children elements of content div, get specific items and show them
    jQuery('#pagination_container_'+id).children().css('display', 'none').slice(start_from, end_on).css('display', 'block');

    /*get the page link that has longdesc attribute of the current page and add active_page class to it
    and remove that class from previously active page link*/
    jQuery('.page_link_'+id+'[longdesc=' + page_num +']').addClass('active_page_'+id).siblings('.active_page_'+id).removeClass('active_page_'+id);

    jQuery('#pagination_info_'+id).html(updatePaginationInfo(id, page_num+1));
    //update the current page input field
    jQuery('#current_page_'+id).val(page_num);
}

function updatePaginationInfo(id, page_num)
{
    //get the number of items shown per page
    var show_per_page = parseInt(jQuery('#show_per_page_'+id).val());
    //get the number of items shown per page
    var total_items = parseInt(jQuery('#total_items_'+id).val());

    var start = ((page_num-1)*show_per_page)+1;
    var end = page_num*show_per_page;
    if(end >= total_items)
        end = total_items;

    var info_html = start + '-' + end + ' of ' + total_items;

    return info_html;
}

