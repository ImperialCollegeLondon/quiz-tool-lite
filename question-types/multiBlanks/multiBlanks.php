<?php
$ek_multiBlanks = new ek_multiBlanks();
class ek_multiBlanks
{

	static $qType = "multiBlanks";

	static function questionMeta()
	{
		$qMeta = array(
			"qString" => 'Fill in the blanks',
			"qOptions" => true, // test
			"qIcon"		=> 'fa-th',
			"qCat"		=> "Text Based",
			"qCatOrder"	=> 2,
		);



		return $qMeta;
	}	//~~~~~

	static function drawQuestion($args)
	{
		$qStr='';
		$userResponse='';
		$ek_multiBlanks = new ek_multiBlanks();


		// Get Current user ID
		$currentUserID = get_current_user_id();

		// Get Defaults
		$defaults = ekQuiz::$defaults;


		foreach($args as $key => $value){$$key = $value;} # Turn all atts into variables of Key name

		if(!is_array($userResponse))
		{
			$userResponseArray=explode(",",$userResponse);
		}
		else{
			$userResponseArray = $userResponse;
		}



		// Show Correct answer or not?
		$showCorrectAnswer = ekQuiz_utils::getQuestionShowAnswer($questionID);



		if($buttonText=="")
		{
			$buttonText=$defaults['buttonText'];
		}


		//if($correctFeedback==""){$correctFeedback = get_post_meta($questionID, "correctFeedback", true);}
		//if($incorrectFeedback==""){$incorrectFeedback = get_post_meta($questionID, "incorrectFeedback", true);}
		//$correctFeedback = ekQuiz_utils::formatMetaboxText($correctFeedback);
		//$incorrectFeedback = ekQuiz_utils::formatMetaboxText($incorrectFeedback);
		// Get the response options - they all correct answers
		$responseOptions = get_post_meta($questionID, "responseOptions", true);



		// Case Sensitive Check
		$caseSensitive = get_post_meta($questionID, "caseSensitive", true);

		$userResponseClassLookup = array();
		//Convert to array for checking later
		$i=0;
		foreach($responseOptions as $thisValue)
		{

			$thisValueStr = $thisValue['optionValue'];


			$thisValueArray = explode(",",$thisValueStr);
			$correctAnswerArray[] = array_map('trim', $thisValueArray);
			$userResponseClassLookup[$i] = "";
			if(!isset($userResponseArray[$i]))
			{
				$userResponseArray[$i] = "";
			}
			if($userResponse)
			{
				$thisUserResponse = $userResponseArray[$i];

				$thisCorrectAnswerArray = $correctAnswerArray[$i];
				if($caseSensitive<>"on")
				{
					$thisUserResponse = strtolower($thisUserResponse);
					$thisCorrectAnswerArray = array_map('strtolower', $thisCorrectAnswerArray);
				}



				if(in_array($thisUserResponse, $thisCorrectAnswerArray))
				{
					$userResponseClassLookup[$i] = "correctLI";
				}
				else
				{
					$userResponseClassLookup[$i] = "incorrectLI";
				}
			}
			$i++;
		}



		/* Create array of user Responses along with if they were right or not */
		/* Used to add class to text boxes */


		$randomKey = '';
		if(isset($args['randomKey']) )
		{
			$randomKey = $args['randomKey'];
		}
		//$qStr.='<div class="multiBlanks">';
		$qStr.='<div class="multiBlanks" id="ek-question-'.$questionID.'-'.$randomKey.'">';


		$tempQuestionString= apply_filters('the_content', get_post_field('post_content', $questionID));
		$blankCount =  substr_count($tempQuestionString, '[blank]'); // Count the number of blanks
		$i = 1;

		while (strpos($tempQuestionString, '[blank]') !== false)
		{
			$tempQuestionString = preg_replace('/\[blank\]/', '[replace-me-'.$i++.']', $tempQuestionString, 1);
		}



		// Now go through and replace the inserts with actual inputs. Done as preg replace didn't seem to work properly :(
		$myBlankResponseArray=array(); // Create blank array for submitted repsonses
		$correctBlankResponsesArray = array();
		$i=1;
		while($i<=$blankCount)
		{
			// Replace the Blank with textbox

			$thisReplacement = '[replace-me-'.$i.']';

			// Only show the class if its read only
			$thisClass='';
			if($readOnly==true)
			{
				$thisClass = $userResponseClassLookup[$i-1];
			}

			//echo 'look to replace '.$thisReplacement.'<br/>';
			$tempQuestionString = str_replace($thisReplacement, '<input type="text" size="10" class="'.$thisClass.'" value = "'.$userResponseArray[$i-1].'" name="multiBlankInput_'.$questionID.'-'.$randomKey.'" id="blank_'.$questionID.'_'.$i.'" >', $tempQuestionString);
			$i++;
		}


		$qStr.=$tempQuestionString;



		// Only add random key if it doesn't exist
		if(!isset($randomKey))
		{

			// Also generate a random key in case of duplicates on the page
			$randomKey = ekQuiz_utils::randomKey(10);

		}




		if($showButtons==true)
		{
			// Add the Vars to the Args to pass via JSON to ajax function
			$args['randomKey'] = $randomKey;
			$args['userID'] = $currentUserID;
			$args['saveResponse'] = $saveResponse;
			$args['qType'] = self::$qType;

			// Create array to pass to JS
			$passData = htmlspecialchars(json_encode($args));

			$qStr.='<div class="ekQuizButtonWrap" id="ekQuizButtonWrap_'.$questionID.'_'.$randomKey.'">';
			$qStr.='<input type="button" value="'.$buttonText.'" class="ekQuizButton" onclick="javascript:singleQuestionSubmit(\''.$passData.'\')";/>';
			$qStr.='</div>';
		}


		// Show the feedback if its been marked
		if($readOnly==true)
		{
			$qStr.='<div class="qFeedbackWrap">';



			$gotItCorrect = false; /* Correct be default then go through the answers and mark incorrect if any wrong */

			$i=0;


			if($userResponse)
			{
				foreach ($userResponseArray as $thisResponse)
				{
					$thisBlankResponse = $thisResponse;


					if($thisBlankResponse==""){$thisBlankResponse = '<i>None Given</i>';}
					$thisBlankFeedbackDiv= '<div class="multiBlanksBlankNumberFeedback">Blank '.($i+1).'</div>';
					$thisBlankFeedbackDiv.='<div class="ek-multiBlankFeedbackAnswers">';
					$thisBlankFeedbackDiv.='<div class="ek-multiBlankFeedbackContent">';
					$thisBlankFeedbackDiv.= 'Your Response : <b>'.$thisBlankResponse.'</b><br/>';

					$thisBlankFeedbackDiv.='<p class="answers">Possible Answers:</p> ';
					//$thisBlankFeedbackDiv.='<ol>';

					$possibleAnswersStr= '';
					foreach($correctAnswerArray[$i] as  $possibleAnswer)
					{
						$possibleAnswersStr.=$possibleAnswer.', ';
						//$thisBlankFeedbackDiv.=  '<li>'. $possibleAnswer.'</li>';
					}

					//Remove last comma from poassible answers
					$thisBlankFeedbackDiv.=substr(rtrim($possibleAnswersStr), 0, -1);


					//$thisBlankFeedbackDiv.='</ol>';
					$thisBlankFeedbackDiv.='</div>';

					/* Start of the Icon Divs */
					$thisBlankFeedbackDiv.='<div class="qFeedbackWrap">';
					$thisBlankFeedbackDiv.='<div class="qFeedbackIcon">';

					if($userResponseClassLookup[$i] == "incorrectLI")
					{
						$thisBlankFeedbackDiv.=ekQuizDraw::drawFeedbackIcon(false, 1);
						$thisBlankFeedbackText='Incorrect';
						$gotItCorrect=false;
					}
					else
					{
						$thisBlankFeedbackDiv.=ekQuizDraw::drawFeedbackIcon(true, 1);
						$thisBlankFeedbackText='Correct';
                        $gotItCorrect=true;

					}


					$thisBlankFeedbackDiv.='</div>';
					$thisBlankFeedbackDiv.='<div class="qFeedbackText">';
					$thisBlankFeedbackDiv.=$thisBlankFeedbackText;

					$thisBlankFeedbackDiv.='</div>';
					$thisBlankFeedbackDiv.='</div>'; // Close the feedback wrap



					// only show the feedback if sowCorrect is on

					if($showCorrectAnswer=="on")
					{

						//$qStr.='<div class="'.$userResponseClassLookup[$i].'">';
						$qStr.='<div class="blankAnswerFeedback feedback-card">';
						$qStr.=$thisBlankFeedbackDiv;
						$qStr.='</div></div>';

					}



					$i++;

				}

              //  $qStr.='</div>';
			}

			// Show the feedback
			$qStr.=ekQuizDraw::drawQuestionFeedback($questionID, $gotItCorrect);
            $qStr.='</div>';


		}

		$qStr.='</div>';

		return $qStr;



	}

