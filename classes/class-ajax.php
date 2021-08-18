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

        add_action( 'wp_ajax_initialise_quiz', array($this, 'initialise_quiz' ));
        add_action( 'wp_ajax_nopriv_initialise_quiz', array($this, 'initialise_quiz' ));


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


		// Check a user can take a quiz
		add_action( 'wp_ajax_check_quiz_access', array($this, 'check_quiz_access' ));
		add_action( 'wp_ajax_check_quiz_access', array($this, 'check_quiz_access' ));


	}


	public function submitPage()
	{

		global $ek_quiz_database;

		// Check the AJAX nonce
		check_ajax_referer( 'quiz_page_ajax_nonce', 'security' );

        // Create blank page str
        $pageStr = '';



        //foreach ($_POST as $KEY => $VALUE)
        //{
        //    $pageStr.=$KEY.' = '.$VALUE.'<br/>';
       // }


        $quizID = $_POST['quizID'];
        $attemptID = $_POST['attemptID'];
        $userID = get_current_user_id();
        $pageToShow = $_POST['pageToShow'];



		// Get the existing user responses array from the quiz attempts table
		$attemptInfo = $ek_quiz_database->getAttemptInfo( $attemptID );


		$userResponsesArray = unserialize($attemptInfo['userResponses']);
		// No data saved yet so create array
		if(!is_array($userResponsesArray))
		{
			$userResponsesArray = array();
		}

        // If the user responses don't exist then
        $userResponses = '';
        if(isset($_POST['userResponses']) )
        {
            $userResponses = $_POST['userResponses'];
        }

        if(is_array($userResponses) )
        {
    		// Add the questionIDs as keys to the master user response array
    		foreach($userResponses as $questionID => $thisResponse)
    		{
    			$userResponsesArray[$questionID] = $thisResponse;
    		}
        }


		// Save the new array
		$pageStr.= $ek_quiz_database->savePageResponses($attemptID, $userResponsesArray);
		//return "called";


		// Create args array for showing the NEXT PAGE
		$args = array(
			"quizID"			=> $quizID,
			"currentPage"		=> $pageToShow,
			"userResponses"		=> $userResponses,
            "attemptID"         => $attemptID,
		);



		$pageStr.= ekQuizDraw::drawQuizPage($args);

		// Check if its the last page - if so then check the quiz settings for jumping to a new page
		$completionRedirectURL = "";
		$completionMessage="";
		if($pageToShow=="answers")
		{

            $siteURL = get_site_url();

			$completionRedirectURL = get_post_meta($quizID, "completionRedirectURL", true);

            // Also get the  emails to email if they have been checked upon completion
            $emailOnCompletionList = get_post_meta($quizID, "emailOnCompletionList", true);
            $emailArray = explode(',', $emailOnCompletionList);
            $quizName = get_the_title($quizID);


            $subject = 'New participant has taken the quiz : '.$quizName;
            $body = 'A new person has taken the quiz "'.$quizName.'". <a href="'.$siteURL.'/wp-admin/options.php?page=ek-quiz-results&quizID='.$quizID.'">Click here to view the partipant list</a>';
            $headers = array('Content-Type: text/html; charset=UTF-8');

            foreach ($emailArray as $thisEmail)
            {
                $email = trim($thisEmail);

                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    wp_mail( $email, $subject, $body, $headers );


                }
            }

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
        $attemptID = $_POST['attemptID'];


        // Now draw the first page of the quiz
        $args = array(
            "quizID"			=> $quizID,
            "currentPage"		=> 1,
            "attemptID"         => $attemptID,
        );

        // Returns array of quiz and if answer is correct so get the qStr KEY
        echo ekQuizDraw::drawQuizPage($args);

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
        $correctFeedback = isset($_POST['correctFeedback']) ? $_POST['correctFeedback'] : '';
        $incorrectFeedback = isset($_POST['incorrectFeedback']) ? $_POST['incorrectFeedback'] : '';
		$optionOrder = $_POST['optionOrder'];
		$qType = $_POST['qType'];
		$showAnswer = $_POST['showAnswer'];


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

    public static function initialise_quiz()
    {


        check_ajax_referer( 'quiz_page_ajax_nonce', 'security' );


        global $ek_quiz_database;
        global $ekQuizzes_CPT;

        // Get the quiz ID from the JS
        $quizID = $_POST['quizID'];

        // Generate the question page array
        $myQuestionArray = $ekQuizzes_CPT->generateQuizQuestionArray($quizID);

        $userID = get_current_user_id();
        // Get all previous attempts and up the attempt number_format

        $previousAttempts = $ek_quiz_database->getAllQuizAttemptsByUser( $quizID, $userID );
        $attemptCount = count($previousAttempts);


        $thisAttemptNumber = $attemptCount+1;

        $args = array(
            "quizID"			 => $quizID,
            "quizQuestionsArray" => $myQuestionArray,
            "thisAttemptNumber"	=> $thisAttemptNumber,
        );

        // Save this latest attempt and add the attemptID as a session var
        $attemptID = $ek_quiz_database->saveAttemptInfo($args);

        echo $attemptID;

        die();


    }

	public static function check_quiz_access()
	{

		check_ajax_referer( 'quiz_page_ajax_nonce', 'security' );

		$quiz_id = $_POST['quizID'];


		// Get the availability
		$quiz_object = new ekQuizzes_CPT();
		$check_access = $quiz_object->checkQuizAccess($quiz_id);

		$access = $check_access[0];

		// If they can access, then let them see the
		if($access==1)
		{
			echo '1';
			die();
		}

		// Are they admin?
		if(current_user_can('delete_pages') )
		{
			echo '1';
			die();
		}

		// Must be not allowed
		echo '0';
		die();


	}

} // End Class
?>
