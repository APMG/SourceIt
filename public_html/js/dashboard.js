
$(function(){


    var Newsroom = Backbone.Model.extend({
        url: "newsroom",
        idAttribute: "nwsrn_id"
    });


    var NewsroomCollection = Backbone.Collection.extend({
        model: Newsroom,
        url: "newsroom",

        parse: function(response) {
            return response.records;
        }
    });


    var Article = Backbone.Model.extend({
        url: "article",
        idAttribute: "artcl_id"
    });


    var ArticleCollection = Backbone.Collection.extend({
        model: Article,

        parse: function(response) {
            return response.records;
        }
    });


    var Selection = Backbone.Model.extend({
        url: "article/"+this.article_uuid+"/selection",
        idAttribute: "slctn_id"
    });


    var SelectionCollection = Backbone.Collection.extend({
        model: Selection,

        parse: function(response) {
            return response.records;
        }
    });


    var Comment = Backbone.Model.extend({
        url: "article/"+this.article_uuid+"/selection/"+this.selection_uuid+"/comment",
        idAttribute: "cmmnt_id"
    });


    var CommentCollection = Backbone.Collection.extend({
        model: Comment,

        parse: function(response) {
            return response.records;
        }
    });


    var NewsroomView = Backbone.View.extend({
        tagName: "option",
        className: "newsroom-options",

        render: function() {
            $(this.el).html(this.model.get("nwsrn_name"));
            $(this.el).val(this.model.get("nwsrn_uuid"))
            return this;
        }
    });


    var NewsroomCollectionView = Backbone.View.extend({

        el: "#newsroom-dropdown",

        initialize: function() {
            Newsrooms.bind("all", this.addAll, this);
            Newsrooms.fetch();
        },

        addAll: function() {
            $("#newsroom-dropdown").append("<option value=\"\">Newrooms</option>");
            Newsrooms.each(this.addOne);
        },

        addOne: function(newsroom) {
            var nrView = new NewsroomView({model: newsroom});
            $("#newsroom-dropdown").append(nrView.render().el);
        },

        events: {
            "change" : "changeSelected"
        },

        changeSelected: function(e) {
            // if the user selects the dropdown default value don't update the view to no newsroom, continue showing the previous
            if( this.$el.val() != "" ){
                $("#articles-container").empty();
                previousNewsroomUUID = this.$el.val()
                Articles.url = "newsroom/" + this.$el.val() + "/article";
                Articles.fetch();
            } else { // if the user selects "Newsroom" revert the dropdown to the previous value so it always shows the correct newsroom name
                this.$el.val(previousNewsroomUUID);
            }
        }
    });


    var ArticleView = Backbone.View.extend({
        tagName: "div",
        className: "article-item row",
        template: "<div class=\"row\"><div class=\"span12\"><a href=\"{{url}}\">{{title}}</a></div><div class=\"selection-count span2\">Selections:&nbsp;<span id=\"article-{{article_id}}-selection-count\">0</span></div></div>",

        initialize: function() {
        },

        render: function() {
            var args = {
                title: this.model.get("artcl_title"),
                url: this.model.get("artcl_url"),
                article_id: this.model.id
            };
            $(this.el).html($.mustache(this.template, args));
            return this;
        },

        events: {
            "click a" : "showSelections"
        },

        showSelections: function(e) {
            $(".selections-container").hide();
            $(".comments-container").hide();
            this.$el.children().show();
            return false;
        }
    });


    var ArticleCollectionView = Backbone.View.extend({
        initialize: function() {
            Articles.bind("all", this.addAll, this);
        },

        addAll: function() {
            $("#loaingAjaxSpinner").show();
            Articles.each(this.addOne);
            total += Articles.length;
        },

        addOne: function(article) {
            var articleView = new ArticleView({model: article});
            $("#articles-container").append(articleView.render().el);
            articleView.$el.append("<div id=\"selections-container-article-"+article.id+"\" class=\"selections-container\"></div>");

            $("#selections-container-article-"+article.id).hide();

            var Selections = new SelectionCollection;
            var selectionView = new SelectionCollectionView({collection: Selections});
            Selections.url = "article/" + article.get("artcl_uuid") + "/selection";
            Selections.fetch();
        }
    });


    var SelectionView = Backbone.View.extend({
        tagName: "div",
        className: "selection-item row",
        template: "<div class=\"row\"><div class=\"selection-name span7 offset1\">{{selection}}</div><div class=\"selection-type span4\">{{type}}</div><div class=\"comment-count span2\">Comments:&nbsp;<span id=\"selection-{{selection_id}}-comment-count\">0</span></div></div>",

        initialize: function() {
        },

        render: function() {
            var selection_string = this.model.get("slctn_value");
            if( this.model.get("slctn_type") == "Q" ) {
                selection_string = "\"" + selection_string + "\"";
            }

            var selection_type = '';
            if( this.model.get("slctn_type") == "Q" ) {
                selection_type = "Quotation";
            } else if( this.model.get("slctn_type") == "S" ) {
                selection_type = "Money or Percentage";
            } else if( this.model.get("slctn_type") == "U" ) {
                selection_type = "User Selection";
            }

            var args = {
                selection: selection_string,
                type: selection_type,
                comment_count: '',
                selection_id: this.model.id
            };
            $(this.el).html($.mustache(this.template, args));
            return this;
        },

        events: {
            "click" : "showComments"
        },

        showComments: function(e) {
            $(".comments-container").hide();
            this.$el.children().show();
        }
    });


    var SelectionCollectionView = Backbone.View.extend({
        initialize: function() {
            this.collection.bind("all", this.addAll, this);
            this.collection.bind("reset", this.hideSpinner, this);
        },

        addAll: function() {
            $("#loaingAjaxSpinner").show();
            this.collection.each(this.addOne);
            if( this.collection.length > 0 ) {
                $("#article-" + this.collection.at(0).get("slctn_artcl_id") + "-selection-count").html(this.collection.length);
            }
            total += this.collection.length;
        },

        addOne: function(selection) {
            var selectionView = new SelectionView({model: selection});
            $("#selections-container-article-"+selection.get("slctn_artcl_id")).append(selectionView.render().el);
            selectionView.$el.append("<div id=\"comments-container-selection-"+selection.id+"\" class=\"comments-container\"></div>");

            $("#comments-container-selection-"+selection.id).hide();

            var Comments = new CommentCollection;
            var commentView = new CommentCollectionView({collection: Comments});
            Comments.url = "article/" + Articles.get(selection.get("slctn_artcl_id")).get("artcl_uuid") + "/selection/" + selection.get("slctn_uuid") + "/comment";
            Comments.fetch();
        },

        hideSpinner: function() {
            iterate++;
            if( total <= iterate ) {
                $("#loaingAjaxSpinner").fadeOut(1000);
            }
        }
    });


    var CommentView = Backbone.View.extend({
        tagName: "div",
        className: "comment-item row",
        template: "<div class=\"row\"><div class=\"comment-title span6 offset2\">{{comment}}</div><div class=\"comment-name span2\">{{name}}</div><div class=\"comment-date span4\">{{date}}</div></div>",

        initialize: function() {
        },

        render: function() {
            var comment = this.model.get("cmmnt_comment");

            if( this.model.get("cmmnt_type") == "S" ) {
                if( this.model.get("cmmnt_accuracy") != -1 ) {
                    comment = "Accuracy: " + this.model.get("cmmnt_accuracy");
                }
                if( this.model.get("cmmnt_sentiment") != -1 ) {
                    if( comment.length != 0 ) {
                        comment += " & ";
                    }
                    comment += "Sentiment: " + this.model.get("cmmnt_sentiment");
                }
            }

            var args = {
                comment: comment,
                name: this.model.get("cmmnt_first_name") + "&nbsp;" + this.model.get("cmmnt_last_name"),
                date: this.model.get("cmmnt_cre_dtim")
            };
            $(this.el).html($.mustache(this.template, args));
            return this;
        }
    });


    var CommentCollectionView = Backbone.View.extend({
        initialize: function() {
            this.collection.bind("all", this.addAll, this);
            this.collection.bind("reset", this.hideSpinner, this);
        },

        addAll: function() {
            $("#loaingAjaxSpinner").show();
            this.collection.each(this.addOne);
            if( this.collection.length > 0 ) {
                $("#selection-" + this.collection.at(0).get("cmmnt_slctn_id") + "-comment-count").html(this.collection.length);
            }
        },

        addOne: function(comment) {
            var commentView = new CommentView({model: comment});
            $("#comments-container-selection-"+comment.get("cmmnt_slctn_id")).append(commentView.render().el);
        },

        hideSpinner: function() {
            iterate++;
            if( total <= iterate ) {
                $("#loaingAjaxSpinner").fadeOut(1000);
            }
        }
    });


    var Newsrooms = new NewsroomCollection;
    var Articles = new ArticleCollection;
    var app = new NewsroomCollectionView;
    var articleView = new ArticleCollectionView;
    var previousNewsroomUUID = "";

    // define a loading div and let a user click on it to close it in case it spins forever
    $('body').append('<div id="loaingAjaxSpinner" class="loadingSpinner" style="display:none;"><img src="img/ajax-loader.gif" />&nbsp;&nbsp;Loading the articles, selections and comments, this may take a while... </div>');
    $('.loadingSpinner').click(function(){
        $(this).hide();
    });
    var left = $(window).width()/2-200;
	$("#loaingAjaxSpinner").css('left',left);
    var total = 0;
    var iterate = 0;


});
