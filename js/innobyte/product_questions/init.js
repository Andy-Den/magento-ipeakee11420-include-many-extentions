    
var inno = inno || {};

inno.product_questions = {
    limitMultiplier : 1,
    sortDirection : 'desc',
    
    voteQuestion : function(element,url) { 
        
        new Ajax.Request(url, {
            method:'post',
            onSuccess: function(transport){
                var json = transport.responseText.evalJSON();
                
                alert(json.message);
                if(json.response == 'success'){
                    $(element.parentNode).update("<em>"+json.text+"</em>");
                    $(element.parentNode).innerHTML;
                }
            }
        });
    },
    
    sortQuestions : function(url,multiplier,isSortable) { 
    
        this.limitMultiplier = multiplier + this.limitMultiplier;
        var sortBy = $('pq-question-sorting').value;

        if(isSortable){
            if (this.sortDirection == 'desc') {
                $('sorting-asc').show();
                $('sorting-desc').hide();
                this.sortDirection = 'asc';
            } else if (this.sortDirection == 'asc') {
                $('sorting-desc').show();
                $('sorting-asc').hide();
                this.sortDirection = 'desc';
            }
        }
        
        $('pq-question-loader').show();
        new Ajax.Request(url, {
            method : 'post',
            parameters : { sort: this.sortDirection, sortby: sortBy, limit: this.limitMultiplier },
            onSuccess : function(transport){
                $('answered-questions').replace(transport.responseText);
                $('answered-questions').innerHTML;
                $('pq-question-loader').hide();
            }
        });
    },
    
    showQuestionForm : function() { 
        $('product-question-wrapper').show();
        $('html, body').animate({
            scrollTop: $('.product-question-wrapper').offset().top
        }, 500);
        $('response').hide();
        $('pq-question-form').show();
    },
    
    hideQuestionForm : function() {
        $('response').hide();
        $('product-question-wrapper').show();
        $('pq-question-form').show();
        jQuery('#innobyte_product_questions_customer_name').val('')
        jQuery('#innobyte_product_questions_customer_email').val('');
        jQuery('#innobyte_product_questions_content').val('');
    }
};
    