<?php
$ek_shortTextResponse = new ek_shortTextResponse();
class ek_shortTextResponse
{
	
	static function questionMeta()
	{
		$qMeta = array(
			"qString" => 'Short Text Response',
			"qOptions" => true, // test
			"qIcon"		=> 'fa-pencil-alt',
			"qCat"		=> "Text Based",
			"qCatOrder"	=> 1,

		);
		
		
		
		return $qMeta;
	}	//~~~~~
	
	static function drawQuestion($args)
	{
		$qStr='';
		$userResponse='';
		$ek_shortTextResponse = new ek_shortTextResponse();
				
		// Get Current user ID
		$currentUserID = get_current_user_id();
		
		// Get Defaults
		$defaults = ekQuiz::$defaults;
		
		
		foreach($args as $key => $value){$$key = $value;} # Turn all atts into variables of Key name
		
		// Show Correct answer or not?
		$showCorrectAnswer = ekQuiz_utils::getQuestionShowAnswer($questionID);
		
		
		if(!isset($buttonText) || $buttonText=="")
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
		//Convert to array for checking later
		foreach($responseOptions as $thisValue)
		{
			$correctAnswerArrayLookup[] = $thisValue['optionValue'];
		}
		
		$gotItCorrect='';
		if($userResponse)
		{
			$checkResponse = trim ($userResponse);
			if($caseSensitive<>"on")
			{
				// Lowercase the response for checking
				$checkResponse = strtolower($checkResponse);
				
				$correctAnswerArray = array_map('strtolower', $correctAnswerArrayLookup);
				
			}
			else
			{
				$correctAnswerArray = $correctAnswerArrayLookup;
			}
			
			if(in_array($checkResponse, $correctAnswerArray))
			{
				$gotItCorrect=1;
			}
		}		

		
		$randomKey = '';
		if(isset($args['randomKey']) )
		{
			$randomKey = $args['randomKey'];	
		}		
		
	//	$qStr.='<div class="shortTextResponse" id="ek-question-'.$questionID.'-'.$randomKey.'">';
		$qStr.='<div class="shortTextResponse">';
		$qStr.= apply_filters('the_content', get_post_field('post_content', $questionID));
		
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
		
		
			
		// Only add random key if it doesn't exist
		if($randomKey=="")
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
			$args['qType'] = 'shortTextResponse';
			
			// Create array to pass to JS
			$passData = htmlspecialchars(json_encode($args));			
			
			$qStr.='<div class="ekQuizButtonWrap" id="ekQuizButtonWrap_'.$questionID.'_'.$randomKey.'">';
			$qStr.='<input type="button" value="'.$buttonText.'" class="ekQuizButton" onclick="javascript:singleQuestionSubmit(\''.$passData.'\')";/>';
			$qStr.='</div>';
		}
		
		
		// Show the feedback if its been marked
		if($readOnly==true)
		{
			
			
			if($gotItCorrect==false && $showCorrectAnswer=="on")
			{
				$correctAnswerCount = count($correctAnswerArrayLookup);
				$qStr.='<br/>Correct answers were ';
				$i=1;
				foreach ($correctAnswerArrayLookup as $correctAnswerOption)
				{
					$qStr.='"<strong>'.$correctAnswerOption.'</strong>"';
					if($i==($correctAnswerCount-1))
					{
						$qStr.=' and ';
					}
					elseif($i<$correctAnswerCount)
					{
						$qStr.=', ';
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
	
		
		//Convert to array for checking later
		foreach($responseOptions as $thisValue)
		{
			$correctAnswerArray[] = $thisValue['optionValue'];
		}
		
		$gotItCorrect='';
		
		if($userResponse)
		{
			if($caseSensitive<>"on")
			{
				// Lowercase the response for checking
				$userResponse = strtolower($userResponse);
				$correctAnswerArray = array_map('strtolower', $correctAnswerArray);
				
				
			}
			
			if(in_array($userResponse, $correctAnswerArray))
			{
				$gotItCorrect=1;
			}
		}			
		return $gotItCorrect;		
		return $gotItCorrect;		
		
	}	
	
}
?>