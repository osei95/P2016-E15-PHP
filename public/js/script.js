$(function(){

    $('.fancybox').fancybox();

    /* Landing page */
    if($('#landing').length>0){
        resize.call(this);
        $(window).on('resize', function(){
            resize.call(this);
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

    /* Page Dashboard */
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
                            addNews.call(this, {
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
        });

        /* Encourgagements */
        $('#profil[data-id=dashboard]').on('click', '.support', function(evt){
            evt.preventDefault();
            var $this = $(this);
            $.getJSON($this.attr('href'), function(data){
                if(data.action.name=='support') $this.addClass('active');
                else                            $this.removeClass('active');
            });
        });

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
                    addNews.call(this, {
                        news : data,
                        conteneur : '#news'
                    })
                },
                error : function(data){
                    console.log(data);
                }
            });
       });

        $('.details a.details-goal').on('click', function(evt){
            evt.preventDefault();
            $this = $(this);
            var user_id = $this.parents('.details').first().data('user');
            $.ajax({
                dataType: 'json',
                type : 'POST',
                url: '/goal',
                data: { 
                    id : user_id,
                    type : 'from',
                    return : 'to'
                },
                success: function(data){
                    console.log(data);
                    goalPopup.call(this, {
                        type : 'resume',
                        goal : data.goal,
                        user : data.user,
                        conteneur : '.main-section'
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

    /* Page rencontres */
    if($('#rencontres').length>0){

        $('.navigation_rencontres a').on('click', function(evt){
            evt.preventDefault();
            var $this = $(this);

            busy = false;   // On réactive le chargement ajax

            /* On affiche la bonne section */
            $('.navigation_rencontres a').removeClass('actif');
            $this.addClass('actif');

            switchSectionMeetings({type : $this.attr('id')});
        });
    
        /* Ajout d'un objectif en réponse à une invitation */
        $('#main-contain').on('click', '.target', function(evt){
            $this = $(this);
            var user_id = $this.parents('.mes-objectifs').first().data('user');
            console.log(user_id);
            $.ajax({
                dataType: 'json',
                type : 'POST',
                url: '/user',
                data: { 
                    id : user_id
                },
                success: function(data){
                    console.log(data);
                    goalPopup.call(this, {
                        type : 'add',
                        user : data.user,
                        conteneur : '.main-section'
                    })
                },
                error : function(data){
                    console.log(data);
                }
            });
        });

        /* Détails d'un objectif */
        $('#main-contain').on('click', '.details', function(evt){
            $this = $(this);
            var user_id = $this.parents('.mes-objectifs').first().data('user');
            $.ajax({
                dataType: 'json',
                type : 'POST',
                url: '/goal',
                data: { 
                    id : user_id,
                    type : ($('.navigation_rencontres a.actif').attr('id')=='goals'?'from':'to')
                },
                success: function(data){
                    console.log(data);
                    goalPopup.call(this, {
                        type : 'resume',
                        goal : data.goal,
                        user : data.user,
                        conteneur : '.main-section'
                    })
                },
                error : function(data){
                    console.log(data);
                }
            });
        });

        /* Réponse à une invitation */
        $('#main-contain').on('click', '.accept, .refus', function(evt){
            $this = $(this);
            var user_id = $this.parents('.mes-objectifs').first().data('user');
            var reply = $this.hasClass('accept')?1:0;
            var url = ($('.navigation_rencontres a.actif').attr('id')=='goals'?'/goal/reply':'/meetings/reply');
            $.ajax({
                dataType: 'json',
                type : 'POST',
                url: url,
                data: { 
                    user_id : user_id,
                    reply : reply
                },
                success: function(data){
                    if(data.action){
                        switchSectionMeetings.call(this, {type : $('.navigation_rencontres a.actif').attr('id')});
                    }else{
                        alert('Une erreur s\'est produite...');
                    }
                },
                error : function(data){
                    console.log(data);
                }
            });
        });
    }

});

function resize(){
    if(window.innerHeight>670){
        $('#landing>section:nth-child(2)').css('height',window.innerHeight-60+'px');
    }
}

function addNews(params){
    var data = params.news;
    var news_class = 'fright';
    for(var key in data){
        /* Génération des classes dynamiques */
        var support_class = data[key].news.support?'active support tolike':'support tolike';
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

        if(news_class=='fleft') news_class = 'fright';
        else news_class = 'fleft';

        /* Création de la news */
        var news = $('<article>').addClass(activity_class).append( 
            $('<div>').addClass('contain clearfix').append([ 
                $('<div>').addClass('profil '+news_class).append([ 
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
                    $('<p>').html( '<strong><a href="/profil/'+data[key].user.username+'">'+data[key].user.firstname+' '+data[key].user.lastname+'</a></strong>'+data[key].news.content),
                    $('<div>').addClass('separator'),
                    $('<div>').addClass('link').append( 
                        $('<a>').attr('href', 'support/'+key).addClass(support_class)
                    )
                ])
            ])
        );
        /* Ajout de la news */
        $(params.conteneur).append(news);
    }
}

function goalPopup(params){

    if(params.type=='add'){
        var details = $('<form>').attr({
            'action' : '/goal/add',
            'method' : 'POST'
        }).append([ 
            $('<input>').attr({
                'name' : 'user_id',
                'type' : 'hidden',
                'value' : params.user.id
            }),
            $('<div>').addClass('line clearfix').append([
                $('<div>').addClass('left').append(
                    $('<p>').text('Kilomètres à parcourir :')
                ),
                $('<div>').addClass('right').append(
                    $('<input>').attr({
                        'name' : 'distance',
                        'type' : 'text',
                        'value' : '1;200'
                    }).addClass('torangekmbis')
                )
            ]),
            $('<div>').addClass('line clearfix').append([
                $('<div>').addClass('left').append(
                    $('<p>').text('Durée de l\'objectif :')
                ),
                $('<div>').addClass('right').append(
                    $('<input>').attr({
                        'name' : 'duration',
                        'type' : 'text',
                        'value' : '1;20'
                    }).addClass('torangejours')
                )
            ]),
            $('<input>').attr({ 
                'type' : 'submit',
                'value' : 'ENVOYER'
            })
        ]);
    }else if(type='resume'){
        var details = $('<div>').append([ 
            $('<div>').addClass('line clearfix').append([
                $('<div>').addClass('left').append(
                    $('<p>').text('Kilomètres à parcourir :')
                ),
                $('<div>').addClass('right').append(
                    $('<p>').text(Math.round(params.goal.value/1000)+'km'+(Math.round(params.goal.value/1000)?'s':'')) // m->km
                )
            ]),
            $('<div>').addClass('line clearfix').append([
                $('<div>').addClass('left').append(
                    $('<p>').text('Durée de l\'objectif :')
                ),
                $('<div>').addClass('right').append(
                   $('<p>').text(params.goal.duration+' jour'+(params.goal.duration>1?'s':''))
                )
            ]),
            $('<div>').addClass('line clearfix').append([
                $('<div>').addClass('left').append(
                    $('<p>').text('Etat actuel :')
                ),
                $('<div>').addClass('right').append(
                   $('<p>').text(params.goal.achievement+'%')
                )
            ])
        ]);
    }

    /* Création de la popup */
    var popup = $('<div>').append(
        $('<div>').addClass('obj-box clearfix').attr('id', 'detail1').append([
            $('<h2>').text('Objectif à atteindre'),
            $('<h3>').addClass('cible').text('Votre cible'),
            $('<p>').addClass('name').text(params.user.firstname+' '+params.user.lastname),
            $('<div>').addClass('photo').append(
                $('<img>').attr('src', '/medias/users/'+params.user.id+'/profil.jpg')
            ),
            $('<div>').addClass('taille').append(
               $('<h4>').text('Taille'),
               $('<p>').text(params.user.height+' cm')
            ),
            $('<div>').addClass('poids').append(
               $('<h4>').text('Poids'),
               $('<p>').text(params.user.weight+' kg')
            ),
            $('<div>').addClass('age').append(
               $('<h4>').text('Age'),
               $('<p>').text(params.user.age+' ans')
            ),
            $('<h3>').addClass('objectif').text('Votre objectif'),
            details
        ])
    );
    $(params.conteneur).append(popup);   
    $(popup).fancybox().click();
    $(popup).off('click');
    $('.torangekmbis').ionRangeSlider({type:"single", postfix: " km"});
    $('.torangejours').ionRangeSlider({type:"single", postfix: " jours"});
}

function switchSectionMeetings(params){
    $.ajax({
        dataType: 'json',
        type : 'POST',
        url: '/meetings',
        data: { 
            type : params.type
        },
        success: function(data){
            $('#main-contain').empty();
            console.log(data);
            switch(params.type){
                case 'followers':
                case 'meetings':
                    $('#main-contain').append(
                        $('<section>').addClass('contain').attr('id', 'result').append( 
                            $('<div>').addClass('list-result clearfix')
                        )
                    );
                    for(var key in data){
                        var line = $('<div>').append([
                            $('<img>').attr('src', '/medias/users/'+data[key].user.id+'/profil.jpg'),
                            $('<div>').append([
                                $('<h2>').text(data[key].user.firstname+' '+data[key].user.lastname),
                                $('<p>').text(data[key].user.city),
                                $('<p>').text(data[key].user.age),
                                $('<a>').attr('href', '/profil/'+data[key].user.username).text('Voir le profil'),
                            ])
                        ]);
                        $('#main-contain section.contain .list-result').append(line);
                    }
                    break;
                 case 'goals':
                 case 'invitations':
                    if(Object.keys(data).length>0){
                        $('#main-contain').append(
                            $('<ul>').addClass('list')
                        );
                        for(var key in data){
                            var line_class = 'normal';
                            var line_message = '';
                            var line_butons ='';
                            if(params.type=='goals'){
                                /* Si un objectif est en attente de réponse */
                                if(data[key].goal.accepted==0){
                                    line_class = 'fix';
                                    line_message = '<strong><a href="'+data[key].user.username+'">'+data[key].user.firstname+' '+data[key].user.lastname+'</a></strong> vous a fixé un objectif de <strong>'+data[key].goal.value+' '+(data[key].goal.type=='distance'?(data[key].goal.value>1?'kms':'km'):'')+'</strong> à parcourir en '+data[key].goal.duration+' '+(data[key].goal.duration>1?'jours':'jour')+'</strong>';
                                    line_butons = [$('<a>').attr('href', '#').addClass('accept').text('Accepter'), $('<a>').attr('href', '#').addClass('refus').text('Refuser')];
                                    if($('body.woman').length>0) line_butons.push($('<a>').attr('href', '#').addClass('target').text('Fixer un objectif'));
                                /* Si un objectif est refusé */
                                }else if(data[key].goal.accepted==-1){
                                    line_class = 'fix';
                                    line_message = 'Vous avez refusé l’objectif de <strong><a href="'+data[key].user.username+'">'+data[key].user.firstname+' '+data[key].user.lastname+'</a></strong>';
                                /* Si un objectif est réussi */
                                }else if(data[key].goal.achievement>=100){
                                    line_class = 'finish';
                                    line_message = 'Vous avez rempli l’objectif de <strong><a href="'+data[key].user.username+'">'+data[key].user.firstname+' '+data[key].user.lastname+'</a></strong>';
                                    line_butons = $('<a>').attr('href', '/messages/'+data[key].user.username).addClass('chat').text('Discuter');
                                /* Si un objectif est raté */
                                }else if(data[key].goal.deadline<Math.round((new Date()).getTime()/1000)){
                                    line_class = 'done';
                                    line_message = 'Vous n’avez pas rempli l’objectif de <strong><a href="'+data[key].user.username+'">'+data[key].user.firstname+' '+data[key].user.lastname+'</a></strong>';
                                /* Si un objectif est en attente */
                                }else{
                                    line_message = 'Vous avez accepté le défi de <strong><a href="'+data[key].user.username+'">'+data[key].user.firstname+' '+data[key].user.lastname+'</a></strong>';
                                    line_butons = $('<a>').attr('href', '#').addClass('details button').text('Voir les détails');
                                }
                            }else{
                                /* Si l'utilisateur est invité */
                                if(data[key].invitation.state==0){
                                    line_class = 'normal';
                                    if(data[key].invitation.from=='me'){
                                        line_message = 'Vous avez envoyé une invitation à <strong><a href="'+data[key].user.username+'">'+data[key].user.firstname+' '+data[key].user.lastname+'</a></strong>';
                                    }else{
                                        /* Si il y a un objectif en cours */
                                        if(data[key].goal.state=='false'){
                                            line_class = 'fix';
                                            line_butons = [$('<a>').attr('href', '#').addClass('accept').text('Accepter'), $('<a>').attr('href', '#').addClass('refus').text('Refuser')];
                                            if($('body.woman').length>0) line_butons.push($('<a>').attr('href', '#').addClass('target').text('Fixer un objectif'));
                                            line_message = '<strong><a href="'+data[key].user.username+'">'+data[key].user.firstname+' '+data[key].user.lastname+'</a></strong> vous a envoyé une invitation';
                                        }else{
                                            line_message = 'Vous avez envoyé un défi à <strong><a href="'+data[key].user.username+'">'+data[key].user.firstname+' '+data[key].user.lastname+'</a></strong>';
                                            line_butons = $('<a>').addClass('details button').attr({
                                                'href' : '#',
                                                'data-goal' : data[key].goal.id
                                            }).text('Voir les détails');
                                        }
                                    }
                                /* Si l'utilisateur a déjà refusé l'invitation */
                                }else if(data[key].invitation.state==-1){
                                    line_class = 'fix';
                                    if(data[key].invitation.from=='me'){
                                        line_message = 'Vous avez refusé l\'invitation de <strong><a href="'+data[key].user.username+'">'+data[key].user.firstname+' '+data[key].user.lastname+'</a></strong>';
                                    }else{
                                        line_message = '<strong><a href="'+data[key].user.username+'">'+data[key].user.firstname+' '+data[key].user.lastname+'</a></strong> a refusé votre invitation';
                                    }
                                /* Si l'utilisateur a déjà accepté l'inviation */
                                }else{
                                    line_class = 'finish';
                                    line_butons = $('<a>').attr('href', '/messages/'+data[key].user.username).addClass('chat').text('Discuter');
                                    if(data[key].invitation.from=='me'){                                    
                                        line_message = '<strong><a href="'+data[key].user.username+'">'+data[key].user.firstname+' '+data[key].user.lastname+'</a></strong> a accepté votre invitation';
                                    }else{
                                        line_message = 'Vous avez accepté l\'invitation de <strong><a href="'+data[key].user.username+'">'+data[key].user.firstname+' '+data[key].user.lastname+'</a></strong>';
                                    }
                                }
                            }
                            var line = $('<li>').addClass('mes-objectifs clearfix').attr('data-user', data[key].user.id).append([
                                $('<div>').addClass('fleft').append( 
                                    $('<div>').append(
                                         $('<img>').attr('src', '/medias/users/'+data[key].user.id+'/profil.jpg')
                                    )
                                ),
                                $('<div>').addClass('fleft '+line_class).append( 
                                    $('<p>').html(line_message)
                                ),
                                $('<div>').addClass('fright').append(line_butons)
                            ]);
                            $('#main-contain .list').append(line);
                        }
                    }else{
                        $('#main-contain').append(
                            $('<p>').addClass('empty').text('-- Vous n\'avez pas encore '+(params.type=='goals'?'d\'objectif.':'d\'invitation')+' --')
                        );
                    }
                    break;
            }
        },
        error : function(data){
            console.log(data);
        }
    });
}