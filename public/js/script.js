$(document).ready(function() {
    $(".fancybox").fancybox();
    $(function() {
        $( "#accordion" ).accordion({
            "collapsible":true,
            active: 2
        });
    });
    $("#autoincrement").focus(function(){
        $(".villeauto").css("display","block");
    });
    $(".villeauto li").click(function(){
        $("#autoincrement").val($(this).text());
        $(".villeauto").css("display","none");
    });
    
    $('a[href^="index.php#"]').click(function() {  
      link = $(this).attr('href'); 
      if($(link ).length>=1)
        hauteur=$(link).offset().top;
      else
        hauteur = $("section#"+link.substr(10,link.length-1)).offset().top;
        console.log(hauteur);
      $('body').animate({scrollTop: hauteur}, 600);
      return false;  
    });


    if($("body").attr("id")=="landing"){
        resize();
        
        window.onresize = function() {
            resize();
        };

        $(document).scroll(function() {
            if($(document).scrollTop()+$(window).height()>(860+250)){
                $("#landing #concept .fleft img").animate({right:'0', opacity:1}, 1500);
            }
            if($(document).scrollTop()+$(window).height()>(860+500+250)){
                $("#landing>section:nth-child(4)>div>div>img").animate({left:'0', opacity:1}, 1500);
            }
        });
    }
    function resize(){
        if(window.innerHeight>670){
            $("#landing>section:nth-child(2)").css('height',window.innerHeight-60+'px');
        }
    }

    $(".torangekm").ionRangeSlider({type:"double", postfix: " km"});
    $(".torangeage").ionRangeSlider({type:"double", postfix: " ans"});
    $(".torangetaille").ionRangeSlider({type:"double", postfix: " cm"});
    $(".torangepoids").ionRangeSlider({type:"double", postfix: " kg"});
    $(".torangecal").ionRangeSlider({type:"double", postfix: " kcal"});
    $(".torangejours").ionRangeSlider({type:"single", postfix: " jours"});
    $(".torangekmbis").ionRangeSlider({type:"single", postfix: " km"});

    
});
