$(function(){

    $('.fancybox').fancybox();

    /* Landing page */
    if($('#landing').length>0){
        resize();
        $(window).on('resize', function(){
            resize();
        });

        $(document).on('scroll', function() {
            if($(document).scrollTop()+$(window).height()>(860+250)){
                $('#landing>section:nth-child(3)>div>div>img').animate({right:'0', opacity:1}, 1500);
            }
            if($(document).scrollTop()+$(window).height()>(860+500+250)){
                $('#landing>section:nth-child(4)>div>div>img').animate({left:'0', opacity:1}, 1500);
            }
        });
    }

    /* Page profil */
    if($('#profil').length>0){
        $('#suivre').on('click', function(evt){
            evt.preventDefault();
            var _this = $(this);
            $.getJSON(_this.attr('href'), function(data){
                if(data.action.name=='follow')  _this.addClass('active');
                else                            _this.removeClass('active');
            });
        });

        $('.support').on('click', function(evt){
            evt.preventDefault();
            var _this = $(this);
            $.getJSON(_this.attr('href'), function(data){
                if(data.action.name=='support') _this.addClass('active');
                else                            _this.removeClass('active');
            });
        });
    }

    /* Page recherche et inscription */
    if($('#recherche').length>0 || $('#inscription').length>0){

        $('.city_autocomplete input[name=city_]').on('focus', function(){
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

        /* Page recherche */
        if($('#recherche').length>0){
            $('.torangekm').ionRangeSlider({type:"double", postfix: " km"});
            $('.torangeage').ionRangeSlider({type:"double", postfix: " ans"});
            $('.torangecal').ionRangeSlider({type:"double", postfix: " kcal"});
            $('.torangetaille').ionRangeSlider({type:"double", postfix: " cm"});
            $('.torangepoids').ionRangeSlider({type:"double", postfix: " kg"});

            $('#accordion').accordion({
                'collapsible' : true,
                active: 2
            });

            $('.search-results section.search-fields form').addClass('hidden');

            $('#edit-search').on('click', function(evt){
                evt.preventDefault();
                $('.search-results section.search-fields form').toggleClass('hidden');
            });

        /* Page inscription */
        }else if($('#inscription').length>0){
            $('.torangetaille').ionRangeSlider({type:"single", postfix: " cm"});
            $('.torangepoids').ionRangeSlider({type:"single", postfix: " kg"});
        }
    }

});

function resize(){
    if(window.innerHeight>670){
        $('#landing>section:nth-child(2)').css('height',window.innerHeight-60+'px');
    }
}