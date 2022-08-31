<?php
class ekQuizDraw
{


	static function drawUserResults($quizID, $args = [])
	{

		$students_only = isset($args['students_only']) ? $args['students_only'] : false;

		$CSV = isset($args['CSV']) ? $args['CSV'] : false;

		$html='';
		$csvArray = array();
		//dataTables js
		//qtl_utils::loadDataTables();

		$ekQuiz_queries = new ekQuiz_queries();

		global $wp_roles;

		// Get an array of results with username as key
		// Get the results
		$quizResults = $ekQuiz_queries->getQuizResults($quizID);

		$quizAttemptArray = array();
		foreach($quizResults as $attemptInfo)
		{
			$score = 0;
			$userID = $attemptInfo['userID'];
			$attemptNumber = $attemptInfo['attemptNumber'];

			if(isset($attemptInfo['score'] ))
			{
				$score = $attemptInfo['score'];
			}

			$quizAttemptArray[$userID][] = array(
			"attemptNumber"=>$attemptNumber,
			"score" => $score
			);

		}

		$html.= '<table id="userTable">';
		$html.= '<thead><tr><th>Name</th><th>Username</th><th>CID</th><th>Role</th><th>Highest Score</th><th>Number of attempts</th><th></th></tr></thead>';
		$csvArray[] = array("Last Name", "First Name", "Username", "CID", "Role", "Highest Score", "Attempts");



		$blogusers = get_users();

		// Array of WP_User objects.
		foreach ( $blogusers as $userInfo )
		{
			$userID = $userInfo->ID;
			$fullname = esc_html( $userInfo->display_name );
			$firstName= esc_html( $userInfo->first_name );
			$surname= esc_html( $userInfo->last_name );
			$username = $userInfo->user_login;
			$cid = $userInfo->cid;
			$role_id = isset($userInfo->roles[0]) ? $userInfo->roles[0] : '';

			$userlevel =  translate_user_role( $wp_roles->roles[ $role_id ]['name'] );
			if($students_only && (strtolower($userlevel)<>"student" && strtolower($userlevel)<>"subscriber") )
			{
				continue;
			}

			// Get the attempt info from the lookup table
			$userAttemptArray = array();
			$maxScore = 0;
			$attemptNumber = "0";
			if(isset ($quizAttemptArray[$userID] ) )
			{
				$userAttemptArray = $quizAttemptArray[$userID];
				foreach($userAttemptArray as $k=>$v)
				{
					if($v['score']>$maxScore)
					{
					   $maxScore = $v['score'];
					}
				}
			}

			$attemptCount = count($userAttemptArray);
			if($attemptCount==0){$maxScore = '-';}

			if(!$maxScore){$maxScore = "-";}
			$html.= '<tr>';
			$html.= '<td>'.$surname.', '.$firstName.'</td>';
			$html.= '<td>'.$username.'</td>';
			$html.= '<td>'.$cid.'</td>';
			$html.= '<td>'.$userlevel.'</td>';
			$html.= '<td>'.$maxScore.'</td>';
			$html.= '<td>'.$attemptCount.'</td>';
			$html.= '<td>';
			if($attemptCount>0)
			{
				$html.='<a href="?page=ek-user-attempts&quizID='.$quizID.'&userID='.$userID.'">Results</a>';
			}
			else
			{
				$html.='-';
			}
			$html.='</td>';
			$html.= '</tr>';
			$csvArray[] = array ($surname,$firstName, $username, $cid, $userlevel,$maxScore,$attemptCount);



		}

		$html.= '</table>';




		$html.='
		<script>
			jQuery(document).ready(function(){
				if (jQuery(\'#userTable\').length>0)
				{
					jQuery(\'#userTable\').dataTable({
						"bAutoWidth": true,
						"bJQueryUI": true,
						"sPaginationType": "full_numbers",
						"iDisplayLength": 50, // How many numbers by default per page
						"order": [[0, "asc"]]
					});
				}

			});
		</script>	';

		if($CSV==true)
		{
			return $csvArray;
		}
		else
		{
			return $html;
		}

	}



	static function drawLegacyShortcodeQuestion($atts)
	{
		$qStr='';

		$atts = shortcode_atts(
			array(
				'id'		=> '',
				'savedata'   => '',
				'button'   => '',
				'correctfeedback'   => '',
				'incorrectfeedback'   => '',
				),
			$atts
		);

		ekQuiz::registerMyFrontScripts();



		$questionID = (int) $atts['id'];
		$saveResponse = esc_attr($atts['savedata']);

		$button = esc_attr($atts['button']);
		$correctFeedback = esc_attr($atts['correctfeedback']);
		$incorrectFeedback = esc_attr($atts['incorrectfeedback']);



		$newID = ekQuiz_queries::getNewQuestionID_fromLegacy($questionID);

		$questionID = $newID;
		$saveResponse = esc_attr($atts['savedata']);

		$button = esc_attr($atts['button']);
		$correctFeedback = esc_attr($atts['correctfeedback']);
		$incorrectFeedback = esc_attr($atts['incorrectfeedback']);

		// Because its a shortcode it won't be read only
		$readOnly=false;

		$qType = get_post_meta($questionID, 'qType', true);
		//echo $qType;



		return $qStr;









	}
	static function drawShortcodeQuestion($atts)
	{
		$qStr = '';

		$atts = shortcode_atts(
			array(
				'id'		=> '',
				'savedata'   => '',
				'button'   => '',
				),
			$atts
		);


		// Load the required scripts
		ekQuiz::registerMyFrontScripts();

		$questionID = (int) $atts['id'];
		$saveResponse = esc_attr($atts['savedata']);

		$button = esc_attr($atts['button']);




		/*
		$correctFeedback = esc_attr($atts['correctfeedback']);
		$incorrectFeedback = esc_attr($atts['incorrectfeedback']);
		$defaultFeedback  = ekQuiz_utils::getDefaultQuestionFeedback();


		// If custom feedback is blank fall back to whats defined in the question

		if($correctFeedback==""){$correctFeedback = get_post_meta($questionID, "correctFeedback", true);}
		if($incorrectFeedback==""){$incorrectFeedback = get_post_meta($questionID, "incorrectFeedback", true);}

		// If we still have no feedback fall back to the global defaults
		if($correctFeedback==""){$correctFeedback = $defaultFeedback['correctFeedback'];}
		if($incorrectFeedback==""){$incorrectFeedback = $defaultFeedback['incorrectFeedback'];}



		// Replace /r/n with <br>
		//$correctFeedback = preg_replace("!\r?\n!", "<br/>", $correctFeedback);
		//$incorrectFeedback = preg_replace("!\r?\n!", "<br/>", $incorrectFeedback);
		/* TO DO Pass correct feedback via json */



		// Because its a shortcode it won't be read only
		$readOnly=false;

		$qType = get_post_meta($questionID, 'qType', true);

		$randomKey = ekQuiz_utils::randomKey(10); // Generate a random key for each question in case of multiple same IDs on the page

		$args = array(

			"questionID" 			=> $questionID,
			"readOnly"				=> false,
			"buttonText"			=> $button,
			//"correctFeedback"		=> htmlentities($correctFeedback),
			//"incorrectFeedback"		=> $incorrectFeedback,
			"saveResponse"			=> $saveResponse,
			"showButtons"			=> true,
			"randomKey"				=> $randomKey,
			"showAnswer"			=> true,
		);



		$thisClass = 'ek_'.$qType;


		if($qType)
		{


			$qStr.='<div class="ek-question" >';
			$qStr.='<div id="ek-question-'.$questionID.'-'.$randomKey.'">';

			$qStr.= $thisClass::drawQuestion($args);

			$qStr.='</div>';

			// Add Hidden inputfield for shortcode params in case of redraw
			$qStr.='<input type="hidden" value="'.$button.'" id="buttonText-'.$questionID.'-'.$randomKey.'">';

			// Add an edit question link if they have the appopriate permissions
			if(current_user_can(get_option('min_quiz_access_level', 'manage_options')))
			{
				// Get the Parent ID (pot)
				$potID = wp_get_post_parent_id( $questionID );
				$qStr.='<div class="ek-question-front-options">';

				// Edit Question
				$qStr.= '<a href="'.get_home_url().'/wp-admin/post.php?post='.$questionID.'&action=edit" target="blank">Edit this Question</a>';

				// View Results
				$qStr.=' | <a href="'.get_home_url().'/wp-admin/options.php?page=ek-question-results&questionID='.$questionID.'&potID='.$potID.'">View Results</a>';
				$qStr.='</div>';



			}




			$qStr.='</div>';
		}

		return $qStr;

	}


	static function drawShortcodeQuiz($atts)
	{

		global $ekQuizzes_CPT;
		$qStr = '';

		$atts = shortcode_atts(
			array(
				'id'		=> '',
				//'savedata'   => '',
				//'button'   => '',
				//'correctfeedback'   => '',
				//'incorrectfeedback'   => '',
				),
			$atts
		);

		// Load the required scripts
		ekQuiz::registerMyFrontScripts();


		$quizID = (int) $atts['id'];

		$quizName = get_the_title($quizID);
		$quizMeta = get_post_meta($quizID);


		// Set some defaults
		$timeLimit = '';
		$quizInstructions = '';
		$loginRequried = '';
		$availableFromDate  = '';

		if(isset($quizMeta['timeLimit'][0] ) )
		{
			$timeLimit = $quizMeta['timeLimit'][0];
		}

		if( isset ($quizMeta['quizInstructions'][0] ) )
		{
			$quizInstructions = ekQuiz_utils::formatMetaboxText( $quizMeta['quizInstructions'][0] );
		}
		// Check if they can do the quiz
		//$qStr.='<h2>'.$quizName.'</h2>';


		$timeLimitInfo = '';


		if($timeLimit=="on")
		{
			$timeLimitMinutes = $quizMeta['timeLimitMinutes'][0];
			$timeLimitInfo = 'You will have '.$timeLimitMinutes.' minutes to complete this quiz';
			$qStr.='<div id="quizTimeLimit" class="hide">Time Remaining : <span id="quizTimeLimitRemaining"></span></div>';
		}


		$qStr.='<div id="ek-quizWrapper">';

		$accessCheckInfo = $ekQuizzes_CPT->checkQuizAccess($quizID); // Can they the quiz or not

		$allowAttempt = $accessCheckInfo[0]; // First Value of the array is true or false. False if not accces


		// Also check for any incomplete attempts and let them continue
		$qStr.=$valid_incomplete_attempts = $ekQuizzes_CPT->get_valid_incomplete_attempts($quizID);


		if($allowAttempt==true)
		{
			$qStr.=$quizInstructions;
			if($timeLimitInfo)
			{
				$qStr.='<div class="block">'.$timeLimitInfo.'</div>';
			}
		}
		else
		{
			$qStr.='<div class="block">'.$accessCheckInfo[1].'</div>';
		}

		$qStr.='<div class="buttons">';
		if($allowAttempt==true)
		{
			$qStr.='<button class="button is-success quizStartButton" id="quizID_'.$quizID.'">Start the Quiz</button>';
		}
		elseif(current_user_can('delete_pages'))
		{
			$qStr.='<button class="button is-success quizStartButton" id="quizID_'.$quizID.'">Preview Quiz</button>';
		}

		// Can the current person edit the quiz? If so show the quiz edit button
		if(current_user_can('delete_pages'))
		{
			$home_url = get_site_url();
			$qStr.='<a class="button" href="'.$home_url.'/wp-admin/post.php?post='.$quizID.'&action=edit">Edit Quiz</a>';
		}

		$qStr.='</div>'; // Close the buttons wwrapper
		$qStr.='</div>'; // Close the ek-quizWrapper


		$qStr.='<div style="clear: both;"></div>	'; // Clear the quiz wrapper

		/* Add the 'finish quiz modal  */
		$qStr.='<div id="quiz-finish-modal" class="ek-quiz-modal">';
		$qStr.='<div class="ek-quiz-modal-content"><span class="close-modal">&times;</span>';
		$qStr.='<p>Are you sure you want to finish this quiz?</p>';
		$qStr.='<br/><button id="quizFinishConfirm" data-id="'.$quizID.'" class="ek-quiz-nav-button">Finish the quiz</button>';
		$qStr.='</div></div>';

		/* Add the 'finish quiz modal if TIMED */
		$qStr.='<div id="quiz-timer-finish-modal" class="ek-quiz-modal">';
		$qStr.='<div class="ek-quiz-modal-content">';
		$qStr.='<p>Your time is up!</p>';
		$qStr.='<br/><button id="quizTimerFinishConfirm" data-id="'.$quizID.'" class="ek-quiz-nav-button">Submit your answers</button>';
		$qStr.='</div></div>';

		return $qStr;
	}

	public static function initaliseQuiz($quizID, $attemptID)
	{
		global $ek_quiz_database;
		global $ekQuizzes_CPT;

		$quizStr ='';



		// Now draw the first page of the quiz
		$args = array(
			"quizID"			=> $quizID,
			"currentPage"		=> 1,
            "attemptID"         => $attemptID,
		);

		// Returns array of quiz and if answer is correct so get the qStr KEY
		$quizStr.=self::drawQuizPage($args);


		return $quizStr;

	}

	public static function drawQuizPage($args)
	{

		// Load the required scripts
		ekQuiz::registerMyFrontScripts();

		global $ek_quiz_database;
		$pageStr = '';

		// Define the other vars in case missing
		$correctFeedback  = '';
		$incorrectFeedback  = '';
		$buttonText  = '';
		$quizReportScreen = '';
		if(isset($args['quizReportScreen']))
		{
			$quizReportScreen = $args['quizReportScreen'];
		}

		$quizID = $args ['quizID'];
		// Get the quiz meta
		$quiz_meta = get_post_meta($quizID);

		// SHow the feedback?

		$showQuestionFeedback = isset( $quiz_meta['showQuestionFeedback'] ) ? $quiz_meta['showQuestionFeedback'][0] : true;

		if($quizReportScreen==1)
		{
			$showQuestionFeedback = true;
		}

		$currentPage = $args['currentPage'];

        if(isset($args['attemptID']))
        {
            $attemptID=$args['attemptID'];
        }
        else
        {
            return 'Error - no attempt ID found';
        }

		$userID = get_current_user_id();
		// Get the question order details based on the attemptID
		$attemptInfo = $ek_quiz_database->getAttemptInfo( $attemptID );

		$quizQuestionsArray = $attemptInfo['questionOrder'];
		$quizQuestionsArray = unserialize($quizQuestionsArray);

		$userResponses = $attemptInfo['userResponses'];
		$userResponses = unserialize($userResponses);


		// If the quiz is finished read only is ON
		if($currentPage=="answers")
		{
			$readOnly = true;

			// Turn the questionsOnPageArray into a single array
			$fullPageArray = array();
			foreach($quizQuestionsArray as $thisPage => $thisPageQuestionArray)
			{
				foreach ($thisPageQuestionArray as $KEY => $myQuestionMeta)
				{
					$qType = $myQuestionMeta['qType'];
					$questionID = $myQuestionMeta['questionID'];
					$fullPageArray[] = array(
					"qType"			=> $qType,
					"questionID"	=> $questionID,
					"responseOptions"	=> $myQuestionMeta['responseOptions'],
					);
				}
			}

			$questionsOnPageArray = $fullPageArray;

		}
		else
		{
			$readOnly = false;
			$questionsOnPageArray = $quizQuestionsArray[$currentPage-1];
		}


		$pageCount = count($quizQuestionsArray);
		$totalCorrect=0;
		$totalWeighting = 0;
		$totalAvailableWeighting = 0;


		//  Loop through the questions and render to page
		// Create the question string
		$question_string = '';
		foreach ($questionsOnPageArray as $questionInfo)
		{
			$questionID = $questionInfo['questionID'];
			$qType = $questionInfo['qType'];


			$responseOptions = $questionInfo['responseOptions'];

			$thisUserResponse = "";

			if(isset($userResponses[$questionID])){$thisUserResponse = ekQuiz_utils::processDatabaseTextForTextarea($userResponses[$questionID]);}


            // Get the current question number
            $currentQuestionNumber = ekQuiz_utils::getCurrentQuestionNumber($questionID, $quizQuestionsArray);

			$args = array(

				"questionID" 			=> $questionID,
				"readOnly"				=> $readOnly,
				"correctFeedback"		=> $correctFeedback,
				"incorrectFeedback"		=> $incorrectFeedback,
				"userResponse"			=> $thisUserResponse,
				"showButtons"			=> false, // TO DO Check if flashcards and set true
				"responseOptionOrder"	=> $responseOptions,
				"buttonText"			=> $buttonText,
			);


			// If its the answers page don't show the individual submit buttons
			if($currentPage=="answers")
			{
				$args["showButtons"]	= false;
				$args["showCorrectAnswer"]	= "on";
			}


			// Get the qType for this question
			$thisQuestionClass = 'ek_'.$qType;

			$question_string.='<div class="ek-question" id="ek-question_'.$questionID.'_'.$qType.'">';


            // Show the currnet question number
            if($currentQuestionNumber)
            {
                $question_string.='<div class="ek_question_number">Question '.$currentQuestionNumber.'.</div>';
            }

			$question_string.= $thisQuestionClass::drawQuestion($args);
			$question_string.='</div>';


			// Mark and calculate score as well if its the answers page
			if($currentPage=="answers")
			{
				$markArgs = array(

					"questionID" 			=> $questionID,
					"userResponse"			=> $thisUserResponse,

				);

				$isCorrect= $thisQuestionClass::markQuestion($markArgs);


				// TODO ADD WEIGHTING
				$questionWeighting = 1;

				$totalAvailableWeighting = $totalAvailableWeighting+$questionWeighting;


				if($isCorrect==1)
				{
					$totalWeighting =$totalWeighting + $questionWeighting;
					$totalCorrect++;
				}

			}
		}



		/* Don't show any page nav if its the end of the quiz */
		if($currentPage=="answers")
		{

			$completionMessage =  ekQuiz_utils::formatMetaboxText(get_post_meta($quizID, "completionMessage", true));
			if($completionMessage)
			{
				$completionMessage = '<div class="completionMessage">'.$completionMessage.'</div>';
			}


			// Mark the Quiz as well
			$questionCount = count($questionsOnPageArray);

			// Get the percentage based on question weighting
			$percentageScore = round(($totalWeighting / $totalAvailableWeighting) * 100);

			$markedFeedback = '<div class="markOutOfTotalFeedback">You got <b>'.$percentageScore.'%</b> ('.$totalCorrect.' / '.$questionCount.' questions correct)</div>';

			// Update this in the DB - but only if not admin screen
			if($quizReportScreen<>1)
			{
				$ek_quiz_database->saveQuizScore($attemptID, $percentageScore);
			}

			// Check if there is grade boundary to show
			$boundaryFeedback =  ekQuiz_queries::getBoundaryFeedback($percentageScore, $quizID);
			//$boundaryFeedback.=do_shortcode($boundaryFeedback);
			if($boundaryFeedback)
			{
				$boundaryFeedback = '<div class="boundaryFeedback">'.$boundaryFeedback.'</div>';
			}

			$pageStr=$markedFeedback.$boundaryFeedback.$completionMessage;

			// Show the answers?
			if($showQuestionFeedback=="true")
			{
				$pageStr.=$question_string;
			}



			return $pageStr; // Return now as it's showing theanswers so need for submit buttons
		}


		$pageStr.=$question_string;
		$pageStr.='Page '.$currentPage.' / '.$pageCount.'<br/><br/>';

		// Add hidden form for the quiz ID
		$pageStr.='<input type="hidden" value="'.$quizID.'" id="quizID" name="quizID" />';
		$pageStr.='<input type="hidden" value="'.$currentPage.'" id="currentPage" name="currentPage" />';


		// If its the first page don't show back button
		$pageStr.='<div id="ek-quiz-nav">';
		if($currentPage<>1)
		{
			$pageStr.='<span class="ek-quiz-nav-button" id="ek-prevpage-button">Save and Go Back</span>';

		}

		// if its the last page don't show next button
		if($currentPage <> $pageCount)
		{
			$pageStr.='<button class="ek-quiz-nav-button" id="ek-nextpage-button" >Save and Continue</button>';
		}

		// If its the last page the show the finish button
		if($currentPage == $pageCount)
		{
			$pageStr.='<button class="ek-quiz-nav-button" id="ek-endquiz-button">Save and Finish</button>';
		}



		$pageStr.='</div>';



		return $pageStr;


	}


	static function drawUserResponse($atts)
	{

		$qStr = '';

		$atts = shortcode_atts(
			array(
				'id'		=> '',
				),
			$atts
		);


		$questionID = (int) $atts['id'];

		// Get the question type
		$qType = get_post_meta($questionID, 'qType', true);

		$userID = get_current_user_id();

		if($userID)
		{
			$ekQuiz_queries = new ekQuiz_queries();
			$responseInfo = $ekQuiz_queries->getUserResponse($questionID, $userID);
			$response = $responseInfo['userResponse'];
			$response =  wpautop(ekQuiz_utils::formatResponse($response));

			// if its radio then get the radio value
			if($qType=="singleResponse")
			{
				$responseOptions = get_post_meta($questionID, 'responseOptions', true);
				if(isset($responseOptions[$response]['optionValue']) )
				{
					$response = $responseOptions[$response]['optionValue'];
				}
			}

		}


		return $response;
	}

	// Draw the Font Awesome Icon - correct being true is a tick
	static function drawFeedbackIcon($correct, $size=2)
	{


		if($correct==true)
		{
			$iconClass = "fa-check";
			$backgroundClass = "correctIcon";

		}
		else
		{
			$iconClass = "fa-times";
			$backgroundClass = "incorrectIcon";

		}




		// Old JS transform that now breaks things :( */
		/*
		$icon='<div class="fa-'.$size.'x">';
		$icon.='<span class="fa-layers fa-fw">';
		$icon.='<i class="fas fa-circle '.$backgroundClass.'"></i>';
		$icon.='<i class="fa-inverse fas '.$iconClass.'" data-fa-transform="shrink-5" style="color:#fff"></i>';
		$icon.='</span>';
		$icon.='</div>';
		*/



		$circleSize = "2x";
		$iconSize = "1x";

		$float="";

		if($size==1)
		{
			$float = 'style="float:right;"';
		}

		if($size==1)
		{
			$circleSize = "xs";
			$iconSize = "xs";
		}

		$icon = '<div class="fa-stack fa-'.$circleSize.'" '.$float.' style="margin:10px;">
			<i class="fa fa-circle fa-stack-2x '.$backgroundClass.'"></i>
			<i class="fa '.$iconClass.' fa-stack-1x fa-inverse fa-'.$iconSize.'"></i>
			</div>';



		return $icon;

	}

	static function backIcon()
	{
		$html = '<i class="fas fa-chevron-circle-left"></i> ';

		return $html;

	}


	static function drawQuestionFeedback($questionID, $gotItCorrect, $additionalFeedback="")
	{
		$feedbackStr='<div class="qFeedbackWrap">';
		$feedbackStr.='<div class="qFeedbackIcon">';
		$feedbackStr.= ekQuizDraw::drawFeedbackIcon($gotItCorrect);

		$feedbackStr.='</div>';
		$feedbackStr.='<div class="qFeedbackText">';

		$feedbackArray = ekQuiz_utils::getQuestionFeedback($questionID);



		$feedbackStr.=$additionalFeedback;

		if($gotItCorrect==true)
		{

			$feedbackStr.= ekQuiz_utils::formatMetaboxText($feedbackArray['correctFeedback']);
		}
		else
		{

			$feedbackStr.=ekQuiz_utils::formatMetaboxText($feedbackArray['incorrectFeedback']);
		}

		$feedbackStr.='</div>';
		$feedbackStr.='</div>'; // Close the feedback wrap

		return $feedbackStr;
	}

	public static  function drawLeaderboard($atts)
	{


		// Load the required scripts
		ekQuiz::registerMyFrontScripts();


		$leaderboardStr = "";
		$atts = shortcode_atts(
			array(
				'id'   => '#',
				'anonymous'   => ''
			),
			$atts
		);

		$quizID = (int) $atts['id'];
		$anonymous = $atts['anonymous'];


		if($quizID)
		{

			// Get the quiz info
			$quizName = get_the_title($quizID);

			// Get the results
			$quizResults = ekQuiz_queries::getQuizResults($quizID);

			$masterResultsArray = array();

			// Create array of results with each userID as the Key, saving attemptcount and the highest score
			foreach($quizResults as $attemptInfo)
			{
				$userID = $attemptInfo['userID'];

				$highestScoreSoFar = 0;
				$attemptCountSoFar = 0;

				$thisAttemptScore = $attemptInfo['score'];
				$thisAttemptCount = $attemptInfo['attemptNumber'];

				if(!isset($masterResultsArray[$userID]) )
				{
					$masterResultsArray[$userID] = array(
						"highestScore" 	=> "0",
						"attemptCount"	=> "0",
					);

				}


				$highestScoreSoFar = $masterResultsArray[$userID]['highestScore'];
				$attemptCountSoFar = $masterResultsArray[$userID]['attemptCount'];


				if($thisAttemptScore>$highestScoreSoFar)
				{
					$masterResultsArray[$userID]['highestScore'] = $thisAttemptScore;
				}

				if($thisAttemptCount>$attemptCountSoFar)
				{
					$masterResultsArray[$userID]['attemptCount'] = $thisAttemptCount;
				}

			}



			$resultCount = count($masterResultsArray);
			$leaderboardStr = '<h2>'.$quizName.' Leaderboard</h2>';
			if($resultCount>=1)
			{


				// Get a list of all users in array
				$userLookupArray = ekQuiz_queries::getBlogUsers();


				$leaderboardStr.='<table id="leaderboard" class="ekTable">';
				$leaderboardStr.='<thead><tr><th>Name</th><th>Attempts</th><th>Highest Score</th></thead>';
				foreach($masterResultsArray as $userID => $attemptMeta)
				{

					$attemptCount = $attemptMeta['attemptCount'];
					$highestScore = $attemptMeta['highestScore'];

					if($anonymous==true)
					{
						$name = "Anonymous User ";
					}
					$leaderboardStr.= '<tr>';
					$leaderboardStr.= '<td>'.$userLookupArray[$userID]['firstName'].' '.$userLookupArray[$userID]['surname'].'</td>';
					$leaderboardStr.= '<td>'.$attemptCount.'</td>';
					$leaderboardStr.= '<td>'.$highestScore.'%</td>';
					$leaderboardStr.= '</tr>';
				}

				$leaderboardStr.='</table>';
			}
			else
			{
				$leaderboardStr.= 'Nobody has tried this quiz yet.';
			}

		}


		return $leaderboardStr;

	}


	// Draw the quiz list if the page slug is quizzes
	public static function draw_quiz_list($content)
	{
		if ( is_page() )
		{
			// Get the page slug
			global $post;
			$post_slug = $post->post_name;
			if($post_slug=="quizzes")
			{
				if(isset($_GET['quiz-id']) )
				{
					// Process the shortcode

					$atts = array(
						'id' => $_GET['quiz-id'],
					);

					return ekQuizDraw::drawShortcodeQuiz($atts);
				}
				else
				{

					$quiz_list = ekQuizDraw::quiz_list();
					return $content . $quiz_list;
				}

			}
		}
		return $content;
	}



	// Draw the quiz list if the page slug is quizzes
	public static function test_s3($content)
	{
		if ( is_page() )
		{
			// Get the page slug
			global $post;
			$post_slug = $post->post_name;
			if($post_slug=="s3test")
			{

				// test

				$test_blog_id = 252;

				$current_blog_id = get_current_blog_id();

				$switched = false;

			//	echo '$current_blog_id = '.$current_blog_id.'<br/>';

				if($test_blog_id<>$current_blog_id)
				{
				//	echo 'Switch to '.$test_blog_id.'<br/>';
					switch_to_blog($test_blog_id);
					$switched = true;
				}

				$question_object = get_post( 780 );
				$test_content = $question_object->post_content;

				//printArray($test_content);

				//echo '$test_content = '.$test_content;


				if($switched==true)
				{

					restore_current_blog();
				}
				return $test_content;

			}
		}
		return $content;
	}





	public static function quiz_list()
	{
		$html = '';

		// Get the quiz list
		$quizzes = ekQuiz_queries::getQuizzes();

		$ekQuizzes_CPT = new ekQuizzes_CPT();

		$quiz_count = count($quizzes);

		if($quiz_count==0)
		{
			return 'No quizzes found';

		}

		$html.='<table class="table is-fullwidth">';
		foreach ($quizzes as $quiz_info)
		{
			$quiz_id = $quiz_info->ID;
			$quiz_access = $ekQuizzes_CPT->checkQuizAccess($quiz_id);
			$show_quiz = $quiz_access[0];

			$reject_reason = '';
			if(isset($quiz_access[2] ) )
			{
				$reject_reason = $quiz_access[2];
			}

			$quiz_name = $quiz_info->post_title;

			if($reject_reason=="max-attempts-exceeded") // If attempts exceedded, show the quiz anyway as it won't let them take it
			{
				$show_quiz = 1;
			}

			if($show_quiz)
			{
				$html.='<tr>';
				$html.='<td class="is-size-4"><a href="?quiz-id='.$quiz_id.'">'.$quiz_name.'</a>';

				$html.='</td>';
				$html.='</tr>';
			}
		}
		$html.='</table>';

		return $html;
	}


	public static function option_count_breakdown($this_blog_id, $quiz_id)
	{

		$switched = 'false';
		$current_blog_id = get_current_blog_id();

		if($this_blog_id<>$current_blog_id)
		{
			switch_to_blog($this_blog_id);
			$switched=true;
		}


		// Get ALL results and add the question IDs to an array
		// We need to do this as individual question reults are only stored on single question stuff
		// Because quizzes can be randomised, we need to do this for each quiz

		$questions_attempted_array = array();
		$quiz_attempts = ekQuiz_queries::getQuizResults($quiz_id);


		$html = '';
		foreach ($quiz_attempts as $attempt_info)
		{

		    $questions = unserialize($attempt_info['questionOrder']);
		    $answers = unserialize($attempt_info['userResponses']);

		    foreach ($questions as $pages)
		    {
				foreach ($pages as $question_meta)
				{
					//printArray($question_meta);
			        $q_type = $question_meta['qType'];
			        $question_id = $question_meta['questionID'];

					$this_response = '';

					// Check the answer array exists, and if so get the response
					if(is_array($answers) )
					{
						if(array_key_exists($question_id, $answers) )
						{
							$this_response = $answers[$question_id];
						}
					}

			        $questions_attempted_array[$question_id]['q_type'] = $q_type;
			        $questions_attempted_array[$question_id]['responses']['all-responses'][] = $this_response;// add the answer if given

			        // ADd the ttoals if single response or checkbox
			        if($this_response)
			        {
			            switch ($q_type)
			            {
			                case "singleResponse":
			                    $current_count = 0;
			                    if(isset($questions_attempted_array[$question_id]['responses'][$this_response]) )
			                    {
			                        $current_count = $questions_attempted_array[$question_id]['responses'][$this_response];
			                        $current_count = $current_count + 1;
			                    }
			                    else
			                    {
			                        $current_count = 1;
			                    }
			                    $questions_attempted_array[$question_id]['responses'][$this_response] = $current_count; // add the answer if given
			                break;

			                case "multiResponse":
			                    //Convert the responses into an array as checkboxes can have nultiple answers
			                    $responses = explode(",", $this_response);
			                    $responses = array_filter($responses); // Remove blank values
			                    foreach ($responses as $this_value)
			                    {
			                        $current_count = 0;
			                        if(isset($questions_attempted_array[$question_id]['responses'][$this_value]) )
			                        {
			                            $current_count = $questions_attempted_array[$question_id]['responses'][$this_value];
			                            $current_count = $current_count + 1;
			                        }
			                        else
			                        {
			                            $current_count = 1;
			                        }
			                        $questions_attempted_array[$question_id]['responses'][$this_value] = $current_count; // add the answer if given

			                    }

			                break;
			            }
					}

		        }

		    }
		}



		// Now go through the master array and count the number of answers for each question


		$current_question_number=1; // This shows the current questino number in the div slider
		$slider_divs = array(); // Array of content for the slider
		foreach ($questions_attempted_array as $question_id => $question_info)
		{
		    $q_type =$question_info['q_type'];

		    switch ($q_type)
		    {
		        case "singleResponse":
		        case "multiResponse":
		            $response_options = get_post_meta($question_id, "responseOptions", true);


					$current_blog_id = get_current_blog_id();
		            // Get the question
		            $question = apply_filters('the_content', get_post_field('post_content', $question_id));
				//	$post_id = 302;
				//	$question_object = get_post( $question_id );

					//$question = 'Final Test '.$current_blog_id.'<br/>'.apply_filters( 'as3cf_filter_post_local_to_provider', $question );

				//	$question = $question_object->post_content;

		            // Go through the responses and draw the graph
		            $chart_data = array();

					$key_string = '<table class="table is-fullwidth">'; // String for the key
					// Create array of letters
					$letters = \icl_network\utils::generate_az_array();

					$current_option = 0;
		            foreach ($response_options as $option_key => $option_info)
		            {
		                $option_value = $option_info['optionValue'];

		                // Trim the words to 15
		            //    $option_value = wp_trim_words( $option_value, 9, '...'); // trim the wors
		                $is_correct = isset($option_info['isCorrect']) ? $option_info['isCorrect'] : '';

		                if($is_correct==1)
		                {
		                    $bar_color = "#336699";
		                }
		                else
		                {
		                    $bar_color = "#C81E78";
		                }

						// Create the graph letter
						$this_letter = $letters[$current_option];

		                // Get the number of responses given
		                $submitted_count_for_this = isset($questions_attempted_array[$question_id]['responses'][$option_key]) ? $questions_attempted_array[$question_id]['responses'][$option_key] : 0;
		                $tooltip = $option_value.' : '.$submitted_count_for_this.' responses';
		                $chart_data[] = array(  'value' => $submitted_count_for_this, 'label' => $this_letter, 'tooltip' => $tooltip, 'backgroundColor'=> $bar_color, );

						// Create the key
						$key_string.='<tr style="color:'.$bar_color.'"><td class="is-narrow">'.$this_letter.'</td><td>'.$option_value.'</td></tr>';

						$current_option++;
		            }

					$key_string.='</table>';

		            //echo \icl_network\draw::content_box_open('content');
		            //echo '<h2>'.$question.'</h2>';

		            $chart_args = array(
		                'legend'    => false,
		                'data'    => $chart_data,
		            );

		            //echo '<div style="width:500px">';
		            //echo \icl_network\imperial_chart::draw( $chart_args, 'bar' );
		            //echo '</div>';


		            //echo \icl_network\draw::content_box_close();
					$div = '';
					$div.='<div class="block">';
					$div.= '<div class="has-text-left has-text-weight-bold block">Question '.$current_question_number.'</div>';

					$div.='<div class="columns">';

					// Left column
					$div.='<div class="column pr-5">';
					$div.= '<div class="has-text-left">'.$question.'</div>';
					$div.='</div>';


					$div.='<div class="column mr-6">';
					$div.='<div style="width:300px" class="block">';
					$div.= \icl_network\imperial_chart::draw( $chart_args, 'bar' );
					$div.= '</div>';

					// Add the legend
					$div.='<div class="has-text-left">';
					$div.=$key_string;
					$div.= '</div>';
					$div.='</div>'; // End of right col


					$div.='</div>';// End of columns


					$div.='</div>'; // Ebd of block



					$slider_divs[] = $div;

					$current_question_number++;


		        break;

		    }


		}

		 $html.= imperial_slider::draw($slider_divs);

		 if($switched==true)
		 {
			 restore_current_blog();
		 }
		 return $html;

	}


}


?>
