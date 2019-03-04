<?php
$ek_quizAjax = new ek_quizAjax();
class ek_quizAjax
{
	
	//~~~~~
	public function __construct ()
	{
		$this->addWPActions();
	}	
	
	
	function addWPActions()
	{	
	
		// Front End
		add_action( 'wp_ajax_startQuiz', array($this, 'startQuiz' ));
		add_action( 'wp_ajax_nopriv_startQuiz', array($this, 'startQuiz' ));
		
		add_action( 'wp_ajax_getTimer', array($this, 'getTimer' ));	
		add_action( 'wp_ajax_nopriv_getTimer', array($this, 'getTimer' ));		
		
		add_action( 'wp_ajax_quizPageSubmit', array($this, 'submitPage' ));	
		add_action( 'wp_ajax_nopriv_quizPageSubmit', array($this, 'submitPage' ));		
		
		add_action( 'wp_ajax_submitResponse', array($this, 'submitAnswer' ));
		add_action( 'wp_ajax_nopriv_submitResponse', array($this, 'submitAnswer' ));

		
		// Redraw the question
		add_action( 'wp_ajax_redrawQuestion', array($this, 'redrawQuestion' ));
		add_action( 'wp_ajax_nopriv_redrawQuestion', array($this, 'redrawQuestion' ));
		
		
		
		
		
		// Backend
		add_action( 'wp_ajax_drawNewResponseOption', array($this, 'drawNewResponseOption' ));
		add_action( 'wp_ajax_duplicateQuestion', array($this, 'duplicateQuestion' ));
		
		
		
		
		
	}
	public function submitPage()
	{
		
		global $ek_quiz_database;
		
		// Check the AJAX nonce				
		check_ajax_referer( 'quiz_page_ajax_nonce', 'security' );
		
		
		$quizID = $_POST['quizID'];
		$userID = get_current_user_id();
		$pageToShow = $_POST['pageToShow'];
		$userResponses = $_POST['userResponses'];
		
		$attemptID = $_SESSION['attemptID'];
		// Get the existing user responses array from the quiz attempts table
		$attemptInfo = $ek_quiz_database->getAttemptInfo( $attemptID );
		
		
		$userResponsesArray = unserialize($attemptInfo['userResponses']);
		// No data saved yet so create array
		if(!is_array($userResponsesArray))
		{
			$userResponsesArray = array();
		}
		
		// Add the questionIDs as keys to the master user response array
		foreach($userResponses as $questionID => $thisResponse)
		{
			$userResponsesArray[$questionID] = $thisResponse;	
		}
		
		
		// Save the new array
		$ek_quiz_database->savePageResponses($attemptID, $userResponsesArray);
		//return "called";

		
		// Create args array for showing the NEXT PAGE
		$args = array(
			"quizID"			=> $quizID,
			"currentPage"		=> $pageToShow,
			"userResponses"		=> $userResponses,
			"redirectURL"		=> $completionRedirectURL,
		);
		
		
		
		$pageStr= ekQuizDraw::drawQuizPage($args);
		
		// Check if its the last page - if so then check the quiz settings for jumping to a new page		
		$completionRedirectURL = "";
		$completionMessage="";
		if($pageToShow=="answers")
		{
				
			$completionRedirectURL = get_post_meta($quizID, "completionRedirectURL", true);
			
		}		
		

		// Encode to a JSON array so we can do a redirect if needed
		$returnDataArray = array();
		$returnDataArray['pageStr'] = $pageStr;	
		$returnDataArray['completionRedirectURL'] = $completionRedirectURL;
		
		echo json_encode($returnDataArray);
			
		die();
	}
	
