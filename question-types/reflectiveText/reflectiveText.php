<?php
$ek_reflectiveText = new ek_reflectiveText;
class ek_reflectiveText
{
	static $qType = "reflectiveText";
	
	static function questionMeta()
	{
		$qMeta = array(
			"qString" => 'Reflective Text',
			"qOptions" => true, // test
			"qIcon"		=> 'fa-comment',
			"qCat"		=> "Text Based",
			"qCatOrder"	=> 3,

		);
		
		
		
		return $qMeta;
	}	//~~~~~
	function __construct ()
	{
		
		$this->addWPActions();
	}
	
/*	---------------------------
	PRIMARY HOOKS INTO WP 
	--------------------------- */	
	function addWPActions ()
	{
		//Add Front End Jquery and CSS
		//add_action( 'wp_footer', array( $this, 'frontendEnqueues' ) );
		
	}
	
	function frontendEnqueues ()
	{
		//Scripts
		wp_enqueue_script('jquery');
		
		// Register Ajax script for front end
		wp_enqueue_script('ek_quiz_reflectiveText_ajax', EK_QUIZ_PLUGIN_URL.'/question-types/reflectiveText/ajax.js', array( 'jquery' ) ); #Custom AJAX functions
			
		
		//Localise the JS file
		$params = array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'ajax_nonce' => wp_create_nonce('reflectiveText_ajax_nonce'),
		);
		wp_localize_script( 'ek_quiz_reflectiveText_ajax', 'reflectiveTextAjax_params', $params );	
		
	}
	
	
	
	
	static function drawQuestion($args)
	{
				
		// Get Current user ID
		$currentUserID = get_current_user_id();
		
		
		// Get Defaults
		$defaults = ekQuiz::$defaults;
		
		foreach($args as $key => $value){$$key = $value;} # Turn all atts into variables of Key name
		
		if($buttonText=="")
		{
			$buttonText=$defaults['buttonText'];
		}
		
		// Add a text area or not
		$addTextarea = get_post_meta($questionID, "addTextarea", true);
		
		//if($correctFeedback==""){$correctFeedback = get_post_meta($questionID, "correctFeedback", true);}

		$randomKey = $args['randomKey'];		
		
		//$qStr='<div id="ek-question-'.$questionID.'-'.$randomKey.'">';
		$qStr='<div>';
		
		$qStr.= apply_filters('the_content', get_post_field('post_content', $questionID));
		
		// Auto Save the response if its a text box
		$saveResponse=false;
		if($addTextarea=="on")
		{
			$saveResponse=true;
		}
		
		
		// Add the Vars to the Args to pass via JSON to ajax function
		$args['randomKey'] = $randomKey;
		$args['userID'] = $currentUserID;
		$args['saveResponse'] = $saveResponse;
		$args['qType'] = self::$qType;
					
		// Create array to pass to JS
		$passData = htmlspecialchars(json_encode($args));	
		
		if($addTextarea=="on")
		{
		
			$userResponseArray = ekQuiz_queries::getUserResponse($questionID, $currentUserID);
			$userResponse = stripslashes($userResponseArray['userResponse']);
			//$userResponse = apply_filters('the_content', $userResponseArray['userResponse']);

		
		
			$editor_settings = array
			(
				"media_buttons"	=> false, 
				"textarea_rows"	=> 6,
				"editor_class"	=> "ek-reflection-editor",
				"tinymce"		=> array(
				'toolbar1'	=> 'bold,italic,underline,bullist,numlist,forecolor,undo,redo',
				'toolbar2'	=> ''
				)
			);					
			
			ob_start();
			wp_editor($userResponse, 'reflection_'.$questionID.'-'.$randomKey, $editor_settings);		
			$qStr.= ob_get_contents();
			ob_end_clean();
		}
			
					
		// Create blank div for feedback fro this qType. unique to reflection as we don't want to recreate textbox
		$qStr.='<div id="reflectionSavedFeedback_'.$questionID.'-'.$randomKey.'" class="reflectionFeedback">Entry Saved</div>';
		$qStr.='<div id="reflectionFeedback_'.$questionID.'-'.$randomKey.'" class="reflectionSavedFeebdack">Entry Saved</div>';		
		
		$qStr.='<div class="ekQuizButtonWrap" id="ekQuizButtonWrap_'.$questionID.'_'.$randomKey.'">';
		$qStr.='<input type="button" value="'.$buttonText.'" class="ekQuizButton" onclick="javascript:singleQuestionSubmit(\''.$passData.'\')";/>';
		$qStr.='</div>';
		
		// Hide the Visual Tab
		$qStr.='		
			<style>
			.wp-editor-tools, .post .wp-editor-tools {
				display: none;
			}
			</style>
		';
			
		
		
		
		
		// Close the Question div wrap
		$qStr.='</div>';
		
		
		
		return $qStr;
	
		
		
		
	}
	
	
}
?>