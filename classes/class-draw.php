<?php
class ekQuizDraw
{


	static function drawUserResults($quizID, $CSV=false)
	{

		$html='';
		$csvArray = array();
		//dataTables js
		//qtl_utils::loadDataTables();

		$ekQuiz_queries = new ekQuiz_queries();

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
		$html.= '<thead><tr><th>Name</th><th>Username</th><th>Role</th><th>Highest Score</th><th>Number of attempts</th><th></th></tr></thead>';
		$csvArray[] = array("Last Name", "First Name", "Username", "Role", "Highest Score", "Attempts");



		$blogusers = get_users();

		// Array of WP_User objects.
		foreach ( $blogusers as $userInfo )
		{
			$userID = $userInfo->ID;
			$fullname = esc_html( $userInfo->display_name );
			$firstName= esc_html( $userInfo->first_name );
			$surname= esc_html( $userInfo->last_name );
			$username = $userInfo->user_login;
			$roles = $userInfo->roles;
			if($roles)
			{
				$userlevel = $roles[0];
			}
			else
			{
				$userlevel = "";
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
			$html.= '<td>'.$userlevel.'</td>';
			$html.= '<td>'.$maxScore.'</td>';
			$html.= '<td>'.$attemptCount.'</td>';
			$html.= '<td><a href="?page=ek-user-attempts&quizID='.$quizID.'&userID='.$userID.'">Results</a></td>';
			$html.= '</tr>';
			$csvArray[] = array ($surname,$firstName, $username, $userlevel,$maxScore,$attemptCount);



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
						"order": [[2, "desc"]]
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

		$accessCheckInfo = $ekQuizzes_CPT->checkQuizAccess($quizID);
		$allowAttempt = $accessCheckInfo[0]; // First Value of the array is true or false. False if not accces

		if($allowAttempt==true)
		{
			$qStr.=$quizInstructions;

			if($timeLimitInfo)
			{
				$qStr.='<hr/>'.$timeLimitInfo.'<hr/>';
			}
			$qStr.='<button class="quizStartButton" id="quizID_'.$quizID.'">Start the Quiz</button>';

		}
		else
		{
			$qStr.=$accessCheckInfo[1];
		}
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

		if(!isset($args['quizID']) )
		{
		//	return;
		}

		$quizID = $args ['quizID'];
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

			$pageStr.='<div class="ek-question" id="ek-question_'.$questionID.'_'.$qType.'">';


            // Show the currnet question number
            if($currentQuestionNumber)
            {
                $pageStr.='<div class="ek_question_number">Question '.$currentQuestionNumber.'.</div>';
            }

			$pageStr.= $thisQuestionClass::drawQuestion($args);
			$pageStr.='</div>';


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

			$pageStr=$markedFeedback.$boundaryFeedback.$completionMessage.$pageStr;

			return $pageStr; // Return now as it's showing theanswers so need for submit buttons
		}



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



}


?>
