/*
SourceIt Class 
*/

jQuery.fn.reset = function () {
	$(this).each (function() { this.reset(); });
}

var SourceIt = function() {};

SourceIt.prototype = {
	
	name: "SourceIt",
	version: "0.1",
	article: new Article(),
	popup: new Popup(),
	selection: new Selection(),
	sharing: new Sharing(),
	active: false,
	button: $('#sourceItButton'),
	doc_url: window.location.href.split("#")[0],
	current_hash: location.hash,
	new_hash: "",
	activeSelectionId: -1,
	user: new User(),
	

	// initialize the sourceIt object
	initialize: function(){
		this.setNewHash();														// filter the hash from selection ids...
		this.article.setDocUrl(this.new_hash);									// we need to decide if we consider urls with hashes as unique urls 
		
		this.article.fetch();													// assign the article using the API...
		this.popup.initialize(this.article.uuid,this.article, this.user);		// initialize popup...
		this.selection.initialize(this.article,this.popup);						// initialize selection instance
		this.user.article = this.article;
		
		var self = this;														// need to set a global variable for scope reasons...
		
		// add on-document-ready actions...
		$('document').ready(function(){

			// add styles to header and add the ajax spinner...
			$('head').append('<link rel="stylesheet" href="REPLACE_IFDB_URL/css/sourceit.css" type="text/css" />');
			$('head').append('<link rel="stylesheet" href="REPLACE_IFDB_URL/css/jquery-ui.css" type="text/css" />');
			$('#sourceIt').append('<div id="sourceItAjaxSpinner" style="display:none;"><img src="REPLACE_IFDB_URL/img/ajax-loader.gif" /> Processing ... </div>');
			$('body').append('<div id="selectionAjaxSpinner" class="selectionSpinner" style="display:none;"><img src="REPLACE_IFDB_URL/img/ajax-loader.gif" /> Processing your selection... </div>');
			$('body').append('<div id="selectionErrors" style="display:none;" class="selectionErrors">Oops! Something went wrong with with your selection. Please check the issues we found listed below. Click anywhere on this popup to close it.<ul class="selectionErrorsList" id="selectionErrorsList"></ul></div>');

			// add click event to toggle button...
			$("#sourceItButton").click(function(){
				if(!self.user.loggedIn()){
					self.user.get();
				}
				
				// give the user a chance to login first
				if(self.user.loggedIn()){
					$("#sourceItAjaxSpinner").toggle('highlight', {}, 200 );			// toggle the spinner
					setTimeout(function() {
						self.toggle();
					}, 200 );
					$("#sourceItAjaxSpinner").toggle('highlight', {}, 500 );			// toggle the spinner
				}
			});
		
			// make sure you click outside the popups to close them
			$(document).click(function(event){
			    if(!$(event.target).is(".highlightedText") && !$(event.target).is(".popupWrapper") && $(".popupWrapper").find($(event.target)).length == 0){
					self.popup.hideAll();											// hide all the popups
		    	}
			});
			
			$('#selectionAjaxSpinner').click(function(){
				$(this).hide();														// hide the spinner by clicking in the div
			})
			
			$('#selectionErrors').click(function(){
				$(this).hide();														// hide the errors by clicking in the popup
			})
			
			// fade in the button
			$("#sourceItButton").effect('fade', {}, 500, self.callback);			// show the source button
			self.detectSelectionInUrlHash();										// detect if there is a selection inside the hash
		});
	},
	
	callback: function(){
		setTimeout(function() {
						$( "#sourceItButton" ).hide().fadeIn();
					}, 1000 );
	},
	
	toggle: function(){
		if(this.active == true){												// if the toggle is on...
			this.button.html("SourceIt: Off");									// set button html
			this.button.removeClass('on');										// remove the class
			$('.highlightedText').attr('class','');								// take out all classes for spans
			this.selection.disallowSelections();								// disallow user selections
			this.active = false;												// set state variable to OFF
		}else{																	// if the toggle is off...
			this.button.html("SourceIt: On");									// set button html
			this.button.addClass('on');											// add class on
			this.selection.load();												// load the selections
			this.selection.allowSelections();									// allow user selections
			this.active = true;													// set state variable to ON
		}
	},
	
	// process the hash and filter out any notions of slctn_id
	setNewHash: function(){
		current_hash_arr = this.current_hash.replace(/#/,'').split('&');				// create an array of the hash values
		new_hash_arr = [];																// initialize new hash array
		$.each(current_hash_arr, function(index, value){								// loop through the hash values
			arr = value.split("=");														// check to see if it is a variable setting
			if (arr[0] != "slctn" && arr[0] != "_"){													// make sure only slctn_id is listed
				new_hash_arr.push(value);												// add if not slctn_id
			}
		});
		// only set the new hash if there are hash parameters, as we do not want a trailing #...
		this.new_hash = "#" + new_hash_arr.join("&");								// construct new hash string
		if (this.new_hash.replace(/#/,'') == "" || this.new_hash.replace(/#/,'') == "_=_"){
			this.new_hash = "";
		}
	},
	
	// detects a selected popup in the url and brings up a sticky popup...
	detectSelectionInUrlHash: function(){
		var self = this;
		current_hash_arr = this.current_hash.replace(/#/,'').split('&');				// create an array of the hash values
		$.each(current_hash_arr, function(index, value){								// loop through all hash values
			arr = value.split("=");
			if (arr[0] == "slctn" ){													// only slctn_id is interesting here of course...
				if(typeof(arr[1]) != "undefined"){										// only load defined popups to avoid NPE...
					self.activeSelectionId = arr[1];									// set the activeSelectionId for convenience
					if(self.selection.selectionExists(self.activeSelectionId)){			// only scroll to and show the popup if the popup exists...
						name = self.popup.anchorId(self.activeSelectionId);				// get the name of the popup anchor...
						self.toggle();													// turn sourceit on as we have want to show a popup...
						var top = $(name).offset().top;									// get the top position...
						$('html, body').animate({									
							scrollTop: top												
						}, 500);														// animate scroll to selection...
						self.popup.showAnchor(name.replace(/#/,''));					// show popup...
						self.popup.setStickyElementNames(self.activeSelectionId);		// make the popup sticky as the user is not hovering over it...
					}
				}
			}
		});
	}
};
