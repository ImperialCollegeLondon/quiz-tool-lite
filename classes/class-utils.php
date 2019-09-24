<?php
if (!class_exists('ekQuiz_utils'))
{
	class ekQuiz_utils
	{

		static function randomKey($length) {

			$key = '';
			$pool = array_merge(range(0,9), range('a', 'z'),range('A', 'Z'));
			for($i=0; $i < $length; $i++) {
			$key .= $pool[mt_rand(0, count($pool) - 1)];
			}
			return $key;
		}


		static function formatResponse($response)
		{
			$response = stripslashes($response);
			$response = convert_chars($response);
			$response = wptexturize($response);
			return $response;
		}


		/*
		Validate an email address.
		Provide email address (raw input)
		Returns true if the email address has the email
		address format and the domain exists.
		*/
		public static function validEmail($email)
		{
		   $isValid = true;
		   $atIndex = strrpos($email, "@");
		   if (is_bool($atIndex) && !$atIndex)
		   {
			  $isValid = false;
		   }
		   else
		   {
			  $domain = substr($email, $atIndex+1);
			  $local = substr($email, 0, $atIndex);
			  $localLen = strlen($local);
			  $domainLen = strlen($domain);
			  if ($localLen < 1 || $localLen > 64)
			  {
				 // local part length exceeded
				 $isValid = false;
			  }
			  else if ($domainLen < 1 || $domainLen > 255)
			  {
				 // domain part length exceeded
				 $isValid = false;
			  }
			  else if ($local[0] == '.' || $local[$localLen-1] == '.')
			  {
				 // local part starts or ends with '.'
				 $isValid = false;
			  }
			  else if (preg_match('/\\.\\./', $local))
			  {
				 // local part has two consecutive dots
				 $isValid = false;
			  }
			  else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
			  {
				 // character not valid in domain part
				 $isValid = false;
			  }
			  else if (preg_match('/\\.\\./', $domain))
			  {
				 // domain part has two consecutive dots
				 $isValid = false;
			  }
			  else if
		(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
						 str_replace("\\\\","",$local)))
			  {
				 // character not valid in local part unless
				 // local part is quoted
				 if (!preg_match('/^"(\\\\"|[^"])+"$/',
					 str_replace("\\\\","",$local)))
				 {
					$isValid = false;
				 }
			  }
			  if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
			  {
				 // domain not found in DNS
				 $isValid = false;
			  }
		   }
		   return $isValid;
		}

		public static function br2nl($text, $tags="br")
		{
			$tags = explode(" ", $tags);

				foreach($tags as $tag)
				{
					$text = eregi_replace("<" . $tag . "[^>]*>", "\n", $text);
					$text = eregi_replace("]*>", "\n", $text);
				}

			return($text);
		}


		public static function dateDiff($startDate, $endDate)
		{
			// Assumes input it a mysql date format
			$startDate = strtotime($startDate);
			$endDate = strtotime($endDate);

			$secondsSinceResponse = $endDate-$startDate;

			$daysResponse = $secondsSinceResponse / 86400;
			$daysResponse = number_format($daysResponse, 0);

			$daysResponse = $secondsSinceResponse / 86400;
			$daysResponse = floor($daysResponse);

			$temp_remainder = $secondsSinceResponse - ($daysResponse * 86400);
			$hours = floor($temp_remainder / 3600);

			$temp_remainder = $temp_remainder - ($hours * 3600);
			$minutes = floor($temp_remainder / 60);

			$seconds = $temp_remainder - ($minutes * 60);
			if($daysResponse==0)
			{
				if($hours==0)
				{
					if($minutes==0)
					{
						$dateDiff = $secondsSinceResponse.' seconds';
					}
					else
					{
						$dateDiff= $minutes.' minute(s), '.$seconds.' seconds';
					}
				}
				else
				{
					$dateDiff= $hours.' hour(s), '.$minutes.' minutes, '.$seconds.' seconds';
				}

			}
			else
			{
				$dateDiff=$daysResponse.' day(s), '.$hours.' hour, '.$minutes.' minutes, '.$seconds.' seconds';
			}

			$dateDiffArray = array();
			$dateDiffArray['seconds'] = $secondsSinceResponse;
			$dateDiffArray['str'] = $dateDiff;

			return $dateDiffArray;
		}



		/**
		 * Returns a random string of numbers and letters [0-9][A-Z]
		 * Note: This is not secure random
		 * @param $chars The number of characters in the random string
		 * @return String containing random numbers and letters
		 */
		public static function randomString ($chars)
		{
			$randStr = "";

			while ($chars > 0) {
				$ord = rand(48, 90);
				if (($ord >= 48 && $ord <=57) || ($ord >= 65 && $ord <=90)) {
					$randStr .= chr($ord);
					$chars -= 1;
				}
			}

			return $randStr;
		}


		/**
		 * Returns a string with http on the front if it doesn't exist. Used for redirection after quiz
		 * @param $url The string to parse
		 * @return String containing the correct http
		 */
		public static function addhttp($url)
		{
			if (!preg_match("~^(?:f|ht)tps?://~i", $url))
			{
				$url = "http://" . $url;
			}
			return $url;
		}


		// Uses the colorMeter below
		public static function generateRedGreenColourArray($stepCount)
		{

			if($stepCount==0)
			{
				$colourArray = 	array();
			}
			elseif($stepCount==1)
			{
				$colourArray = array("#00820B");
			}
			else
			{
				$percentStep = 1/($stepCount-1); // Subtract 1 from ttola step count for some reason :( eeek

				$colourArray = array();
				for ($i = 0.0; $i <= 1.0; $i += $percentStep)
				{
					$RGB = ekQuiz_utils::colorMeter($i);
					$colourArray[] = '#'.$RGB;
				}
			}
			return $colourArray;
		}

		// Returns an array of gradient colours based on starting colout and ending colour and steps between red and green
		public static function colorMeter($percent, $invert = false)
		{
			//$percent is in the range 0.0 <= percent <= 1.0
			//    integers are assumed to be 0% - 100%
					 // and are converted to a float 0.0 - 1.0
			//     0.0 = red, 0.5 = yellow, 1.0 = green
			//$invert will make the color scale reversed
			//     0.0 = green, 0.5 = yellow, 1.0 = red

			//convert (int)% values to (float)
			if (is_int($percent)) $percent = $percent * 0.01;

			$R = min((2.0 * (1.0-$percent)), 1.0) * 210.0;
			$G = min((2.0 * $percent), 1.0) * 130.0;
			$B = 11;

			return (($invert) ?
		sprintf("%02X%02X%02X",$G,$R,$B)
		: sprintf("%02X%02X%02X",$R,$G,$B));
		}


		// Parses the DB  content adds line breaks and removes forward slashes for on page rendering
		public static function formatMetaboxText($input)
		{
		//	$output = sanitize_textarea_field( $input );
			$output = wpautop( $input );
			$output = stripslashes($output);

			return $output;
		}

		// Parses the DB  content for rending to input field
		public static function processDatabaseTextForTextarea($input)
		{

				$output = esc_textarea($input);
				$output = sanitize_textarea_field( $output );
				$output = stripslashes($input);

				return $output;

		}





		public static function getDefaultQuestionFeedback()
		{
			global $ekQuiz;

			$defaultCorrect = $ekQuiz::$defaults['correctFeedback'];
			$defaultIncorrect = $ekQuiz::$defaults['incorrectFeedback'];


			$correctFeedback = get_option('ek-quiz-correct-feedback', $defaultCorrect);
			$incorrectFeedback = get_option('ek-quiz-incorrect-feedback', $defaultIncorrect);


			$feedbackArray = array(
				'correctFeedback'	=> ekQuiz_utils::processDatabaseTextForTextarea($correctFeedback),
				'incorrectFeedback'	=> ekQuiz_utils::processDatabaseTextForTextarea($incorrectFeedback),
			);

			return $feedbackArray;
		}

		public static function getDefaultShowCorrectAnswer()
		{

			$showCorrectAnswer = get_option('ek-quiz-showCorrectAnswer', "on");

			return $showCorrectAnswer;
		}


		public static function getQuestionShowAnswer($questionID)
		{

			$showCorrectAnswer = get_post_meta($questionID,'showCorrectAnswer',true);

			if($showCorrectAnswer=='')
			{
				$showCorrectAnswer  = ekQuiz_utils::getDefaultShowCorrectAnswer();
			}



			return $showCorrectAnswer;
		}




		public static function getQuestionFeedback($questionID)
		{
			$defaultFeedack = ekQuiz_utils::getDefaultQuestionFeedback();

			$correctFeedback = get_post_meta($questionID, 'correctFeedback', true);
			$incorrectFeedback = get_post_meta($questionID, 'incorrectFeedback', true);

			$feedbackArray = array();

			// If the question correct feedback is empty add the default feedback instead
			if($correctFeedback=="")
			{
				$feedbackArray['correctFeedback']=$defaultFeedack['correctFeedback'];
			}
			else{
				$feedbackArray['correctFeedback']=$correctFeedback;
			}

			if($incorrectFeedback=="")
			{
				$feedbackArray['incorrectFeedback']=$defaultFeedack['incorrectFeedback'];
			}
			else{
				$feedbackArray['incorrectFeedback']=$incorrectFeedback;
			}


			return $feedbackArray;
		}



		public static function shuffle_array(&$array)
		{
			$keys = array_keys($array);

			shuffle($keys);

			foreach($keys as $key) {
				$new[$key] = $array[$key];
			}

			$array = $new;

			return $array;

		}

		public static function isTextBasedAnswer($qType)
		{
			$textTypeAnswers = array("shortTextResponse", "multiBlanks");

			if(in_array($qType, $textTypeAnswers) )
			{
				return true;
			}
			else
			{
				return false;
			}

		}



	}// End of class
}// End of if class exists
?>
