$(document).ready(function() {
    
    $(document).scroll(function() {
        if($(document).scrollTop()+$(window).height()>(860+250)){
            $("#landing>section:nth-child(3)>div>div>img").animate({right:'0', opacity:1}, 1500);
        }
        if($(document).scrollTop()+$(window).height()>(860+500+250)){
            $("#landing>section:nth-child(4)>div>div>img").animate({left:'0', opacity:1}, 1500);
        }
    });

});
