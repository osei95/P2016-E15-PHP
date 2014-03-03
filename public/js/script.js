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

     /* Landing Dashboard */
    if($('#profil[data-id=dashboard]').length>0){
        var  busy = false;
        $(window).on('scroll', function(){
           if($(window).scrollTop() + $(window).height() == $(document).height() && !busy) {
                busy = true;
                $.ajax({
                    dataType: 'json',
                    type : 'POST',
                    url: '/news',
                    data: { 
                        type : $('.navigation_news a.actif').attr('id'),
                        offset : $('#news article').length-1,
                        limit : 8
                    },
                    success: function(data){
                        if(Object.keys(data).length>0){
                            addNews({
                                news : data,
                                conteneur : '#news'
                            });
                            busy = false;
                        }else{
                            $('#news').append($('<p>').addClass('loading').text('-- Toutes les actualités sont chargées --'));
                        }
                    },
                    error : function(data){
                        console.log(data);
                    }
                });
            }
        })

       $('.navigation_news a').on('click', function(evt){
            evt.preventDefault();
            var $this = $(this);

            busy = false;   // On réactive le chargement ajax

            /* On affiche la bonne section */
            $('.navigation_news a').removeClass('actif');
            $this.addClass('actif');

            $.ajax({
                dataType: 'json',
                type : 'POST',
                url: '/news',
                data: { 
                    type : $this.attr('id'), 
                    offset : 0,
                    limit : 8
                },
                success: function(data){
                    $('#news').empty();
                    addNews({
                        news : data,
                        conteneur : '#news'
                    })
                },
                error : function(data){
                    console.log(data);
                }
            });
       });
    }

    /* Page profil */
    if($('#profil[data-id=profil]').length>0){

        /* Suivi de l'activité d'une personne */
        $('#suivre').on('click', function(evt){
            evt.preventDefault();
            var $this = $(this);
            $.getJSON($this.attr('href'), function(data){
                if(data.action.name=='follow')  $this.addClass('active');
                else                            $this.removeClass('active');
            });
        });

        /* Encourgagements */
        $('.support').on('click', function(evt){
            evt.preventDefault();
            var $this = $(this);
            $.getJSON($this.attr('href'), function(data){
                if(data.action.name=='support') $this.addClass('active');
                else                            $this.removeClass('active');
            });
        });

        /* Demande de discussion */
        $('#discuter').on('click', function(evt){
            evt.preventDefault();
            var id_user = $(this).data('id');
            console.log(id_user);
            socket.emit('talkRequest', {
                to : {
                    id : id_user
                }
            });
        })

        /* Réponse à la demande de discussion */
        socket.on('talkRequestResponse',function(params){
            if(typeof(params.relationship)!='undefined'){
                if(params.relationship===true){
                    window.location.href = $('a#discuter').attr('href');
                }else if(params.action=='addGoal'){
                    alert('Voulez-vous ajouter un objectif ?');
                }else if(params.action=='sentNotification'){
                    alert('Demande envoyée');
                }else if(params.action=='notificationAlreadySent'){
                    alert('Demande déjà envoyée');
                }
            }
        });
    }

    /* Page recherche et inscription */
    if($('#recherche').length>0 || $('#inscription').length>0){

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

function addNews(params){
    var data = params.news;
    for(var key in data){
        /* Génération des classes dynamiques */
        var support_class = data[key].news.support?'active':'';
        var activity_class = 'bg-';
        switch(data[key].news.type){
            case 'activity_calories':
                activity_class+='cal';
                break;
            case 'activity_event':
                activity_class+='event';
                break;
            case 'activity_friend':
                activity_class+='friend';
                break;
            default :
                activity_class+='km';
        }

        /* Création de la news */
        var news = $('<article>').addClass(activity_class).append( 
            $('<div>').addClass('contain clearfix').append([ 
                $('<div>').addClass('profil fleft').append([ 
                    $('<a>').attr('href', '/profil/'+data[key].user.username).addClass('name').text(data[key].user.firstname+' '+data[key].user.lastname), 
                    $('<div>').addClass('friend-pics').append(
                        $('<div>').addClass('friend-pics').append(
                            $('<div>').addClass('targets').append(
                                $('<div>').addClass('pics').append(
                                    $('<img>').attr('src', '/medias/users/'+data[key].user.id+'/profil.jpg')
                                )
                            )
                        )
                    ),
                    $('<p>').addClass('date').text(data[key].news.date)
                ]),
                $('<div>').addClass('live-news fleft').append([
                    $('<p>').html( '<span>'+data[key].user.firstname+' '+data[key].user.lastname+'</span>'+data[key].news.content),
                    $('<div>').addClass('separator'),
                    $('<div>').addClass('link').append( 
                        $('<a>').attr('href', 'support/'+key).addClass(support_class).text('J\'encourage')
                    )
                ])
            ])
        );
        /* Ajout de la news */
        $(params.conteneur).append(news);
    }
}