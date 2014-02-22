var DB_INFOS = {
  host     : '',
  database : '',
  user     : '',
  password : ''
};

var http  = require('http');
var mysql = require('mysql');

httpServer = http.createServer(function(request,response){});

httpServer.listen('9002');

var io = require('socket.io').listen(httpServer);
var users = {};

io.sockets.on('connection',function(socket){

	var current;

	socket.on('login',function(params){

		var connection = mysql.createConnection(DB_INFOS);

		current = {
			from : {
				token : params.from.token,
				id : null
			},
			to : {
				id : params.to.id
			}
		};

		console.log('to');
		console.log(params.to.id);

		/* On récupère l'id de l'utilisateur avec son token */
		connection.connect();
		connection.query('SELECT user_id FROM user WHERE user_key="'+current.from.token+'"', function(err, rows, fields) {
			current.from.id = rows[0]['user_id'];

			if(current.from.id!=null){

				/* On enregiste le socket */
				users[current.from.id] = socket;

				console.log('id: '+current.from.id);

				/* On récupère les précédents messages et on les envoi à l'utilisateur */
				//connection.connect();
				connection.query('SET lc_time_names = "fr_FR"', function() {
					connection.query('SELECT CASE WHEN message.message_from='+current.from.id+' THEN "me" ELSE "friend" END message_from_class, message.message_from, message.message_content, DATE_FORMAT(message.message_time,"%Hh%i") message_time, DATE_FORMAT(message.message_time,"%W %d %M %Y") message_date FROM message WHERE (message.message_from='+current.from.id+' AND message.message_to='+current.to.id+') OR (message.message_from='+current.to.id+' AND message.message_to='+current.from.id+') ORDER BY message_time DESC', function(err, rows, fields) {
						if (err) throw err;
						socket.emit('receiveMessage', {
							messages : rows
						});
						connection.end();
					});
				});

			}else{
				connection.end();
			}
		});

	});

	/* Envoi d'un message */
	socket.on('sendMessage',function(params){

		var message = {};
	  	message['message_content'] = params.message;
	  	message['message_from'] = current.from.id;
	  	message['message_from_class'] = 'me';

	  	var currentDate = new Date();
	  	message['message_time'] = currentDate.getHours()+'h'+currentDate.getMinutes();
	  	message['message_date'] = date_fr(currentDate);

		var connection = mysql.createConnection(DB_INFOS);

		connection.connect();

		connection.query('INSERT INTO message (message_from, message_to, message_content) VALUES ('+current.from.id+', '+current.to.id+', "'+params.message+'")', function(err, rows, fields) {
			if (err) throw err;
			  	socket.emit('receiveMessage', {
				messages : [message]
			});
			if(typeof(users[current.to.id])!='undefined'){
				message['message_from_class'] = 'friend';
				users[current.to.id].emit('receiveMessage', {
				messages : [message]
			  });
			}
			});
		connection.end();
	});

});

function date_fr(dateObjet){
	var jours = new Array('dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi');
	var mois = new Array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre');
	return jours[dateObjet.getDay()]+' '+dateObjet.getDate()+' '+mois[dateObjet.getMonth()]+' '+dateObjet.getFullYear();
}