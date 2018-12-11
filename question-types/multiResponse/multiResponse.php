<?php
$ek_multiResponse= new ek_multiResponse();
class ek_multiResponse
{
	static $qType = "multiResponse";
	
	static function questionMeta()
	{
		$qMeta = array(
			"qString" => 'Multiple Response',
			"qOptions" => true, // test
			"qIcon"		=> "fa-check-square",
			"qCat"		=> "Mutiple Choice",
			"qCatOrder"	=> 2,

		);
		
		
		
		return $qMeta;
	}	//~~~~~
	
	static function drawQuestion($args)
	{
		global $ek_multiResponse;
		
		$userResponse='';
		$qStr = '';
		if(isset($args['userResponse'])){$userResponse = $args['userResponse'];}
		
		// Get Defaults
		$defaults = ekQuiz::$defaults;
		
		
		if(!isset($buttonText))
		{
			$buttonText=$defaults['buttonText'];
		}
		
		
		
		// Turn into an array if its not already one.
		// If it comes from a single question its aready an array which is handy
		if(!is_array($userResponse))
		{
			$userResponseArray=array_filter(explode(",",$userResponse)); // Turn into an array and remove blank values
		}
		else{
			$userResponseArray = $userResponse;
		}
		



		
		// Get Current user ID
		$currentUserID = get_current_user_id();
		
		// Get Defaults
		$defaults = ekQuiz::$defaults;
		// Turn all args into a variable of the same name
		foreach($args as $key => $value)
		{
			$$key = $value;	
		}
		
		if(!isset($showCorrectAnswer) || $showCorrectAnswer=="")
		{
			// Show Correct answer or not?
			$showCorrectAnswer = ekQuiz_utils::getQuestionShowAnswer($questionID);		
			
		}		
		
		
		
		
		if($buttonText=="")
		{
			$buttonText=$defaults['buttonText'];
		}	
		
		
		// Get the response options and put them in a <ul> list
		$responseOptions = get_post_meta($questionID, "responseOptions", true);
		
		//if($correctFeedback==""){$correctFeedback = get_post_meta($questionID, "correctFeedback", true);}
		//if($incorrectFeedback==""){$incorrectFeedback = get_post_meta($questionID, "incorrectFeedback", true);}
		
		
		$randomKey = '';
		if(isset($args['randomKey']) )
		{
			$randomKey = $args['randomKey'];	
		}		
		//$qStr.='<div id="ek-question-'.$questionID.'-'.$randomKey.'">';
		$qStr.='<div>';
		
		$qStr.= apply_filters('the_content', get_post_field('post_content', $questionID));
		
		
		$gotItCorrect=true; // Set it correct by default
		
		// Add the Vars to the Args to pass via JSON to ajax function
		
		// Create array to pass to JS
		$passData = htmlspecialchars(json_encode($args));		
		
		
		// If its ranomising the answers then stick the randomised values into the order array
		$responseOptionDisplayOrder = array();
		if(isset($responseOptionOrder))
		{
			$responseOptionDisplayOrder = $responseOptionOrder;
		}
		else
		{
			
			foreach($responseOptions as $optionID => $responseMeta)
			{	
				$responseOptionDisplayOrder[] = $optionID; 
			}
		
		}		
		
		
		
		
		$qStr.='<ul class="ek-responses" data-qtype="multiResponse" id="questionUL_'.$questionID.'-'.$randomKey.'">';
			
			
		// See if they got it right first
		
		
		if($readOnly==true)
		{
			foreach($responseOptionDisplayOrder as $optionID)
			{
				$isCorrect = '';
				if(isset($responseOptions[$optionID]['isCorrect']))
				{
					$isCorrect = $responseOptions[$optionID]['isCorrect'];
				}	

				if(in_array($optionID, $userResponseArray) )
				{
					if($isCorrect<>true)
					{
						$gotItCorrect = false;						
					}						
						
				}
				elseif($isCorrect==true)
				{	
					$gotItCorrect = false;						

				}
			}
		}

			
			
	
		foreach($responseOptionDisplayOrder as $optionID)
		{
			
			$optionValue = $responseOptions[$optionID]['optionValue'];
			$isCorrect = '';
			
			if(isset($responseOptions[$optionID]['isCorrect']))
			{
				$isCorrect = $responseOptions[$optionID]['isCorrect'];
			}
			$qStr.='<label class="multiResponseItem" for="response_'.$questionID.'_'.$optionID.'" >';
			

			
			$qStr.='<li class="ek-response';
						
			// See if they actually for it right!
			if($readOnly==true)
			{

				if(in_array($optionID, $userResponseArray) )
				{
					if($isCorrect==true)
					{
						$qStr.=' correctLI ';
					}
					else
					{
						$qStr.=' incorrectLI ';
					}						
						
				}
				elseif($isCorrect==true)
				{
					if($showCorrectAnswer=="on")
					{
						
						$qStr.=' correctLI ';
					}					
				}
			}
			elseif(in_array($optionID, $userResponseArray) )				
			{
				$qStr.=' selected ';
			}
			
			if($readOnly==false)
			{
			
				$qStr.=' active ';
			}

				
				
			
			
			
			$qStr.='">';
			
			
			$qStr.='<input type="checkbox" name="qResponses_'.$questionID.'_'.$randomKey.'" value="'.$optionID.'" id="response_'.$questionID.'_'.$optionID.'"';
			if(in_array($optionID, $userResponseArray) )
			{
				$qStr.= ' checked';
			}
			if($readOnly==true)
			{
				$qStr.=' disabled readonly ';
			}
			
			$qStr.= '>';
			$qStr.='<span class="checkmark"></span>';
			$qStr.=$optionValue;
			
			// Add Correct or incorrect tick if its selected and wrong / right
			if($readOnly==true)
			{
		
				if(in_array($optionID, $userResponseArray))
				{	
					$fa_icon= 'fa-check';
				
					if($isCorrect==true)
					{
						$fa_icon= 'fa-check';
					}	
					else
					{
						$fa_icon= 'fa-times';
		
					}
				//	$qStr.='<i class="fa '.$fa_icon.'" aria-hidden="true"></i>';	
					$qStr.= ekQuizDraw::drawFeedbackIcon($isCorrect, 1);
					
				}
				

				// Additional feedback for each option
				$additionalFeedback = '';
				
				$showAdditionalFeedback=false;


				
				if($readOnly==true && ($showCorrectAnswer=="on" || $gotItCorrect==true) )
				{
					$showAdditionalFeedback=true;
				}
				


				
				
				
				if($showAdditionalFeedback==true)
				{
									
					if(in_array($optionID, $userResponseArray) )
					{					
						if(isset($responseOptions[$optionID]['feedbackIfSelected']) )
						{
							$additionalFeedback = $responseOptions[$optionID]['feedbackIfSelected'];
						}					
					
					}	
					else
					{
						if(isset($responseOptions[$optionID]['feedbackIfNotSelected']) )
						{					
							$additionalFeedback = $responseOptions[$optionID]['feedbackIfNotSelected'];	
						}
					}	
					
					if($additionalFeedback)
					{
						$qStr.='<div class="additionalFeedbackDiv"><span class="tooltip">?<span>Additional Information</span></span> ';
						$qStr.= ekQuiz_utils::formatMetaboxText($additionalFeedback);
						$qStr.='</div>';			
						
					}					
					
				}
				

				
				
			}
			
			
		
			$qStr.='</li></label>';
		}
		$qStr.='</ul>';
		
		
		
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
		
		
				
		// What to show if they get it correct
		if($readOnly==true)
		{
			
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
		
		// Turn to array
		$userResponse = explode(",", $userResponse);
		array_filter($userResponse);
		$gotItCorrect=true; // Set it correct by default
		
		// Get the response options
		$responseOptions = get_post_meta($questionID, "responseOptions", true);
		
	
		foreach($responseOptions as $optionID => $responseMeta)
		{
			
			$isCorrect = '';
			if(isset($responseMeta['isCorrect']) )
			{
				$isCorrect = $responseMeta['isCorrect'];
			}			
	
			// It its been checked
			if(in_array($optionID, $userResponse) )
			{
				//... and its NOT correct then they've got it wrong
				if($isCorrect<>true)
				{
					$gotItCorrect=false;
				}
			}
			// Else they havne't checked it but it SHOULD be its also wrong
			elseif($isCorrect==true)
			{
				$gotItCorrect= false;
			}	
	
		}
		
		
		return $gotItCorrect;		
		
	}
	
}
?>