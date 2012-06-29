/*
Simple Accuracy Class 
*/

var Accuracy = function() {};

Accuracy.prototype = {

	name: "accuracy.js",
	version: "0.1",

	initialize: function(){
		var self = this;
		$(".sliderSpan").each(function() {
			// read initial values from markup and remove that
			var valueField = this.id.replace('Slider', 'Value');
			var value = parseInt($(this).text(),10);
			var self = this;
			$(this).empty().slider({
				value: value,
				orientation: "horizontal",
				range: "min",
				animate: true,
				change: function(event, ui) {  
					$('#'+valueField).val($(self).slider("value").toString());				// set slider value
				}
			});
		});
	}
}