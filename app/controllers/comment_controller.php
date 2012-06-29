<?php

require_once "IFDB_Comment.php";

/**
 *
 *
 * @author ktaborski
 * @package default
 */
class Comment_Controller extends IFDB_Controller {

    public $is_secure = false;

    private static $SPAM_FIELD = "q";

    private static $ACCURACY = "accuracy";

    private static $SENTIMENT = "sentiment";

    private static $COMMENT_FIELD_NAMES = array("user_id", "full_name", "comment");

    private static $SLIDER_FIELD_NAME = array("accuracy", "sentiment");

    /**
     * list all comments for an article's selection
     *
     * @param String  $article_uuid
     * @param String  $selection_uuid
     */
    public function GET_index($article_uuid, $selection_uuid) {
        $comment_service = new IFDB_Comment($article_uuid, $selection_uuid);
        $comments = $comment_service->getAllFiltered();
        $this->response(array("records" => $comments));
    }


    /**
     * create a comment for an article's selection
     *
     * @param String  $article_uuid
     * @param String  $selection_uuid
     */
    public function create($article_uuid, $selection_uuid) {
        // convert post data into an array to pass around and work with more easilly
        $form_as_array = $this->get_post();

        try {
            // IFDB comment validation
            $error_array = $this->validateForm($form_as_array);

            if (count($error_array) > 0) {
                $this->response(
                    array(
                        "message" => "There was a validation error with your submission.",
                        "errors" => $error_array,
                        "callback" => $this->input->get_post("callback")
                    )
                );
            }

            // create the comment
            $comment_service = new IFDB_Comment($article_uuid, $selection_uuid);
            $comment = $comment_service->createArticleSelectionComment($form_as_array);

            $this->response(
                array(
                    "message" => "Comment created.",
                    "record" => $comment,
                    "callback" => $this->input->get_post("callback")
                )
            );
        } catch (IFDB_Exception $e) {
            $this->response(
                array(
                    "message" => "Error creating selection: ".$e->getMessage().".",
                    "errors" => array($e->getMessage()),
                    "callback" => $this->input->get_post("callback")
                )
            );
        }
    }


    /**
     * Scan through the form data and return basic errors.
     *
     * @param String  $form_as_array
     * @return array or error messages or empty array if everything is good
     */
    private function validateForm($form_as_array) {
        $errors = array();

        // is the q field populated
        $this->spamCheck($form_as_array);

        // is this a slider or a comment
        if ($form_as_array["comment_added"] === "0") {
            // if this is a slider comment only those two fields
            $errors = $this->validateSlider($form_as_array);
        } elseif ($form_as_array["comment_added"] === "1") {
            // if this is a comment then all comments and optionaly slider values
            $errors = $this->validateComment($form_as_array);
        } else {
            throw new IFDB_Exception("This is not a recognized comment type.");
        }

        return $errors;
    }


    /**
     * Do the various spam checks
     * - see if the field q is populated, is so abort!!!!
     *
     * Throws an exception when spam is detected.
     *
     * @param String  $form_as_array
     */
    private function spamCheck($form_as_array) {
        if (isset($form_as_array[self::$SPAM_FIELD]) && $form_as_array[self::$SPAM_FIELD] !== '' || !isset($form_as_array[self::$SPAM_FIELD])) {
            throw new IFDB_Exception("There was an error processing your request. Please reload your page and try again");
        }
    }


    /**
     * Valdiate a slider submit.
     *
     * @param String  $form_as_array
     * @return array errors
     */
    private function validateSlider($form_as_array) {
        $errors = array();

        // make sure atleast one of the sliders is adjusted
        if (isset($form_as_array[self::$ACCURACY]) && $form_as_array[self::$ACCURACY] === '' && isset($form_as_array[self::$SENTIMENT]) && $form_as_array[self::$SENTIMENT] === '') {
            $errors[] = "Neither of the sliders were adjusted, please adjust them.";
        }

        // make sure the slider values are in the correct range
        $errors = array_merge($errors, $this->checkRange($form_as_array, self::$ACCURACY));
        $errors = array_merge($errors, $this->checkRange($form_as_array, self::$SENTIMENT));

        // make sure all the fields are part of the submission
        $errors = array_merge($errors, $this->fieldExist($form_as_array, self::$SLIDER_FIELD_NAME));

        // make sure all the comment fields are NOT part of the submission
        foreach (self::$COMMENT_FIELD_NAMES as $field) {
            if (isset($form_as_array[$field]) && $form_as_array[$field] !== '') {
                $errors[] = "Field ".$field." should NOT have been included.";
            }
        }

        return $errors;
    }


    /**
     * Validate a comment.
     *
     * @param String  $form_as_array
     * @return array errors
     */
    private function validateComment($form_as_array) {
        $errors = array();

        if (count($form_as_array) > 0 && $form_as_array != false) {
            foreach ($form_as_array as $name => $value) {
                $trimmed_val = trim($value);

				// check to see if there is a user id set
				if($name == 'userId' && $value == '-1'){
					$errors[] = "You have to be logged in to submit a comment.";
				}

                // if it is a comment make sure it is a certain number of words, ie higher value
                if ($name == "comment" && count(split(' ', preg_replace("/\s\s+/", ' ', $trimmed_val))) < 4) {
                    $errors[] = "Field comment should be more then 4 words.";
                }
            }
        } else {
            $errors[] = "No data was submitted.";
        }

        // make sure all the fields are part of the submission
        $errors = array_merge($errors, $this->fieldExist($form_as_array, self::$COMMENT_FIELD_NAMES));

        return $errors;
    }


    /**
     * Check to make sure the slider values are within acceptable values.
     *
     * @param array   $form_as_array
     * @param String  $index
     * @return string message
     */
    private function checkRange($form_as_array, $index) {
        if (isset($form_as_array[$index]) && !(intval($form_as_array[$index]) >= 0 && intval($form_as_array[$index]) <= 100)) {
            return array("Field ".$index." did not contain an acceptable value.");
        }

        return array();
    }


    /**
     * Make sure the form has the index values set.
     *
     * @param String  $form_as_array
     * @param array   $check_values
     * @return array errors
     */
    private function fieldExist($form_as_array, $check_values) {
        $errors = array();

        foreach ($check_values as $field) {
            if (!isset($form_as_array[$field])) {
                $errors[] = "Field ".$field." was not included in the submission.";
            }
        }

        return $errors;
    }


}
