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

	/* --------------------------------- */
	/* ------------ Global ------------- */
	/* --------------------------------- */
	socket.on('loadPage',function(params){

		current = {
			user : {
				token : params.user.token,
				id : null
			}
		};

		var connection = mysql.createConnection(DB_INFOS);
		connection.connect();
		// On récupère l'id de l'utilisateur avec son token
		// Penser à la sécurité de la requête !
		connection.query('SELECT user_id FROM user WHERE user_key="'+current.user.token+'"', function(err, rows, fields) {

			// On enregiste l'utilisateur
			current.user.id = rows[0]['user_id'];

			var status = '403';

			if(current.user.id!=null){
				users[current.user.id] = {
					socket : socket,
					page : {
						name : params.page.name,
						params : {}
					}
				};
				status = '200';
			}

			socket.emit('loadedPage', {
				status : status
			});

		});
	});

	socket.on('disconnect', function(){
		if(typeof current!=='undefined' && typeof users[current.user.id]!=='undefined'){
			delete users[current.user.id];
		}
	});



	/* --------------------------------- */
	/* ----------- Page chat ----------- */
	/* --------------------------------- */

	/* Connexion au chat */
	socket.on('connectIM',function(params){

		var connection = mysql.createConnection(DB_INFOS);

		current.im = {
			to : {
				id : params.to.id
			}
		};

		// On enregistre avec qui l'utilisateur chat
		users[current.user.id].page.params.to = params.to.id;

		// On récupère les anciens messages
		connection.connect();
		connection.query('SET lc_time_names = "fr_FR"', function() {
			connection.query('SELECT CASE WHEN message.message_from='+current.user.id+' THEN "me" ELSE "friend" END message_from_class, message.message_from, message.message_content, DATE_FORMAT(message.message_time,"%Hh%i") message_time, message.message_time message_datetime, DATE_FORMAT(message.message_time,"%W %d %M %Y") message_date FROM message WHERE (message.message_from='+current.user.id+' AND message.message_to='+current.im.to.id+') OR (message.message_from='+current.im.to.id+' AND message.message_to='+current.user.id+') ORDER BY message_datetime DESC', function(err, rows, fields) {
				if (err) throw err;
				socket.emit('receiveMessageIM', {
					messages : rows
				});
				connection.end();
			});
		});

	});

	/* Envoi d'un message */
	socket.on('sendMessageIM',function(params){

		var message = {};
	  	message['message_content'] = params.message;
	  	message['message_from'] = current.user.id;
	  	message['message_from_class'] = 'me';

	  	var currentDate = new Date();
	  	message['message_time'] = currentDate.getHours()+'h'+currentDate.getMinutes();
	  	message['message_date'] = date_fr(currentDate);

		var connection = mysql.createConnection(DB_INFOS);
		connection.connect();
		connection.query('INSERT INTO message (message_from, message_to, message_content) VALUES ('+current.user.id+', '+current.im.to.id+', "'+params.message+'")', function(err, rows, fields) {
			if (err) throw err;
			  	socket.emit('receiveMessageIM', {
					messages : [message]
				});
			if(typeof(users[current.im.to.id])!='undefined'){
				if(users[current.im.to.id].page.name='chat'){
					if(users[current.im.to.id].page.params.to==current.user.id){
						message['message_from_class'] = 'friend';
						users[current.im.to.id].socket.emit('receiveMessageIM', {
							messages : [message]
						});
					}else{
						users[current.im.to.id].socket.emit('notifyMessageIM', {
							from : current.user.id
						});
					}
				}
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