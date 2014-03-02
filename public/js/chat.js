var last_date = null;
var init = true;

socket.on('loadedPage',function(params){

	if(params.status=='200'){

		$('.friends-talk .details').on('click', function(evt){
			evt.preventDefault();

			$(this).removeClass('notification');

			init = true;
			last_date = null;
			$('#conversation-block .conversation-actions').empty();

			/* On affiche le bloc de saisie */
			$('#conversation-block').removeClass('hidden');

			/* On affiche le loader */
			var id_user = $(this).data('id');
			socket.emit('connectIM', {
				to : {
					id : id_user
				}
			});
		});
	}else{
		alert('Erreur de connexion au serveur');
	}
});

/* On scroll en bas des messages si une conversation est chargée au démarrage */
if($('#conversation-block .conversation-actions>div').length>0){
	$('#conversation-block .conversation-actions').animate({ scrollTop: $('#conversation-block .conversation-actions')[0].scrollHeight}, 1000);
}

/* L'appui sur la touche entré dans le textarea permet l'envoi du message */
$('#conversation-tfchat textarea.conversation-tftextarea').keydown(function(evt) {
    if (evt.keyCode == 13) {
    	evt.preventDefault();
        $(this.form).submit();
    }
});

/* Notification de messages */
socket.on('notifyMessageIM',function(params){
	var friend_id = params.from;
	$('.friends-talk .details[data-id='+friend_id+']').addClass('notification');
});

/* Reception de messages */
socket.on('receiveMessageIM',function(params){

	/* On cache le loader */

	var messages = params.messages;
	console.log(messages);
	if(messages.length>0){
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
	}
	if(init==true) init=false;
	$('#conversation-block .conversation-actions').animate({ scrollTop: $('#conversation-block .conversation-actions')[0].scrollHeight}, 1000);
});

/* Envoi d'un message */
$('#conversation-tfchat').on('submit', function(evt){
	evt.preventDefault();
	socket.emit('sendMessageIM', {
		message : $('#conversation-tfchat textarea.conversation-tftextarea').val()
	});
	$('#conversation-tfchat textarea.conversation-tftextarea').val('');
});