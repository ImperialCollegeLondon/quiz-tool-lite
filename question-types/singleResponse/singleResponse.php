<?php
//$ek_singleResponse = new ek_singleResponse;
class ek_singleResponse
{
	static $qType = "singleResponse";


	// General Meta for this questino type, including the icon used when selecting questions
	static function questionMeta()
	{
		$qMeta = array(
			"qString" => 'Single Response',
			"qOptions" => true, // Not used currently - testing
			"qIcon"		=> "fa-dot-circle",
			"qCat"		=> "Mutiple Choice",
			"qCatOrder"	=> 1,

		);

		return $qMeta;
	}	//~~~~~


	static function drawQuestion($args)
	{
		global $ek_singleResponse;

		$userResponse = '';
		$qStr='';

		// Get Current user ID
		$currentUserID = get_current_user_id();

		// Get Defaults
		$defaults = ekQuiz::$defaults;




		foreach($args as $key => $value){$$key = $value;} # Turn all atts into variables of Key name


		$randomKey = '';
		if(isset($args['randomKey']) )
		{
			$randomKey = $args['randomKey'];
		}


		if(!isset($buttonText) || $buttonText=="")
		{
			$buttonText=$defaults['buttonText'];
		}


		if(!isset($showCorrectAnswer) || $showCorrectAnswer=="")
		{
			// Show Correct answer or not?
			$showCorrectAnswer = ekQuiz_utils::getQuestionShowAnswer($questionID);

		}



		// Get the user Response type to check for no answer submitted
		$userResponseType = gettype($userResponse); // get the user respose type. Check for NULL i.e. no answer

		// Get the response options and put them in a <ul> list
		$originalResponseOptions = get_post_meta($questionID, "responseOptions", true);

		$randomiseOptions = get_post_meta($questionID, "randomiseOptions", true);




		// if option order doesn't exist its the first time loading the question so create the order etc
		if(!isset($optionOrder) )
		{

			// Setup the Array of the option order
			$optionOrderArray = array();
			foreach ($originalResponseOptions as $key => $value)
			{
				$optionOrderArray[] = $key;
			}


			// If shuffle is set then shuffle the array but keep keys
			if($randomiseOptions=="on")
			{

				$originalResponseOptions = ekQuiz_utils::shuffle_array($originalResponseOptions);

				$optionOrderArray = array(); // Clear the array
				foreach ($originalResponseOptions as $key => $value)
				{
					$optionOrderArray[] = $key;
				}
			}
		}
		else
		{
			$optionOrderArray = $optionOrder; // Set the order of the responses using the INCOMING data as its already beed set

		}

		$args['optionOrder'] = $optionOrderArray;

		// Reconfigure the response option order based on this order
		$responseOptions = array();
		foreach ($optionOrderArray as $thisOptionKey)
		{
			$responseOptions[$thisOptionKey] = $originalResponseOptions[$thisOptionKey];
		}




		//$qStr='<div id="ek-question-'.$questionID.'-'.$randomKey.'">';
		$qStr.='<div>';


		$qStr.= apply_filters('the_content', get_post_field('post_content', $questionID));



		// Create array to pass to JS
		//$passData = htmlspecialchars(json_encode($args));

		$qStr.='<ul data-qtype="singleResponse" class="ek-responses" id="questionUL_'.$questionID.'-'.$randomKey.'">';


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


		// By default they haven't got it right
		$gotItCorrect=false;



		if($readOnly==true)
		{
			// See if they got it correct or not first
			foreach($responseOptionDisplayOrder as $optionID)
			{
				$isCorrect=false;
				if(isset($responseOptions[$optionID]['isCorrect']))
				{
					$isCorrect = $responseOptions[$optionID]['isCorrect'];
				}

				if($userResponse==$optionID && $userResponse<>"" && $isCorrect==1)
				{
					$gotItCorrect=true;
				}
			}
		}


		foreach($responseOptionDisplayOrder as $optionID)
		{
			$optionValue = $responseOptions[$optionID]['optionValue'];
			$isSelected = false;
			$isCorrect=false;
			if(isset($responseOptions[$optionID]['isCorrect']))
			{
				$isCorrect = $responseOptions[$optionID]['isCorrect'];
			}

			$qStr.='<label class="responseOptionItem" for="response_'.$questionID.'_'.$optionID.'">';

			$qStr.='<li class="ek-response';
			if($readOnly<>true)
			{
				$qStr.=' active';

				if($userResponse==$optionID && $userResponse<>"")
				{

					$qStr.=' selected ';
				}
			}
			else
			{
				// See if they actually for it right!
				if($userResponseType<>"NULL")
				{
					if($userResponse==$optionID && $userResponseType<>"NULL")
					{
						$isSelected = true;
						if($isCorrect==1)
						{
							$qStr.=' correctLI ';
						}
						else
						{
							$qStr.=' incorrectLI ';
						}
					}
					elseif($isCorrect==1 && $showCorrectAnswer=="on")
					{
						$qStr.=' correctLI ';
					}
				}

			}

			$qStr.='">';



			$qStr.='<input type="radio" name="qResponses_'.$questionID.'_'.$randomKey.'" value="'.$optionID.'" id="response_'.$questionID.'_'.$optionID.'"';


			if($isSelected==true && $userResponse<>"")
			{
				$qStr.= ' checked';
			}


			if($readOnly==true)
			{
				$qStr.=' disabled';
			}

			$qStr.= '>';

			$qStr.='<span class="checkmark"></span>';

			//echo 'userResponse = '.$userResponse.'<br/>';
			//echo 'optionID = '.$optionID.'<br/>';

			//echo '$showCorrectAnswer = '.$showCorrectAnswer.'<br/>';
			//echo '$isSelected = '.$isSelected.'<br/>';
			//echo '$readOnly = '.$readOnly.'<br/>';


			$qStr.=$optionValue;

			if($readOnly==true && ($showCorrectAnswer=="on" || $isSelected==true) )
			{


				if($userResponseType<>"NULL")
				{	if($optionID == $userResponse)
					{
						$qStr.= ekQuizDraw::drawFeedbackIcon($isCorrect, 1);
					}
				}
			}

			// Add additianl feedback for each option if it exists
			// ONly how it if its read only and they eother got it correct OR showCorrectAnswer is set to 1
			if($readOnly==true &&  ($isSelected==true || $showCorrectAnswer=="on" || $gotItCorrect==true))
			{



				$additionalFeedback = '';
				if($userResponse==$optionID)
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
					}					}

				if($additionalFeedback)
				{

					$qStr.='<div class="additionalFeedbackDiv"><span class="tooltip">?<span>Additional Information</span></span> ';
					$qStr.= ekQuiz_utils::formatMetaboxText($additionalFeedback);
					$qStr.='</div>';

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
			$passData = htmlentities(json_encode($args)); // Need to encode like this to account for apostrophes


			$qStr.='<div class="ekQuizButtonWrap" id="ekQuizButtonWrap_'.$questionID.'_'.$randomKey.'">';
			$qStr.='<input type="button" value="'.$buttonText.'" class="ekQuizButton" onclick="javascript:singleQuestionSubmit(\''.$passData.'\')";/>';
			$qStr.='</div>';
		}
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

		$responseType = gettype($userResponse); // get the user respose type. Check for NULL i.e. no answer
		if($responseType=="NULL")
		{
			return false;
		}

		// Get the response options
		$responseOptions = get_post_meta($questionID, "responseOptions", true);

		$gotItCorrect=false;
		foreach($responseOptions as $optionID => $responseMeta)
		{

			$isCorrect = '';
			if(isset($responseMeta['isCorrect']) )
			{
				$isCorrect = $responseMeta['isCorrect'];

			}

			if($userResponse==$optionID && $isCorrect==true)
			{
				$gotItCorrect=true;
			}


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
            $errorArray[] = "There are no response options assoicated with this question.";
        }


        return $errorArray;
    }


}
?>
