/*
User Class 
*/

var User = function() {};

User.prototype = {

	name: "user.js",
	version: "0.1",
	user_id: -1, 
	full_name: "",
	email: "",
	popup: null,
	article: null,

	loggedIn: function(){
		if(this.user_id == -1){
			return false;
		}else{
			return true;
		}
	},

	/*
		Get user
	*/
	get: function () {
		var self = this;
		$.ajax({
			url: 'REPLACE_IFDB_URL/connect',
			dataType: 'JSONP',
			async: false,
			data: { article_uuid: self.article.uuid, json: true },
			success: function( msg ) {
				if(typeof(msg.login_url)!="undefined"){
					window.location = msg.login_url;
				} else{
					self.user_id = msg.user_id;									// set the userid
					self.full_name = msg.full_name;
					$('#sourceIt').append('<a href="'+msg.logout_url+'" class="logoutLink">Logout</a>');
				}
			},
			error: function( jqXHR, textStatus, errorThrown ){
				console.log( ' Could not log the user in.' );
				console.log( jqXHR );
				console.log( textStatus );
				console.log( errorThrown );
			}
		});
	}
};