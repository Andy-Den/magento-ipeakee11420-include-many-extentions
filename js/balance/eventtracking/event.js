function pushGoogleEvent(id , a, l) {
    console.log('push id: '+ id + ' act: ' + a + ' label: ' + l);
    _gaq.push(['_trackEvent', id, a, l ]);
};

function trackSignup(e,a,l,check) {
    console.log('sign up');
    if(check)
    {
        pushGoogleEvent(e, a, l );
    }
};


function dotrackevent()
{
    //console.log('dotrack');
    //banner
    var bannerimage = $$('#bannercarousel li a img');
    //console.log(bannerimage);
    if(bannerimage){
        bannerimage.each(function(bimg){
            bimg.observe('click', function trackBanner() {
                //console.log(bimg);
                pushGoogleEvent("Banner", 'click', bimg.id );
            });
        });
    }

    //warranty
    var warranty = $$('.warrenty div.grayboxCurvs_midBg input');
    if(warranty){
        warranty.each(function(w){
            var a = $(w).up('div');
            var l = $(a).select('label').first();
            //console.log(l);
            //console.log(l.getInnerText());
            w.observe('click', function trackWarranty() {
                //console.log(bimg);
                pushGoogleEvent("Warranties", 'Warranty Selected', l.getInnerText() );
            });
        });
    }

    //signup on regpage

};

document.observe('dom:loaded',	function () {
    //console.log('dom loaded');
    dotrackevent();

});