	public static function markQuestion($args)
	{
		$questionID = $args['questionID'];
		$userResponse = $args['userResponse'];

		// Get the response options
		$responseOptions = get_post_meta($questionID, "responseOptions", true);
		$caseSensitive = get_post_meta($questionID, "caseSensitive", true);


		$gotItCorrect = true; // By default got it correct
		$i=0;
		foreach($responseOptions as $thisValue)
		{

			$thisValueStr = $thisValue['optionValue'];
			$thisValueArray = explode(",",$thisValueStr);
			$correctAnswerArray[] = array_map('trim', $thisValueArray);
			$userResponseClassLookup[$i] = "";
			if($userResponse)
			{
                // Split the responses into an array
                $thisUserResponseArray = explode (",", $userResponse);
				if(isset($thisUserResponseArray[$i]) )
				{
					$thisUserResponse = $thisUserResponseArray[$i];
				}

				$thisCorrectAnswerArray = $correctAnswerArray[$i];
				if($caseSensitive<>"on")
				{
					$thisUserResponse = strtolower($thisUserResponse);
					$thisCorrectAnswerArray = array_map('strtolower', $thisCorrectAnswerArray);
				}

				if(!in_array($thisUserResponse, $thisCorrectAnswerArray) || $thisUserResponse=="") // If its not in the array or is blank its wrong
				{
					$gotItCorrect=false;
				}
			}
			else
			{
					$gotItCorrect=false;
			}
			$i++;
		}


		return $gotItCorrect;

	}

