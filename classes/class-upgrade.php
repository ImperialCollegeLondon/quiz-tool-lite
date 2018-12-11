<?php
$ekQuiz_upgrade = new ekQuiz_upgrade();
class ekQuiz_upgrade
{
	
	//~~~~~
	function __construct ()
	{
		$this->addWPActions();
	}	
	
	
/*	---------------------------
	PRIMARY HOOKS INTO WP 
	--------------------------- */	
	function addWPActions ()
	{
		//Admin Menu
		
		update_option( "qtl-v3upgrade", false );  // uncomment to run again			
		//add_action( 'init',  array( $this, 'checkUpgrade' ) );
	}
	
	
/*	---------------------------
	ADMIN-SIDE MENU / SCRIPTS 
	--------------------------- */
	function checkUpgrade ()
	{
		
		$liveUpdate = false; // Set to true for the real thing
		

		$hasAlreadyRun = get_option('qtl-v3upgrade');
		
		$report= '<h2>CHECK FOR UPGRADE</h2>';
		$report.= 'Step 1 : Check for previous version<br/>';
		// Check if an option exists from the previous version_compare
		if(get_option('qtl-minimum-editor'))
		{			
			$report.= 'Previous Version Found<hr/>';
	
			// Now see if the upgrade has been successful
			if(get_option('qtl-v3upgrade')<>1)
			{
				
				//echo '<h1>RUN</h1>';
				
				global $wpdb;
				
				// Create the lookup array for legacy items
				$legacyLookupArray =array();
				
				
				// This does not exist so DO THE UPGRADE
				$report.= '<h2>Do the upgrade</h2>';
				
				
				// Copy question pots
				$report.= '<h3>Question Pots</h3>';
				$table_name = $wpdb->prefix . "AI_Quiz_tblQuestionPots";		
				
				$SQL='Select * FROM '.$table_name.' ORDER by potID ASC';
				$potRS = $wpdb->get_results( $SQL, ARRAY_A );
				
				
				
				foreach($potRS as $potInfo)
				{
					$potID = $potInfo['potID'];
					$potName = $potInfo['potName'];			
					$report.= $potName.' ('.$potID.')<hr/>';
					
					
					
					
					// Create post object
					$my_post = array(
					  'post_title'		=> wp_strip_all_tags( $potName ),
					  'post_status'		=> 'publish',
					  'post_type'		=> 'ek_pot',

					  );
					 
					// Insert the post into the database
					
					
					if($liveUpdate==true)
					{
						$newPotID = wp_insert_post( $my_post );
						$report.= 'New ID = '.$newPotID.'</br>';
						
						$legacyLookupArray[] = array(
							"itemType"		=> "pot",
							"originalID"	=> $potID,
							"newID"			=> $newPotID,
						);
						
					}
					
					// Now get all the questions for the old pot and add them to the new pot
					$table_name = $wpdb->prefix . "AI_Quiz_tblQuestions";		
		
					
					$SQL='Select * FROM '.$table_name.' WHERE potID='.$potID;	
					$questionRS = $wpdb->get_results($SQL, ARRAY_A);
					
					
					echo '<pre>';
					//print_r($questionRS);
					echo '</pre>';
					foreach($questionRS as $questionInfo)
					{
						$questionID = $questionInfo['questionID'];
						$question = $questionInfo['question'];
						$qType = $questionInfo['qType'];
						$correctFeedback = $questionInfo['correctFeedback'];
						$incorrectFeedback = $questionInfo['incorrectFeedback'];
						$optionOrderType = $questionInfo['optionOrderType'];
						
						// COnvert qTypes
						switch ($qType)
						{
							case "radio":
								$newQtype = 'singleResponse';
							break;
							
							case "check":
								$newQtype = 'multiResponse';
							break;
							
							case "text":
								$newQtype = 'shortTextResponse';
							break;
							
							case "blank":
								$newQtype = 'multiBlanks';
							break;							
						}
						
						
						$report.= '<h2>Question : '.$question.'</h2>';
						$report.= 'qType = '.$qType.'<br/>';						
						
						
						// Get any response options
						$table_name = $wpdb->prefix . "AI_Quiz_tblResponseOptions";		
						$SQL='Select * FROM '.$table_name.' WHERE questionID='.$questionID.' ORDER by optionOrder ASC';
						$responseOptionRS = $wpdb->get_results( $SQL, ARRAY_A );
						echo '<pre>';
						//print_r($responseOptionRS);
						echo '</pre>';
						$report.= '<h3>Response Options</h3>';
						
						// Create option array
						$options_data= array();
						
						
						if($newQtype=="multiBlanks")
						{
							foreach($responseOptionRS as $optionInfo)
							{
								//$isCorrect = $optionInfo['isCorrect'];
								$optionValue = $optionInfo['optionValue'];							

								
								//unseralise this database
								$thisOptionArray = unserialize($optionValue);
								
								echo  '<pre>';
								//print_r($thisOptionArray);
								echo '</pre>';								
								
								// Is this array key an answer?
								foreach($thisOptionArray as $key=>$value)
								{
									if(stristr($key,'answers')!==FALSE)
									{

										
	
										$possibleAnswers = $value[0];
										// Now add the possible answers array to the mast option array
										$options_data[] = 
										array(
											"optionValue" => $possibleAnswers,
										);
									}
								
								}

	
									
									
									//$report.='thisInfo='.$thisInfo[0].'<br/>';
								
								
								
								//$report.= $optionValue.' ('.$isCorrect.')<br/>';

							}								
							
						}
						else
						{
						
							foreach($responseOptionRS as $optionInfo)
							{
								$isCorrect = $optionInfo['isCorrect'];
								$optionValue = $optionInfo['optionValue'];
								$report.= $optionValue.' ('.$isCorrect.')<br/>';
								
								$options_data[] = array (
									"optionValue" => $optionValue,
									"isCorrect" => $isCorrect
								);
							}
						
						
						}
						
						
						
						
						
						

					
						$randomiseOptions = "";
						if($optionOrderType<>"ordered")
						{
							$randomiseOptions="on";
						}

						$totalOptions = count($options_data);
						$next_key = $totalOptions+1;
						$report.= 'Total Options = '.$totalOptions.'<br/>';
						$report.= 'next key = '.$next_key.'</br>';
						$report.= 'randomiseOptions = '.$randomiseOptions.'</br>';
						
						
						
						
									echo  '<pre>';
								print_r($options_data);
								echo '</pre>';						
						
						
						// Update the question meta
						
						if($liveUpdate==true)
						{
							
							// Create post object
							$my_post = array(
							  'post_title'		=> "temp title",
							  'post_status'		=> 'publish',
							  'post_type'		=> 'ek_question',
							  'post_content'	=> $question,
							  'post_parent'		=> $newPotID,

							 );
							 
							$newQuestionID = wp_insert_post( $my_post );
							
							$legacyLookupArray[] = array(
								"itemType"		=> "question",
								"originalID"	=> $questionID,
								"newID"			=> $newQuestionID,
							);							

							 
							// Insert the question into the database
							$title = substr(preg_replace("/[^A-Za-z0-9 ]/", ' ', strip_tags($question) ), 0, 80);
							$title=$title.'...';
							
							$where = array( 'ID' => $newQuestionID );
							$wpdb->update( $wpdb->posts, array( 'post_title' => $title ), $where );
				
						
							update_post_meta( $newQuestionID, 'qType', $newQtype );        
							update_post_meta( $newQuestionID, 'responseOptions', $options_data );        
							update_post_meta( $newQuestionID, 'autoIncrementOptionKeyID', $next_key );	
							update_post_meta( $newQuestionID, 'randomiseOptions', $randomiseOptions );
							update_post_meta( $newQuestionID, 'correctFeedback', $correctFeedback );		
							update_post_meta( $newQuestionID, 'incorrectFeedback', $incorrectFeedback );



						}
					}

					echo '<hr/>';					
											
				}
				
				
				
				if($liveUpdate==true)
				{				
					global $legacyLookupTable;
					foreach($legacyLookupArray as $lookupInfo)
					{
						$itemType = $lookupInfo["itemType"];
						$originalID = $lookupInfo["originalID"];
						$newID = $lookupInfo["newID"];
						
						$report.= 'Item Type = '.$itemType.'<br/>';
						$report.= 'Old ID = '.$originalID.'<br/>';
						$report.= 'New ID = '.$newID.'<hr/>';
						
						$wpdb->query( $wpdb->prepare(
						"INSERT INTO ".$legacyLookupTable." (itemType, originalID, newID) VALUES ( %s, %d, %s )",
						array(
							$itemType,
							$originalID,
							$newID
							)
						));
					}
					
						
					// Update the site meta that it has been upgraded
					$report.='<h2>update Site option</h2>';
					update_option( "qtl-v3upgrade", 1 ); 						
					
				}

			
	
				

			}
			else
			{
				$report.='Already Updated';
				//update_option( "qtl-v3upgrade", false ); 		

			}
			
		}
		
		echo $report;
		die();

	
	}
	
	
	
	
	
} //Close class
?>