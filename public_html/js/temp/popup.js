/*
	Popup Class 
*/

var Popup = function () {};

Popup.prototype = {

	name: "popup.js",												// name
	version: "0.1",													// version
	aTemplate: new Template(),										// add template instance
	template: "",													// empty on initialization
	preWrapper: "popupWrapper-",									// prefix for wrappers
	preAnchor: "popupAnchor-",										// prefix for anchors
	comment: new Comment(),											// add instance of comment
	sharing: null,													// nothing set
	stickyAnchorName: "",											// make sure this is empty by default...
	stickyWrapperName: "",											// make sure this is empty by default...
	article: null,
	user: null,

	initialize: function(uuid, article, user){
		this.aTemplate.get('popup');								// get the template for the popup
		this.template = this.aTemplate.templateHTML;				// set the html template for the popup
		this.article = article;
		this.comment.initialize(uuid, this);						// initialize comment instance (see comment.js for details)...
		this.user = user;
	},
	
	wrapperId: function(id){
		return "#"+this.preWrapper+id;
	},
	
	anchorId: function(id){
		return "#"+this.preAnchor+id;
	},

	create: function(args){
		$('body').append($.mustache(this.template, args));			// create the popup
		this.comment.getAndLoad(args.uuid);							// load comments
	},
	
	setStickyElementNames: function(id){
		this.stickyAnchorName = this.anchorId(id);
		this.stickyWrapperName = this.wrapperId(id);
	},
	
	hideAll: function(){
		var self = this;
		$(".formErrors").hide();
		$(".formErrorsList").html("");
		$(".highlightedText").each(function(){
			if(self.stickyAnchorName != "#"+name){
				self.hideAnchor(this.id);
			}
		});
	},
	
	// hide popup from anchor name
	hideAnchor: function(name){
		var self = this;
		if(self.stickyAnchorName == "" || self.stickyAnchorName == "#"+name){
			anchor = $("#"+name);
			id = name.replace(this.preAnchor,'');
			if(anchor.is(".highlightedText")){
				if (!$(self.anchorId(id)).is('.hover')){
					$(self.wrapperId(id)).hide();
					$(self.wrapperId(id)).css({top:0,left:0});
				}
			}
			self.stickyAnchorName = "";
			self.stickyWrapperName = "";
		}
	},
	
	// show popup from anchor name
	showAnchor: function(name){
		var self = this;
		self.hideAll();
		anchor = $("#"+name);
		id = name.replace(this.preAnchor,'');
		if(anchor.is(".highlightedText") && (self.stickyAnchorName == "" || self.stickyAnchorName == "#"+name)){
			$(self.wrapperId(id)).show();
			$(self.wrapperId(id)).position({
				"my": "left top",
				"at": "left bottom",
				"of": anchor,
				"collision": "none none"
			});
			$(self.wrapperId(id)).hide();
			$(self.wrapperId(id)).fadeIn();
			self.stickyAnchorName = "";
			self.stickyWrapperName = "";
		}
	},
	
	addAnchorHoverIntent: function(){
		var self = this;
		var config = {    
			timeout: 300,    
			out: function(){
				//self.hideAnchor(this.id);
			},
			over: function(){
				self.showAnchor(this.id);
			}
		};
		$(".highlightedText").hoverIntent(config);									// keeping the hover intent for fun...
		$(".highlightedText").click(function(){										// add click action to support ipads...
			self.showAnchor(this.id);
		});
	},
	
	showWrapper: function(name){
		var self = this;
		if(self.stickyWrapperName == "" || self.stickyWrapperName == "#"+name){
	        id = name.replace(self.preWrapper, '');
			$(self.anchorId(id)).addClass("hover");
			$(self.wrapperId(id)).show();
			self.stickyAnchorName = "";
			self.stickyWrapperName = "";
		}
    },
 
	hideWrapper: function(name){
		var self = this;
		if(self.stickyWrapperName == "" || self.stickyWrapperName == "#"+name){
			id = name.replace(self.preWrapper, '');
			$(self.anchorId(id)).removeClass("hover");
			$(self.wrapperId(id)).hide();        
			$(self.wrapperId(id)).css({top:0,left:0});
			self.stickyAnchorName = "";
			self.stickyWrapperName = "";
		}
    },

	addPopupHoverIntent: function(){
		var self = this;
		var config = {    
			timeout: 300,    
			out: function(){
				self.hideWrapper(this.id);
			},
			over: function(){
				self.showWrapper(this.id);
			}
		};
		$(".popupWrapper").hoverIntent(config);
	},
	
	addCloseLinkListeners: function(){
		var self = this;
		$(".closeLink").click(function(){
			id = this.id.replace('closePopup-','');
		    name = self.wrapperId(id).replace(/#/,'');
			self.hideWrapper(name);
		});
	},
	
	addCommentSubmissionListeners: function(){
		var self = this;
		
		// submit comment listener
		$("input[id*=submitComment-]").click(function(){
			slctn_uuid = this.id.replace('submitComment-','');
			self.comment.save(slctn_uuid);
		    return false;
		});
		
		// submit accuracy & user sentiment button listeners
		$("input[id*=submitAccuracy-]").click(function(){
			slctn_uuid = this.id.replace('submitAccuracy-','');
			self.comment.save(slctn_uuid);
		    return false;
		});
		
		// show comment form
		$("input[id*=AddComment-]").click(function(){
			slctn_uuid = this.id.split('-')[1];
			$("#formErrors-"+slctn_uuid).hide();
			$("#formErrorsList-"+slctn_uuid).html();
			formAnchor = $('#'+this.id.replace('AddComment','FormAnchor'));
			$("div[id*=FormButtons]").show();
			$('#'+this.id.replace('AddComment','FormButtons')).hide();
			$('#commentAdded-'+slctn_uuid).val(1);
			var commentForm = $("#formWrapper-"+slctn_uuid);
			commentForm.hide();
			commentForm.insertAfter(formAnchor);
			commentForm.fadeIn();
			return false;
		});
		
		// close comment form
		$("input[id*=closeCommentForm-]").click(function(){
			slctn_uuid = this.id.split('-')[1];
			$('#commentAdded-'+slctn_uuid).val(0);
			$("#formWrapper-"+slctn_uuid).fadeOut();
			id = $("#formWrapper-"+slctn_uuid).prev().attr('id').replace('FormAnchor','FormButtons');
			$('#'+id).fadeIn();
			// find a way to reset only the comment fields and leave the sliders or reset the slider values after resetting the form. latter seems simpler.
			// find buttons div and show buttons
			return false;
		});
		
		
	}

}
