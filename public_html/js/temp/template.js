/*
Simple Template Class 
*/

var Template = function() {};

Template.prototype = {

	name: "template.js",
	version: "0.1",
	templateHTML: "",
	
	get: function(template) {
		var self = this;
		$.ajax({
			url: 'REPLACE_IFDB_URL/js/templates/' + template + '.php',
			type: 'GET',
			dataType: 'html',
			async: false,																	// making sure the call stops the load for now...
			success: function( data ) {
				self.templateHTML = data;
				console.log( data );
			},
			error: function( jqXHR, textStatus, errorThrown ){
				console.log( 'pin response fail' );
				console.log( jqXHR );
				console.log( textStatus );
				console.log( errorThrown );
			}
		});
	}
}