	public function startQuiz()
	{
		
		
		// Check the AJAX nonce				
		check_ajax_referer( 'quiz_page_ajax_nonce', 'security' );
		
		$quizID = $_POST['quizID'];
			
		echo ekQuizDraw::initaliseQuiz($quizID);
			
		die();
	}
	
	
	/* Submits a single answer (not in quiz) from question shortcode */
	public function submitAnswer()
	{
		// Check the AJAX nonce				
		check_ajax_referer( 'submitQuestion_ajax_nonce', 'security' );
		

		
		$userID = $_POST['userID']; 
		$questionID = $_POST['questionID'];
		$userResponse = $_POST['userResponse'];
		$saveResponse = $_POST['saveResponse'];
		$correctFeedback = $_POST['correctFeedback'];
		$incorrectFeedback = $_POST['incorrectFeedback'];
		$optionOrder = $_POST['optionOrder'];
		$qType = $_POST['qType'];		
		$showAnswer = $_POST['showAnswer'];	

		
		
	
		
	
		$saveResponse=true; // Set this to true- we always want to save it
		/* If the shortcode savedata is passed then save this data */
		if($saveResponse==true)
		{	
			
			$gotItCorrect='';
			if($qType<>"reflectiveText")
			{
				
				$args = array(
					"questionID" 	=> $questionID,
					"userResponse"	=> $userResponse,
				);
				
				$thisQ_class = 'ek_'.$qType;				
				$gotItCorrect = $thisQ_class::markQuestion($args);
			}
			
			
			$ek_quiz_actions = new ek_quiz_actions();			
			
			$ek_quiz_actions->saveUserResponse($questionID, $userResponse, $gotItCorrect);
		}
		
		// If its text based then convert for JSON
		if($qType=="singleResponse")
		{
			$userResponse = htmlspecialchars(stripslashes($userResponse) );
		}

		// If its multi response then convert each array value
		if($qType=="multiBlanks")
		{
			
			$tempResponseArray = array();
			
			foreach ($userResponse as $KEY => $VALUE)
			{
				$tempResponseArray[$KEY] = htmlspecialchars(stripslashes($VALUE) );
			}
			
			$userResponse = $tempResponseArray;
		}	
		
		$args = array(
		
			"questionID" 			=> $questionID,
			"readOnly"				=> true,
			"userResponse"			=> $userResponse,
			"correctFeedback"		=> $correctFeedback,
			"incorrectFeedback"		=> $incorrectFeedback,
			"optionOrder"			=> $optionOrder,
			"showAnswer"			=> $showAnswer,
	
		);
		

		
		if($qType=="reflectiveText")
		{
			// Get the Reflection feedback
			$correctFeedback = get_post_meta($questionID, "correctFeedback", true);
			echo apply_filters('the_content', $correctFeedback);
		}
		else
		{
			// Draw the question again with the correct answers etc
			$drawClass = 'ek_'.$qType;			
			$qString = $drawClass::drawQuestion($args);			
			echo $qString;
		}
			
		die();
	}	
	
	
	public function getTimer()
	{
		
		check_ajax_referer( 'quiz_page_ajax_nonce', 'security' );
		
		$quizID = $_POST['quizID'];
		$timeLimit = get_post_meta($quizID, 'timeLimit', true);
		$timeLimitMinutes = get_post_meta($quizID, 'timeLimitMinutes', true);
		
		
		
		/* Get the quiz timer in minutes */
		
		if($timeLimit=="on")
		{
			wp_send_json( $timeLimitMinutes) ;
		}
		
		die();
	}	
	
	
	public function drawNewResponseOption()
	{
		
		
		$optionInfo = array();
		$qType = $_POST['qType'];
		
		$text = htmlspecialchars(stripslashes($_POST['text']));
		$dataKey = $_POST['dataKey'];
		$isCorrect = $_POST['isCorrect'];		
		
		$optionInfo = array(
			"optionValue"	=> $text,
			"isCorrect"		=> $isCorrect,
			"dataKey"		=> $dataKey,
			"displayNumber"	=> "", // This will be renumbered anyway
		);
		
		
		echo ekQuestions_CPT::drawResponseOptionEditDiv($optionInfo, $qType);
		
		die();
		
	}
	
	public static function redrawQuestion()
	{
		

		
		$questionID = $_POST['questionID'];
		$randomKey = $_POST['randomKey'];
		$buttonText= $_POST['buttonText'];
		
		// Ger the qType
		$qType = get_post_meta($questionID, "qType", true);
		
		$thisClass = 'ek_'.$qType;
		
		
		$button = 'SUBMIT';
		
		$args = array(
		
			"questionID" 			=> $questionID,		
			"randomKey"				=> $randomKey,
			"showButtons"			=> true,
			"readOnly"				=> false,
			"buttonText"			=> $buttonText,	
			
		);
		

		

		
		// Bulletproof only execute if the qType is not blank
		if($qType)
		{
			$qStr.= $thisClass::drawQuestion($args);
		}	

		echo $qStr;

		die();		
		
		
		
	}
	
	public static function duplicateQuestion()
	{
		
		
		$targetQuestionID = $_POST['targetQuestionID'];
		$targetPotID = $_POST['targetPotID'];
	
		ekQuestions_CPT::ek_question_duplicate($targetQuestionID, $targetPotID);
		
		
		die();
	}
	
} // End Class
?>