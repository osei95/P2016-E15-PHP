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