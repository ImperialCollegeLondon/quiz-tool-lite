<?php
if ( ! defined( 'ABSPATH' ) )
{
	die();	// Exit if accessed directly
}
// Only let them view if admin
if(!current_user_can('delete_pages'))
{
	die();
}
$feedback = '';



function saveResponseOptionImport($optionArray, $questionID)
{
	// Count the number of array options
	$optionCount = count($optionArray);
	$next_key = $optionCount+1;


	//echo '<b>Add options for '.$questionID.'</b><br/>';
	//echo  '<pre>';
	//print_r($optionArray);
	//echo '</pre>';
	//echo 'Next Key = '.$next_key.'<br/>';



	update_post_meta( $questionID, 'autoIncrementOptionKeyID', $next_key );
	update_post_meta( $questionID, 'responseOptions', $optionArray );
}




// If form was submitted then sanitize the submitted values and update the settings.
if ( isset( $_GET['myAction'] ) )
{

	$myAction = $_GET['myAction'];
	switch ($myAction)
	{

		case "CSVUpload":


			// Check the nonce before proceeding;
			$retrieved_nonce="";
			$feedbackStr = '';
			$newPotCount=0;
			$newQuestionCount=0;
			$updatedPotCount=0;
			$updatedQuestionCount=0;
			if(isset($_REQUEST['_wpnonce'])){$retrieved_nonce = $_REQUEST['_wpnonce'];}

			if (wp_verify_nonce($retrieved_nonce, 'CSV_UploadNonce' ) )
			{
				$newFilename = dirname(__FILE__).'\questionImport.csv';

				if(isset($_FILES['csvFile']['tmp_name']))
				{

					move_uploaded_file($_FILES['csvFile']['tmp_name'], $newFilename);

					// Go through the CSV stuff
					ini_set('auto_detect_line_endings',1);
					$handle = fopen($newFilename, 'r');

					// Define the option loop
					$inOptionLoop = '';

					while (($data = fgetcsv($handle, 1000, ',')) !== FALSE)
					{
						$dataType = trim($data[1]);

						switch ($dataType)
						{
							case "POT":

								if($inOptionLoop==true)
								{

									saveResponseOptionImport($tempOptionArray, $parentQuestionID);


									// Reset the array
									$tempOptionArray = array();
								}


								$inOptionLoop=false; // Reset the option Loop
								$potID = trim($data[0]);
								$potName = trim($data[2]);


								//echo 'pot ID = '.$potID.'<br/>';

								// Check to see if the pot ID exists
								$checkPost = get_post_status($potID);
								$potAction = "new"; // By default create new pot

								if($checkPost)
								{
									//echo 'This Pot Exists - check for correct type<br/>';
									$postInfo = get_post($potID);
									if($postInfo->post_type =="ek_pot")
									{
										//echo 'This is a question pot so update<br/>';
										$potAction = "update";
									}
								}

								//echo 'potAction = '.$potAction.'<br/>';

								if($potAction=="new")
								{
									// Create new pot
									$my_post = array(
									  'post_title'		=> $potName,
									  'post_status'		=> 'publish',
									  'post_type'		=> "ek_pot"

									);

									// Insert the post into the database
									$parentPotID = wp_insert_post( $my_post );

									$newPotCount++;
								}
								else // Do an update
								{
									$my_pot = array(
										'ID'           => $potID,
										'post_title'   => $potName,
									);

									// Update the post into the database
									wp_update_post( $my_pot );
									$parentPotID = $potID;
									$updatedPotCount++;

								}



							break;



							case "QUESTION":

								if($inOptionLoop==true)
								{
									saveResponseOptionImport($tempOptionArray, $parentQuestionID);

									// Reset the array
									$tempOptionArray = array();
								}


								$inOptionLoop=false; // Reset the option Loop
								$questionID = trim($data[0]);
								$qType = trim($data[2]);
								$questionName = trim($data[3]);
								$questionContent = trim($data[4]);
								$correctFeedback = trim($data[5]);
								$incorrectFeedback = trim($data[6]);
								$randomiseOptions = trim($data[7]);
								$caseSensitive = trim($data[8]);
								$addTextarea = trim($data[9]);


								//echo 'randomiseOptions = '.$randomiseOptions.'<br/>';


								if($randomiseOptions=="randomise")
								{
									$randomiseOptions="on";
								}

								if($caseSensitive=="Case Sensitive")
								{
									$caseSensitive="on";
								}

								if($addTextarea=="Include Textarea")
								{
									$addTextarea="on";
								}


								// Check to see if the pot ID exists
								$checkPost = get_post_status($questionID);
								$questionAction = "new"; // By default create new pot

								if($checkPost)
								{
								//	echo 'This Pot Exists - check for correct type<br/>';
									$postInfo = get_post($questionID);
									if($postInfo->post_type =="ek_question")
									{
										//echo 'This is a question pot so update<br/>';
										$questionAction = "update";
									}
								}

								if($questionAction=="new")
								{
									//echo 'CREATE NEW QUETION.<br/>';
									// Create new question
									$my_question = array(
										'post_title'		=> $questionName,
										'post_content'		=> $questionContent,
										'post_status'		=> 'publish',
										'post_type'		=> "ek_question",
										'post_parent'		=> $parentPotID,

									);

									// Insert the post into the database
									$parentQuestionID = wp_insert_post( $my_question );

									$newQuestionCount++;
								}
								else // Do an update
								{

									//echo 'UPDATE QUETION.<br/>';
										$my_question = array(
										'ID'           => $questionID,
										'post_title'   => $questionName,
										'post_content'   => $questionContent,
										'post_parent'		=> $parentPotID,
									);

									// Update the post into the database
									wp_update_post( $my_question );
									$parentQuestionID = $questionID;
									$updatedQuestionCount++;
								}

								// Save the question meta
								update_post_meta( $parentQuestionID, 'correctFeedback', $correctFeedback );
								update_post_meta( $parentQuestionID, 'incorrectFeedback', $incorrectFeedback );
								update_post_meta( $parentQuestionID, 'randomiseOptions', $randomiseOptions );
								update_post_meta( $parentQuestionID, 'caseSensitive', $caseSensitive );
								update_post_meta( $parentQuestionID, 'addTextarea', $addTextarea );
								update_post_meta( $parentQuestionID, 'qType', $qType );



							break;

							case "RESPONSE-OPTION":
								$optionValue = trim($data[2]);
								$isCorrect = trim($data[3]);

								// If not in option loop then this is the start of the options
								// Create a temp array
								if($inOptionLoop==false)
								{
									$tempOptionArray = array();
								}

								$inOptionLoop=true;

								$tempOptionArray[] = array (
									"optionValue" => $optionValue,
									"isCorrect"		=> $isCorrect
								);



							break;





						}

					} // End CSV loop

				} // End if file type is CSV
				// Now delete the temp file
				unlink ($newFilename);


				$feedback='';

				if($newPotCount>=1)
				{
					$feedback.=$newPotCount.' new pots created<br/>';
				}
				if($updatedPotCount>=1)
				{
					$feedback.=$updatedPotCount.' pots updated<br/>';
				}
				if($newQuestionCount>=1)
				{
					$feedback.=$newQuestionCount.' new questions created<br/>';
				}
				if($updatedQuestionCount>=1)
				{
					$feedback.=$updatedQuestionCount.' questions updated<br/>';
				}

			}

		break;

		} // End of switch


} // End is action








echo '<h1>Import / Export</h1>';

if($feedback)
{
	echo '<div class="notice notice-success is-dismissible">'.$feedback.'</div>';
}


?>

<div class="ek-content-box">
<h2>Import a quiz CSV file</h2>
<form name="csvUploadForm" action="?post_type=ek_pot&page=ek-quiz-export&myAction=CSVUpload"  method="post" enctype="multipart/form-data">

<input type="file" name="csvFile" size="20"/><br/>
<input type="submit" value="Upload" name="submit" class="button-primary" />
<?php
// Add nonce
wp_nonce_field('CSV_UploadNonce');
?>

</form>
</div>


<?php
echo '<div class="ek-content-box">';
echo '<h2>Export your question pots and questions</h2>';
echo '<a class="button-primary" href="?post_type=ek_pot&page=ek-quiz-export&myAction=exportQuestionsCSV&_wpnonce='. wp_create_nonce( 'download_csv' ).'">Export</a>';
echo '</div>';
?>
