<?php

require_once "IFDB_Exception.php";
require_once "IFDB_Article.php";
require_once "IFDB_Selection.php";
require_once "IFDB_FormbuilderQuery.php";

/**
 *
 *
 * @author ktaborski
 * @package default
 */
class IFDB_Comment {


    /**
     *
     *
     * @var Article
     */
    public $article = null;


    /**
     *
     *
     * @var Selection
     */
    public $selection = null;


    /**
     *
     *
     * @param string  $article_uuid
     * @param string  $selection_uuid
     */
    public function __construct($article_uuid, $selection_uuid) {
        $this->article = IFDB_Article::find_by_uuid($article_uuid);
        $this->selection = IFDB_Selection::find_by_uuid($selection_uuid);

        if ($this->article->artcl_id != $this->selection->Article->artcl_id) {
            throw new IFDB_Exception("Article is not Selection's parent");
        }
    }


    /**
     * This uses IFBD and not AIR!!!!!!
     *
     * Return comments (source responses) for the article and selection.
     * Filters out all private responses. For journalist only.
     * Filters out all hidden reponses. For curration.
     *
     * @return Array of comments
     */
    public function getAllFiltered() {
        $comments = IFDB_Query::create()
        ->from("Comment")
        ->where("cmmnt_slctn_id = ?", $this->selection->slctn_id)
        ->andWhere("cmmnt_status = ?", Comment::$ACTIVE)
        ->andWhere("cmmnt_private != ?", Comment::$PRIVATE);

        return $comments->execute()->toArray();
    }


    /**
     *
     *
     * @param unknown_type $form_data
     * @return Comment
     */
    public function createArticleSelectionComment($form_data) {
        $comment = $this->storeComment($form_data);

        // convert the form into FormBuilder data
/*        $formbuilder = new IFDB_FormbuilderQuery($comment);
        $formbuilder->submit();

        if ($formbuilder->status() != '302') {
//echo var_dump($formbuilder->response)."<br>";
//echo var_dump($formbuilder->response_info)."<br>";
            // set the migration status as failed
            $comment->cmmnt_fb_export_status = Comment::$MIGRATION_ERROR;
            $comment->save();

            if ($formbuilder->status() == '200') {
                throw new IFDB_Exception("Query submission or data validation error occurred while submitted comment to Formbuilder");
            } else {
                throw new IFDB_Exception("Unknown error occurred while submitted comment to Formbuilder");
            }
        } else {
            // set the migration status as succeeded
            $comment->cmmnt_fb_export_status = Comment::$MIGRATED;
            $comment->save();
        }
*/

        // need to get comment from AIR2
        return $comment->toArray();
    }


    /**
     * Pass in a result from a selection with it's type and store it.
     *
     * @param array   $form_data
     * @return Comment
     */
    private function storeComment($form_data) {
        $comment = new Comment();
        $comment->cmmnt_user_id = $form_data["user_id"];
        $comment->cmmnt_full_name = $form_data["full_name"];
        $comment->cmmnt_comment = $form_data["comment"];
        $comment->cmmnt_private = (isset($form_data["private"]) && $form_data["private"] == 1) ? Comment::$PRIVATE : '';
        $comment->cmmnt_accuracy = $form_data["accuracy"];
        $comment->cmmnt_sentiment = $form_data["sentiment"];
        $comment->cmmnt_status = Comment::$ACTIVE;
        $comment->cmmnt_type = ($form_data["comment_added"] == "1") ? Comment::$COMMENT : Comment::$SLIDER;
        $comment->cmmnt_submission = json_encode($form_data);
        $comment->Selection = $this->selection;
        $comment->save();

        return $comment;
    }


}
