/*
Article Class 
*/

var Article = function() {};

Article.prototype = {

	name: "article.js",
	version: "0.1",
	uuid: null, 
	message: "",
	doc_url: window.location.href.split("#")[0],
	new_hash: "",

	/*
		Fetch article
	*/
	fetch: function () {
		var self = this;
		$.ajax({
			url: 'REPLACE_IFDB_URL/article/create',
			dataType: 'JSONP',
			async: false,											// synchronous call for now
			data: {
				url: self.doc_url
			},
			success: function( msg ) {
				self.message = msg.message;
				self.uuid = msg.uuid;
			},
			error: function( jqXHR, textStatus, errorThrown ){
				console.log( 'Fetch article call failed for url: ' + self.doc_url );
				console.log( jqXHR );
				console.log( textStatus );
				console.log( errorThrown );
			}
		});
	},
	
	// make sure the url is constant between unique pages...
	setDocUrl: function(new_hash){
		this.new_hash = new_hash;
		this.doc_url += new_hash;
	},
	
	selectionUrl: function(slctn_uuid){
		url = window.location.href.split("#")[0];
		_new_hash = this.new_hash;
		if (_new_hash == ""){
			_new_hash = "#slctn="; 
		} else {
			_new_hash += "slctn=";
		}
		url += _new_hash;
		url += slctn_uuid;
		return url;
	}
};