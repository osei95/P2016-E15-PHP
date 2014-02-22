var last_date = null;
var init = true;

$(function(){
	var GLOBALS = {
		url : 'http://php.h3.erwan.co',
		port : '8880'
	}
	var socket = io.connect(GLOBALS.url+':'+GLOBALS.port);


	$('.friends-talk .details').on('click', function(evt){
		evt.preventDefault();

		init = true;
		$('#conversation-block .conversation-actions').empty();

		/* On affiche le bloc de saisie */
		$('#conversation-block').removeClass('hidden');

		/* On affiche le loader */

		var id_user = $(this).data('id');
		$.ajax({
            url: GLOBALS.url+"/profil/session",
            type: "GET",
            dataType: 'json',
            success: function(data){
            	console.log('success');
            	console.log(data);
            	socket.emit('login', {
				from : {
					token : data.user.token
				},
				to : {
					id : id_user
				}
			});
            } 
        });
	});


	$('#conversation-tfchat textarea.conversation-tftextarea').keydown(function(evt) {
	    if (evt.keyCode == 13) {
	    	evt.preventDefault();
	        $(this.form).submit();
	    }
	});


	socket.on('receiveMessage',function(params){

		/* On cache le loader */

		var messages = params.messages;
		console.log(messages);
		if(last_date==null)	last_date=messages[0].message_date;
		for(var key in messages){
			var html_message = $('<div>').addClass(messages[key].message_from_class).append(
				$('<div>').addClass(messages[key].message_from_class+'_block').append([
					$('<div>').addClass(messages[key].message_from_class+'_pics').append(
						$('<div>').addClass('pics').append(
							$('<img>').attr({'src':'/medias/users/'+messages[key].message_from+'/profil.jpg', alt:'Photo de profil'})
						)
					),
					$('<div>').addClass(messages[key].message_from_class+'_talk').append([
						$('<span>').addClass('clock_icon').text(messages[key].message_time),
						$('<p>').addClass('message').html(messages[key].message_content)
					])
				])
			);
			if(init){
				if(messages[key].message_date!=last_date || key==messages.length-1){
					var div_date = $('<div>').addClass('date').append([
						$('<div>').addClass('separator'),
						$('<p>').text(last_date),
						$('<div>').addClass('separator')
					]);
					if(messages[key].message_date!=last_date){
						$('#conversation-block .conversation-actions').prepend(div_date);
						last_date=messages[key].message_date;
					}
					$('#conversation-block .conversation-actions').prepend(html_message);
					if(key==messages.length-1){
						$('#conversation-block .conversation-actions').prepend(div_date);
					}
				}else{
					$('#conversation-block .conversation-actions').prepend(html_message);
				}
			}else{
				$('#conversation-block .conversation-actions').append(html_message);
			}
		}
		if(init==true) init=false;
		$('#conversation-block .conversation-actions').animate({ scrollTop: $('#conversation-block .conversation-actions')[0].scrollHeight}, 1000);
	});

	/* Envoi d'un message */
	$('#conversation-tfchat').on('submit', function(evt){
		evt.preventDefault();
		socket.emit('sendMessage', {
			message : $('#conversation-tfchat textarea.conversation-tftextarea').val()
		});
		$('#conversation-tfchat textarea.conversation-tftextarea').val('');
	});

})