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
		$qStr.='<div class="multiBlanks">';
	//	$qStr.='<div class="multiBlanks" id="ek-question-'.$questionID.'-'.$randomKey.'">';
		
		
		$tempQuestionString= apply_filters('the_content', get_post_field('post_content', $questionID));
		$blankCount =  substr_count($tempQuestionString, '[blank]'); // Count the number of blanks
		$i = 1;				
		while (strpos($tempQuestionString, '[blank]') !== false)
		{
			$tempQuestionString = preg_replace('[blank]', 'replace-me-'.$i++, $tempQuestionString, 1);
		}
		
		
		
		
		// Now go through and replace the inserts with actual inputs. Done as preg replace didn't seem to work properly :(				
		$myBlankResponseArray=array(); // Create blank array for submitted repsonses
		$correctBlankResponsesArray = array();
		$i=1;
		while($i<=$blankCount)
		{
			
			// Turn the possible answers into an array
			//$tempBlankCorrectArray = explode(",", ${'answers'.$i});
			//$correctBlankResponsesArray[$i] = $tempBlankCorrectArray;
			
			// Trim all whuitepsaces before and after
			//$tempBlankCorrectArray = array_map('trim', $tempBlankCorrectArray);
			
			// Get the submitted value of this box
			//$thisSubmittedValue = trim(strtolower($response[$i-1]));
			//$myBlankResponseArray[] = $thisSubmittedValue;
			
			// Check if its right and colour the boxes acoordingly
			/*
			if (in_array($thisSubmittedValue, $tempBlankCorrectArray))
			{
				$thisBoxClass = "correctFeedbackDiv";
			}
			else
			{
				$thisBoxClass = "incorrectFeedbackDiv";						
			}
				
			print_r($
								*/
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
		
		//$qStr.= str_replace('[blank]', '<input type="text" value="" size="10">', $tempQuestionString);		
		
		$qStr.=$tempQuestionString;
		
		
		
		/*
		$qStr.='<input type="text" name="qResponse_'.$questionID.'_'.$randomKey.'" ';
		$qStr.='id="qResponse_'.$questionID.'_'.$randomKey.'" value="'.$userResponse.'"';
		if($readOnly==true)
		{
			$qStr.= 'readOnly='.$readOnly;
			// Also add the correct / incorrect class if answers
			if($gotItCorrect==1)
			{
				$qStr.=' class="correctLI"';
			}
			else
			{
				$qStr.=' class="incorrectLI"';
			}
		}
		
		$qStr.='/>';
		*/
		
		
			
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
			
			
			
			$gotItCorrect = true; /* Correct be default then go through the answers and mark incorrect if any wrong */
			
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
			}
			
			// Show the feedback
			$qStr.=ekQuizDraw::drawQuestionFeedback($questionID, $gotItCorrect);			
	
		}
	
		// Close the Question div wrap
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
	
		
		
		/*
		//Convert to array for checking later
		foreach($responseOptions as $thisValue)
		{
			$correctAnswerArray[] = $thisValue['optionValue'];
		}
		
		$gotItCorrect='';
		if($userResponse)
		{
			$checkResponse = $userResponse;
			if($caseSensitive<>"on")
			{
				// Lowercase the response for checking
				$checkResponse = strtolower($checkResponse);
				$correctAnswerArray = array_map('strtolower', $correctAnswerArray);
				
			}
			
			if(in_array($checkResponse, $correctAnswerArray))
			{
				$gotItCorrect=1;
			}
		}			
		
		*/
		
		
		
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
				$thisUserResponse = '';
				if(isset($userResponse[$i]) )
				{
					$thisUserResponse = $userResponse[$i];
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
}
?>