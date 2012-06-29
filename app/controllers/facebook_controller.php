<?php

require_once "IFDB_Users.php";
require_once "IFDB_Article.php";
require_once "facebook/src/facebook.php";

/**
*
*
* @author esundelof
* @package default
*/
class Facebook_Controller extends IFDB_Controller {

	public $is_secure = false;
	
	public function connect() {
		$facebook = new Facebook(array(
			'appId'  => FB_APP_ID,
			'secret' => FB_SECRET
		));
		
		// make sure we can redirect back to the respective site
		$article = null;
		$form_data = $this->get_post();
		
		try{
			if( isset( $form_data["article_uuid"]) && !isset($form_data["json"]) ) {
				$article_uuid = $form_data["article_uuid"];
				$article = IFDB_Article::find_by_uuid($article_uuid);
				$article_url = str_replace("#_=_", "", $article->artcl_url);
			}
		} catch( IFDB_Exception $e ) {
		}
		
		// add logout functionality
		try{
			if(isset($form_data["logout"])){
				$facebook->logoutUser();
				// redirect if we have an article set and this is not a json call...
				if(isset($article)){
					header("Location:" . $article_url);
					exit();
				}
			}
		} catch( IFDB_Exception $e ) {
		}
						
		// Get the Facebook user ID
		$user = $facebook->getUser();

		if ($user) {
			try {
				// Proceed knowing you have a logged in user who's authenticated...
				$user_profile = $facebook->api('/me');
				$fb_user_id = $user_profile["id"];
				$user = IFDB_Users::find_by_fb_user_id($fb_user_id);
				
				// only create if the user does not exist
				if(!isset($user->user_id)){
					try {
			            $user = IFDB_Users::createUser($fb_user_id);
			
						// redirect if we have an article set and this is not a json call...
						if(isset($article)){
							header("Location:" . $article_url);
							exit();
						}
			     		
						// send JSON response 
						$this->response(array(
							"message" => "User created.",
							"logout_url" => str_replace("json", "logout", $facebook->getLogoutUrl()),
                			"callback" => $this->input->get_post("callback"),
							"user_id" => $user->user_id, 
							"full_name" => $user_profile["name"],
							"fb_user_id" => $fb_user_id));
			   		} catch( IFDB_Exception $e ) {
			            $this->response(
							array( "message" => "Could not create the user: ".$e->getMessage().".")
			            );
			        }
					
				} else {
					
					// redirect if we have an article set and this is not a json call...
					if(isset($article)){
						header("Location:" . $article_url);
						exit();
					}
					
					// send JSON response
					$this->response(array(
							"message" => "Found user.",
							"logout_url" => str_replace("json", "logout", $facebook->getLogoutUrl()),
                			"callback" => $this->input->get_post("callback"),
							"user_id" => $user->user_id, 
							"full_name" => $user_profile["name"],
							"fb_user_id" => $fb_user_id));
				}
			} catch (FacebookApiException $e) {
				error_log($e);
				$user = null;
				$this->response(array(
					"message" => "Could not create the user.",
                	"callback" => $this->input->get_post("callback"))
				);
			}
		}else{
			// send back the login url to allow the user to go through the browser to authenticate...
			$loginUrl = str_replace("json", "browser", $facebook->getLoginUrl());
			
			// redirect if we have an article set and this is not a json call...
			if(isset($article)){
				header("Location:" . $article_url);
				exit();
			}
			
			// send back the login url
			$this->response(array(
					"message" => "User not found.",
                	"callback" => $this->input->get_post("callback"),
					"login_url" => $loginUrl
			));
		}
	}
}