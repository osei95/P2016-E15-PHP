var GLOBALS = {
	url : 'http://php.h3.erwan.co',
	port : '8880'
}
var socket = io.connect(GLOBALS.url+':'+GLOBALS.port);
var currentPage = $('body').data('id');

$.ajax({
    url: GLOBALS.url+"/profil/session",
    type: "GET",
    dataType: 'json',
    success: function(data){
    	socket.emit('loadPage', {
			user : {
				token : data.user.token,
			},
			page : {
				name : currentPage
			}
		});
    }
});

socket.on('receiveNotification',function(params){
	switch(params.type){
		case 'message':
			var iconPosition = 3
			break;
		case 'relation':
			var iconPosition = 2
			break;
	}
	var selector = 'header .contain div>a:nth-child('+iconPosition+')';

	var nthNotifications = 0;
	if($(selector+'>span').length>0){
		nthNotifications=parseInt($(selector+'>span').text());
		$(selector+'>span').text(nthNotifications+1);
	}else{
		$(selector).append($('<span>').text(1));
	}
});