/*
Selection Class 
*/

var Selection = function() {};

Selection.prototype = {
	
	name: "selection.js",
	version: "0.1",
	article: null,
	uuid: null,
	selections: [],
	popup: null,
	accuracy: new Accuracy(),										// setting the accuracy object
	allowed: false,
	template: "<span class='highlightedText' id='popupAnchor-{{uuid}}'>{{selection}}</span>",
	sharing: new Sharing(),
	types: { 
		Q: { css: 'quote', display: "Quotation" }, 
		U: { css: 'user', display: "User Defined" }, 
		S: { css: 'statistic', display: "Statistic" }, 
		E: { css: 'entity', display: "Entity" }
	},
	
	initialize: function(article,popup){
		this.uuid = article.uuid;
		this.article = article;
		this.get();
		this.popup = popup;
		this.popup.uuid = popup;
		this.popup.sharing = this.sharing;
		
		// add event for selection of text, the control of if selections are allowed are done inside checkSelection...
		var self = this;
		$('document').ready(function(){
			$("p").mousedown(function(){
				self.popup.hideAll();
			});
			
			$("p").mouseup( function() {
				saved = self.checkSelection(self.getSelectedText());
				if(!saved){
					self.popup.hideAll();
					$("#selectionErrors").toggle('highlight', {}, 500 );
				}
			});	
		});
	},
	
	selectionExists: function(uuid){
		var self = this;
		exists = false;
		_.each(self.selections, function(aSelection){
			if (aSelection.slctn_uuid == uuid){
				exists = true;
			}
		})
		return exists;
	},
	
	allowSelections: function(){
		this.allowed = true;
	},
	
	disallowSelections: function(){
		this.allowed = false;
	},

	// get the stored text selections
	get: function() {
		var self = this;
		$.ajax({
			url: 'REPLACE_IFDB_URL/article/' + self.uuid + '/selection',
			type: 'GET',
			dataType: 'JSON',
			async: false,															// synchronous call for now			
			success: function( data ) {
				self.selections = data.records;										// set selections
				console.log( data );
			},
			error: function( jqXHR, textStatus, errorThrown ){
				console.log( 'Get stored selections call failed for article id: ' + self.uuid );
				console.log( jqXHR );
				console.log( textStatus );
				console.log( errorThrown );
			}
		});
	},
	
	process: function(aSelection){
		var self = this;
		// only add selection if not previously loaded...
		if (typeof(aSelection) != "undefined" && !$(this.popup.anchorId(aSelection.slctn_uuid)).is('.highlightedText')){
			_selText = aSelection.slctn_value;
			
			// Create the popup anchor
			args = {
				uuid: aSelection.slctn_uuid, 
				selection: _selText.toString(),
				sharing: self.sharing.getLinkTemplates(encodeURIComponent(self.article.selectionUrl(aSelection.slctn_uuid)), _selText, 'selection'),
				user_id: this.popup.user.user_id,
				full_name: this.popup.user.full_name
			};
			$("p").each(function () {
				$(this).html($(this).html().replace(_selText.toString(),$.mustache(self.template, args)));
			});
			
			//  Create popup wrapper...
			this.popup.create(args);					// create popup
		}
		// add a highlight color per as the selection type...
		if (typeof(aSelection) != "undefined"){
			$(this.popup.anchorId(aSelection.slctn_uuid)).addClass(this.types[aSelection.slctn_type].css);
		}
	},
	
	load: function(){
		var self = this;
		$("span[id*="+this.popup.preAnchor+"]").addClass('highlightedText');
		_.each(this.selections, function( aSelection ) {
			self.process(aSelection);
	    });
		this.addEventsAndListeners();
	},
	
	addEventsAndListeners: function(){
		// add hover intents and persist the popups...
		this.popup.addAnchorHoverIntent();
		//this.popup.addPopupHoverIntent();						// do not add event to hover as it is sticky...
		this.popup.addCloseLinkListeners();						
		this.popup.addCommentSubmissionListeners();		
		this.accuracy.initialize();	
	},
	
	save: function(selectedText) {
		var self = this;
		$.ajax({
			url: 'REPLACE_IFDB_URL/article/' + self.uuid + '/selection/create',
			dataType: 'JSONP',
			data: {
				selection: selectedText.toString()
			},
			beforeSend: function(){
				var left = $(window).width()/2-200;
				$("#selectionAjaxSpinner").css('left',left);
				$("#selectionAjaxSpinner").fadeIn();
			},
			success: function( data ) {
				aSelection = data.record;
				if(typeof(aSelection) != 'undefined'){
					self.selections.push(aSelection);
					self.process(aSelection);
					self.addEventsAndListeners();
					var sel = window.getSelection ? window.getSelection() : document.selection;
					if (sel) {
						if (sel.removeAllRanges) {
							sel.removeAllRanges();
						} else if (sel.empty) {
							sel.empty();
						}
					}
					self.popup.showAnchor(self.popup.anchorId(aSelection.slctn_uuid).replace('#',''));
					console.log( data );
					$("#selectionAjaxSpinner").fadeOut();
				} else{
					$("#selectionAjaxSpinner").hide();
					$("#selectionErrorsList").append("<li>You cannot select text over paragraphs.</li>");
					var left = $(window).width()/2-200;
					$("#selectionErrors").css('left',left);
					$("#selectionErrors").fadeIn();	
				}
			},
			error: function( jqXHR, textStatus, errorThrown ){
				console.log( 'pin response fail' );
				console.log( jqXHR );
				console.log( textStatus );
				console.log( errorThrown );
			}
		});
	},
	
	checkSelection: function(selectedText){
		if(this.allowed == true){
			enoughWords = this.checkWordCount(selectedText,null);
			overlappingSelection = this.checkIfOverlappingOrWithinSelection(selectedText);
			if (enoughWords && !overlappingSelection){
				this.save(selectedText);
				return true;
			} else{
				if(selectedText.toString() != ''){
					$("#selectionErrorsList").html('');
					if(!enoughWords){
						$("#selectionErrorsList").append("<li>You have to select at least 4 words.</li>");
					}
					if(overlappingSelection){
						$("#selectionErrorsList").append("<li>Your selection is overlapping or inside an existing selection.</li>");
					}
					var left = $(window).width()/2-200;
					$("#selectionErrors").css('left',left);
					return false;
				}
				return true;
			}
		}
	},

	checkWordCount: function( selectedText, iterations ) {
		selectedText = selectedText.toString();
		iterations = typeof( iterations ) != 'undefined' ? iterations : 1;

		if( selectedText.indexOf( ' ' ) != -1 ) {
			if( iterations < 3 ) {								// needs to have at least 4 words, which is 3 spaces... 
				return this.checkWordCount( selectedText.substring( selectedText.indexOf( ' ' ) + 1 ), ++iterations );
			}
			return true;
		}
		return false;
	},
	
	checkIfOverlappingOrWithinSelection: function(selectedText){
		// check if you have overlapping spans
		strippedSelectedText = selectedText.toString();
		strippedSelectedText = strippedSelectedText.replace(/<span>/gi,'');
		strippedSelectedText = strippedSelectedText.replace(/<\/span>/gi,'');
		if (strippedSelectedText.length  != selectedText.toString().length ){
			return true;
		}
		// check if you are inside the span...
		var value = "";
		if (selectedText.focusNode){
			value = $(selectedText.focusNode.parentNode).attr('class');
		} else {
			value = $(selectedText.parentElement()).attr('class');
		}
		if(typeof(value) != 'undefined' && value.toString().split(' ')[0] == 'highlightedText'){
			return true;
		}
		return false;
	},


	getSelectedText: function() {
		var txt = '';
		if (window.getSelection) {
			txt = window.getSelection();
		}
		else if (document.getSelection) {
			txt = document.getSelection();
		}
		else if (document.selection) {
			txt = document.selection.createRange().text;
		}
		else {
			return;
		}
		return txt;
	}

}