    public static function custom_metabox()
    {

        $qType = self::$qType;
        global $post;


        // do not show this metabo if post is not saved
        if($post->post_status !== 'publish')
        {
                return;
        }




        // Check for problems and show problems if there are any
        $questionID = $post->ID;
        $errorArray = self::check_for_problems($questionID);
        $errorCount = count($errorArray);
        if($errorCount==0)
        {
            return;
        }




        // Response Options
        $id 			= 'check_for_problems';
        $title 			= "Problems Found!";
        $drawCallback 	= array( "ek_".$qType, 'drawCustomMetabox' );
        $screen 		= 'ek_question';
        $context 		= 'side';
        $priority 		= 'high';
        $callbackArgs 	= array(
        "errorArray"	=> $errorArray,
        );


        add_meta_box(
            $id,
            $title,
            $drawCallback,
            $screen,
            $context,
            $priority,
            $callbackArgs

        );
    }

    public static function drawCustomMetabox ( $post, $callbackArgs )
    {
        echo '<div class="ek_question_check_error">There are possible problems with this question!</div>';
        echo '<div class="ek_question_check_error_list">';

        $i=1;
        foreach ($callbackArgs['args']['errorArray'] as $thisError)
        {
            echo '<strong>Problem '.$i.'. </strong><br/> '.$thisError.'<br/>';
            $i++;
        }

        echo '</div>';


    }

    public static function check_for_problems($questionID)
    {

        global $post;
        $errorArray = array();

        $questionID = $post->ID;

        $responsesCount = 0;
        $responseOptions = get_post_meta($questionID, "responseOptions", true);

        // Get the totla number or blanks
        if(is_array($responseOptions) )
        {
            $responsesCount = count($responseOptions);
        }



        if($responsesCount==0)
        {
            $errorArray[] = "There are no answers assoicated with this question.";
        }



        $page_object = get_page( $questionID );
        $tempQuestionString =  $page_object->post_content;

        $blankCount =  substr_count($tempQuestionString, '[blank]'); // Count the number of blanks


        if($blankCount==0)
        {
            $errorArray[] = "There are no blanks in the question.";
        }

        if($blankCount<>$responsesCount)
        {
            $errorArray[] = "You have a different number of blanks(".$blankCount.") than responses. (".$responsesCount.")";
        }



        return $errorArray;
    }
}
?>
