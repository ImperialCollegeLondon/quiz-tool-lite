<?php
if ( isset($_GET['myAction'] ) ) 
{
	
	$myAction = $_GET['myAction'];
	
	switch ($myAction)
	{
		case "exportQuestionsCSV":
			// Handle CSV Export
			add_action( 'admin_init', array('ekQuiz_export', 'csv_export') );
		
		break;
		
		case "exportQuizResultsCSV":
			// Handle CSV Export
			add_action( 'admin_init', array('ekQuiz_export', 'exportQuizResultsCSV') );
		
		break;		
		
	}	
	
	
}


class ekQuiz_export
{
	
	
	
	static function exportQuizResultsCSV()
	{

		// Check permissions	
		if(!current_user_can(get_option('min_quiz_access_level', 'manage_options')))
		{
			return;
		}	
		
		$quizID = $_GET['quizID'];		
		$postTitle = get_the_title($quizID);	
		$fileNameStart = preg_replace("/[^A-Za-z0-9 ]/", '', $postTitle).'-results-'.$quizID;
		$fileName = $fileNameStart.'.csv';
		
		$CSV_array = ekQuizDraw::drawUserResults($quizID, true);

		
		
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header('Content-Description: File Transfer');
		ob_end_clean();		 // Remove unwanted blank spaces / line breaks
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename={$fileName}");
		header("Expires: 0");
		header("Pragma: public");
		
		$fh = @fopen( 'php://output', 'w' );
		
		foreach ($CSV_array as $fields) {
			fputcsv($fh, $fields);
		}				
		
		// Close the file
		fclose($fh);
		// Make sure nothing else is sent, our file is done
		die();		
		
		
		
		
		
		
	}
	
	
	
	
	
	// Get an array of blog users and their role
	static function generateExportFile()
	{
		
		// Check permissions	
		if(!current_user_can(get_option('min_quiz_access_level', 'manage_options')))
		{
			return;
		}
		
		
		$CSV_array = array();
		$CSV_array[] = array ('Item ID', 'Item Type');
		// Get all question pots
		$allPots = ekQuiz_queries::getPots();
		
		foreach($allPots as $potMeta)
		{
			$potName = $potMeta->post_title;
			$potID = $potMeta->ID;
			
			// Add the pot to the CSV
			$CSV_array[] = array (''.$potID.'', 'POT',''.$potName.'');
			
			// Now get the question in this pot
			// Get the question count
			$args = array("potID" => $potID);
			$potQuestions = ekQuiz_queries::getPotQuestions($args);
			
			
			foreach ($potQuestions as $questionInfo)
			{
				$questionTitle = $questionInfo->post_title;
				$questionContent = $questionInfo->post_content;
				$questionID = $questionInfo->ID;
				
				// Get the question Meta
				$questionMeta = get_post_meta($questionID);		
				$correctFeedback = '';
				$incorrectFeedback = '';
				$randomiseOptions = '';
				$caseSensitive = '';
				
				
				$qType = $questionMeta['qType'][0];
				if(isset($questionMeta['correctFeedback'][0]) )
				{
					
					$correctFeedback = $questionMeta['correctFeedback'][0];
				}
				
				if(isset($questionMeta['incorrectFeedback'][0]) )
				{
					
					$incorrectFeedback = $questionMeta['incorrectFeedback'][0];
				}
				
				if(isset($questionMeta['randomiseOptions'][0]) )
				{
					
					$randomiseOptions = $questionMeta['randomiseOptions'][0];
				}
				
				if(isset($questionMeta['caseSensitive'][0]) )
				{
					
					$caseSensitive = $questionMeta['caseSensitive'][0];
				}
				
				
				if(isset($questionMeta['addTextarea'][0]) )
				{
					
					$addTextarea = $questionMeta['addTextarea'][0];
				}	
				
				
				
				if($randomiseOptions=="on")
				{
					$randomiseOptions = 'randomise';
				}
				if($caseSensitive=="on")
				{
					$caseSensitive = 'Case Sensitive';
				}	
				
				if($addTextarea=="on")
				{
					$addTextarea = 'Include Textarea';
				}					


				$CSV_array[] = array(''.$questionID.'', 'QUESTION',''. $qType.'',''. $questionTitle.'',''. $questionContent.'',''. $correctFeedback.'',''. $incorrectFeedback.'',''. $randomiseOptions.'', ''.$caseSensitive.'', ''.$addTextarea.'');
				
				$responseArrayTypes = array("singleResponse", "multiResponse", "multiBlanks", "shortTextResponse");
				
				if(in_array($qType, $responseArrayTypes) )
				{
					
					$responseOptions = array();
					
					if(isset($questionMeta['responseOptions']) )
					{
					
						// Unserialise and go through the array
						$responseOptions = unserialize($questionMeta['responseOptions'][0]);
					}
					

					
					foreach($responseOptions as $optionID => $optionMeta)
					{
						
						$optionValue = $optionMeta['optionValue'];
						$isCorrect = '';
						if(isset($optionMeta['isCorrect']) )
						{
							if($optionMeta['isCorrect']=="correct") 
							{
								$isCorrect = "correct";
							}
						}
						
						$CSV_array[] = array('','RESPONSE-OPTION',''.$optionValue.'',''.$isCorrect.'');						
					}
				}
			}
		}		

		return $CSV_array;				

	}
	
	
	
	public static function csv_export()
	{
		// Check for current user privileges 
		if(!current_user_can(get_option('min_quiz_access_level', 'manage_options'))){ return false; }
		
	
		
		// Check if we are in WP-Admin
		if( !is_admin() ){ return false; }
		// Nonce Check
		$nonce = isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : '';
		if ( ! wp_verify_nonce( $nonce, 'download_csv' ) ) {
			die( 'Security check error' );
		}
		
		$fileName = 'quizQuestionsExport.csv';
		

		$CSV_array = ekQuiz_export::generateExportFile();
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header('Content-Description: File Transfer');
		ob_end_clean();		 // Remove unwanted blank spaces / line breaks
		header("Content-type: text/csv");
		header("Content-Disposition: attachment; filename={$fileName}");
		header("Expires: 0");
		header("Pragma: public");
		
		$fh = @fopen( 'php://output', 'w' );
		
		foreach ($CSV_array as $fields) {
			fputcsv($fh, $fields);
		}				
		
		// Close the file
		fclose($fh);
		// Make sure nothing else is sent, our file is done
		die();
	}	
	
} //Close class
?>