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

		if(typeof params.user!='undefined' && typeof params.user.token!='undefined' && typeof params.page!='undefined' && typeof params.page.name!='undefined'){

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
			connection.query('SELECT user_id, user_username, user_gender FROM user WHERE user_key="'+current.user.token+'"', function(err, rows, fields) {
				if (err) throw err;

				// On enregiste l'utilisateur
				current.user.id = rows[0]['user_id'];
				current.user.username = rows[0]['user_username'];
				current.user.gender = rows[0]['user_gender'];

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

				if(params.page.name=='chat'){
					// On supprime les notifications de type message
					connection.query('DELETE FROM notification WHERE user_id='+current.user.id+' AND notification_type="message" AND notification_seen=0', function(err, rows, fields) {
						connection.end();
					});
				}


			});
		}
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

		if(typeof params.to!='undefined' && typeof params.to.id!='undefined'){

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
		}
	});

	/* Envoi d'un message */
	socket.on('sendMessageIM',function(params){

		if(typeof params.message!='undefined'){

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
					if(users[current.im.to.id].page.name=='chat'){
						// Conversation en cours avec cette personne
						if(users[current.im.to.id].page.params.to==current.user.id){
							message['message_from_class'] = 'friend';
							users[current.im.to.id].socket.emit('receiveMessageIM', {
								messages : [message]
							});
						}else{
							// La personne parle avec quelqu'un d'autre
							users[current.im.to.id].socket.emit('notifyMessageIM', {
								from : current.user.id
							});
						}
					}else{
						// La personne est connectée, mais sur une autre page
						connection.query('SELECT notification_id FROM notification WHERE user_id='+current.im.to.id+' AND notification_type="message" AND notification_from='+current.user.id+' AND notification_seen=0', function(err, rows, fields) {
							if (err) throw err;
							if(rows.length==0){
								connection.query('INSERT INTO notification (user_id, notification_type, notification_content, notification_from) VALUES ('+current.im.to.id+', "message", "", '+current.user.id+')', function(err, rows, fields) {
									if (err) throw err;
									users[current.im.to.id].socket.emit('receiveNotification', {
										type : 'message'
									});
									connection.end();
								});
							}
						});
					}
				}else{
					// La personne n'est pas connectée
					connection.query('SELECT notification_id FROM notification WHERE user_id='+current.im.to.id+' AND notification_type="message" AND notification_from='+current.user.id+' AND notification_seen=0', function(err, rows, fields) {
						if (err) throw err;
						if(rows.length==0){
							connection.query('INSERT INTO notification (user_id, notification_type, notification_content, notification_from) VALUES ('+current.im.to.id+', "message", "", '+current.user.id+')', function(err, rows, fields) {
								if (err) throw err;
							});
						}
					});
				}
			});
		}
	});

	/* --------------------------------- */
	/* ---------- Page profil ---------- */
	/* --------------------------------- */

	/* Demande de discution */
	socket.on('talkRequest',function(params){
		console.log(params);

		if(typeof params.to!='undefined' && typeof params.to.id!='undefined'){

			var user_id_to = params.to.id;
			var connection = mysql.createConnection(DB_INFOS);

			connection.connect();
			connection.query('SELECT * FROM relationship WHERE ((request_from='+user_id_to+' AND request_to='+current.user.id+') OR (request_from='+current.user.id+' AND request_to='+user_id_to+'))', function(err, rows, fields) {
				if (err) throw err;
				var response = {};
				response.relationship = false;
				if(rows.length>0){
					if(rows[0]['request_state']==1){
						response.relationship = true;
					}else{
						response.action = 'notificationAlreadySent';
					}
				}else{
					// Homme
					if(current.user.gender===0){
						connection.query('INSERT INTO relationship (request_from, request_to, request_state, request_time) VALUES ('+current.user.id+', '+user_id_to+', 0, '+new Date().getTime()+')', function(err, rows, fields) {
							if (err) throw err;
							if(typeof(users[user_id_to])!='undefined'){
								users[user_id_to].socket.emit('receiveNotification', {
									type : 'relation'
								});
							}
							response.action = 'sentNotification';
						});
					// Femme
					}else{
						response.action = 'addGoal';
					}
				}
				socket.emit('talkRequestResponse', response);
				connection.end();
			});
		}
	});

});

function date_fr(dateObjet){
	var jours = new Array('dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi');
	var mois = new Array('janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre');
	return jours[dateObjet.getDay()]+' '+dateObjet.getDate()+' '+mois[dateObjet.getMonth()]+' '+dateObjet.getFullYear();
}