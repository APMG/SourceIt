/*
	Simple Social Sharing Class 
*/

var Sharing = function() {};

Sharing.prototype = {

	name: "sharing.js",
	version: "0.1",
	messages: {
		selection: {
			twitter: "Check out article snippet: {{selection}} - ",
			facebook: "Check out this article snippet: {{selection}} - "
		},
		comment: {
			twitter: "I have commented on an article snippet: {{comment}} - ",
			facebook: "I have commented on an article snippet: {{comment}} - "
		}
	},
	buttons: {
		twitter: "http://twitter.com/share?url={{url}}&text={{message}}",
		facebook: "http://www.facebook.com/sharer.php?t={{message}}&u={{url}}"
	},
	
	getLinkTemplates: function(url,msg,type){
		args = { url: url };
		args[type] = msg;
		return this.getSharingArgs(this.getArgs(args, this.messages[type]));
	},
	
	getArgs: function(args,messageTemplate){
		return {
			twitter: { url: args.url, message: encodeURI($.mustache(messageTemplate.twitter, args))},
			facebook: { url: args.url, message: encodeURI($.mustache(messageTemplate.facebook, args))}
		}
	},
	
	getSharingArgs: function(args) {
		return {
			twitter: $.mustache(this.buttons.twitter, args.twitter),	
			facebook: $.mustache(this.buttons.facebook, args.facebook)
		}
	}
}