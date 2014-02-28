$(document).ready(function() {
    resize();
    if($('#recherche').length>0 || $('#inscription').length>0){

        $('.torangetaille').ionRangeSlider({type:"double", postfix: " cm"});
        $('.torangepoids').ionRangeSlider({type:"double", postfix: " kg"});

        $('.city_autocomplete input[name=city]').on('focus', function(){
            $('.city_autocomplete ul').css('display','block');
        });
        $('.city_autocomplete input[name=city]').on('keyup', function(){
            $.ajax({
                dataType: 'json',
                type : 'POST',
                url: '/search/cities',
                data: { 
                    name : $('.city_autocomplete input[name=city]').val() 
                },
                success: function(data){
                    $('.city_autocomplete ul').empty();
                    for(var cpt=0; cpt<data.length; cpt++){
                        $('.city_autocomplete ul').append(
                            $('<li>').attr('data-slug', data[cpt].city_slug).text(data[cpt].city_name)
                        );
                        if(cpt==5) break;
                    }
                    console.log(data);
                },
                error : function(data){
                    console.log(data);
                }
            });
        });
        $('.city_autocomplete ul').on('click', 'li', function(evt){
            evt.preventDefault();
            $('.city_autocomplete input[name=city]').val($(this).text());
            $('.city_autocomplete input[name=city_slug]').val($(this).data('slug'));
            $('.city_autocomplete ul').css('display','none');
        });

        if($('#recherche').length>0){
            $('.torangekm').ionRangeSlider({type:"double", postfix: " km"});
            $('.torangeage').ionRangeSlider({type:"double", postfix: " ans"});
            $('.torangecal').ionRangeSlider({type:"double", postfix: " kcal"});

            $('#accordion').accordion({
                'collapsible' : true,
                active: 2
            });

            $('.search-results section.search-fields form').addClass('hidden');

            $('#edit-search').on('click', function(evt){
                evt.preventDefault();
                $('.search-results section.search-fields form').toggleClass('hidden');
            });

        }else if($('#inscription').length>0){

        }
    }

    window.onresize = function() {
        resize();
    };
    
    $('a[href^="index.php#"]').click(function() {  
      link = $(this).attr('href'); 
      if($(link ).length>=1)
        hauteur=$(link).offset().top;
      else
        hauteur = $('section#'+link.substr(10,link.length-1)).offset().top;
        console.log(hauteur);
      $('body').animate({scrollTop: hauteur}, 600);
      return false;  
    });

    $(document).scroll(function() {
        if($(document).scrollTop()+$(window).height()>(860+250)){
            $('#landing>section:nth-child(3)>div>div>img').animate({right:'0', opacity:1}, 1500);
        }
        if($(document).scrollTop()+$(window).height()>(860+500+250)){
            $('#landing>section:nth-child(4)>div>div>img').animate({left:'0', opacity:1}, 1500);
        }
    });

    $(document).ready(function() {
		$('.fancybox').fancybox();
	});
});

function resize(){
    if(window.innerHeight>670){
        $('#landing>section:nth-child(2)').css('height',window.innerHeight-60+'px');
    }
}
