$(function(){
	var socket = io.connect('http://php.h3.erwan.co:8880/');

	socket.emit('login', {
		from : $('input[name=from]').val(),
		to : $('input[name=to]').val(),
	});

	socket.on('receiveMessage',function(params){
		var messages = params.messages;
		for(var key in messages){
			$('#messages').append(
				$('<li></li>').addClass('message').append([
					$('<p></p>').text(messages[key].username).addClass('username'),
					$('<p></p>').text(messages[key].content).addClass('message')
				])
			);
		}
	});

	$('#message').on('submit', function(evt){
		evt.preventDefault();
		socket.emit('sendMessage', {
			message : $('textarea[name=message]').val()
		});
		$('textarea[name=message]').val('');
	});

})