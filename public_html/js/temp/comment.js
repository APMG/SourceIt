/*
    Comment Class 
*/

var Comment = function() {};

Comment.prototype = {
    
    name: "comment.js",
    version: "0.1",
	uuid: null,
	popup: null,
	sharing: new Sharing(),
	template: "<li style='display:none;' id='comment-{{comment.cmmnt_uuid}}'>\
		<a name='commentAnchor-{{cmmnt_uuid}}'></a>\
		{{comment.cmmnt_comment}}\
		<div class='commentMetaData'>\
			{{comment.cmmnt_full_name}}\
			<span class='metaDataSpacer'>|</span>\
			submitted at {{comment.cmmnt_cre_dtim}}\
			<ul id='sharingWrapper-{{uuid}}' class='commentSharingContainer'>\
				<li class='twitterIcon'><a href='{{sharing.twitter}}' title='Share on Twitter' target='_blank'><img src='REPLACE_IFDB_URL/img/tweet.png' /></a></li>\
				<li><a href='{{sharing.facebook}}' title='Share on Facebook' target='_blank'><img src='REPLACE_IFDB_URL/img/facebook.png' /></a></li>\
			</ul>\
		</div>\
	</li>",
	errorTemplate: "<li>{{error}}</li>",
	preComment: "popupContainer-",
	comments: [],
	activeCommentUUID: -1,

	/*
			<span class='metaDataSpacer'>|</span>
			accuracy: {{cmmnt_accuracy}}
			<span class='metaDataSpacer'>|</span>
			sentiment: {{cmmnt_sentiment}}
	*/

	initialize: function(uuid, popup) {
		this.uuid = uuid;
		this.popup = popup;
	},
	
	getAndLoad: function(slctn_uuid){
		this.get(slctn_uuid, true);
	},
	
	load: function(slctn_uuid){
		var self = this;
		_.each(this.comments, function( aComment ) {
			self.process(aComment, slctn_uuid, false);
	    });
	},
	
	process: function(aComment, slctn_uuid, fadeIn){
		// process comment and add it to the relevant comment list...
		var self = this;
		if (aComment.cmmnt_type == 'C'){
			noCommentsLi = $('#noComments-'+slctn_uuid);													// get the no comments message...
			if(noCommentsLi){																				// check if it exists...
				noCommentsLi.hide();																		// fade out if it exists...
			}
			// process the comment object
			args = {
				comment: aComment,
				sharing: self.sharing.getLinkTemplates(encodeURIComponent(self.popup.article.selectionUrl(slctn_uuid)), aComment.cmmnt_comment,'comment')
			};
			$('#commentsContainer-' + slctn_uuid).append($.mustache(this.template, args));					// add the comment
			self.activeCommentUUID = aComment.cmmnt_uuid;
			if(self.activeCommentUUID.toString() != "-1" && fadeIn){										// add effect if fadeIn is true
				$('#comment-'+self.activeCommentUUID).fadeIn();												// fade in comment
			} else{
				$('#comment-'+self.activeCommentUUID).show();												// just show the comment
			}
		}
	},
	
	// get the stored text selections
	get: function(slctn_uuid, load_comments) {
		var self = this;
		$.ajax({
			url: 'REPLACE_IFDB_URL/article/' + self.uuid + '/selection/' + slctn_uuid + '/comment',
			type: 'GET',
			dataType: 'JSON',
			async: true,															// synchronous call for now			
			success: function( data ) {
				self.comments = data.records;										// set comments
				if(load_comments){													// load comments if wished
					self.load(slctn_uuid);											
				}
				console.log( data );
			},
			error: function( jqXHR, textStatus, errorThrown ){
				console.log( 'Get stored comments call failed for article id: ' + self.uuid + " and selection " + slctn_uuid);
				console.log( jqXHR );
				console.log( textStatus );
				console.log( errorThrown );
			}
		});
	},
	
	save: function(slctn_uuid) {
		var self = this;
		formErrors = $("#formErrors-"+slctn_uuid);																// get error div
		formErrorsList = $("#formErrorsList-"+slctn_uuid);														// get error list ul
		// define the right spinner
		if($('#commentAdded-'+slctn_uuid).val() == "1"){
			spinnerElement = $("#submitCommentSpinner-"+slctn_uuid);
		} else{
			spinnerElement = $("#submitAccuracyCommentSpinner-"+slctn_uuid);
		}
		$.ajax({
			url: 'REPLACE_IFDB_URL/article/' + self.uuid + '/selection/' + slctn_uuid + '/comment/create',
			dataType: 'JSONP',
			data: $("#commentForm-"+slctn_uuid).serialize(),
			beforeSend: function(jqXHR, textStatus){
				formErrorsList.html("");																// wipe old errors
				formErrors.fadeOut();																	// make sure errors are hidden
				spinnerElement.show();																	// show spinner
			},
			success: function( data ) {
				if(typeof(data.errors) == "undefined"){													// check for errors
					aComment = data.record;																// get comment
					self.comments.push(aComment);														// add comment to array
					self.process(aComment, slctn_uuid, true);											// add comment to list in the popup
					$("#commentForm-"+slctn_uuid).reset();												// reset the form
					// scroll to comment?
				} else{																					// add error handling
					_.each(data.errors, function( aError ) {											// loop through errors
						formErrorsList.append($.mustache(self.errorTemplate, { error: aError }))		// add error
				    });		
					formErrors.fadeIn();																// show errors
				}
				console.log( data );
				spinnerElement.hide();											// hide spinner
			},
			error: function( jqXHR, textStatus, errorThrown ){
				console.log( 'Could not save comment for article id: ' + self.uuid + " and selection " + slctn_uuid);
				console.log( jqXHR );
				console.log( textStatus );
				console.log( errorThrown );
				$("#submitCommentSpinner-"+slctn_uuid).hide();
			}
		});
	}

}