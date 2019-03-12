<?php

	if ( ! defined( 'ABSPATH' ) ) 
	{
		die();	// Exit if accessed directly
	}
	
	// Only let them view if admin		
	if(!current_user_can(get_option('min_quiz_access_level', 'manage_options')))
	{
		die();
	}	
	
	
	wp_enqueue_style( 'ek-quiz-css' );		
	
?>
<?php
echo '<h1>Quiz Settings</h1>';


if ( isset( $_GET['action'] ) )
{
	// Check the nonce before proceeding;	
	$retrieved_nonce="";
	if(isset($_REQUEST['_wpnonce'])){$retrieved_nonce = $_REQUEST['_wpnonce'];}
	
	$myAction = $_GET['action'];
	switch ($myAction)
	{
	
		case "updateSettings":
		
			if (wp_verify_nonce($retrieved_nonce, 'submitForm' ) )
			{		
				$defaultCorrectFeedback = $_POST['defaultCorrectFeedback'];
				$defaultIncorrectFeedback = $_POST['defaultIncorrectFeedback'];
				$showCorrectAnswer = '';
				if(isset($_POST['showCorrectAnswer']) )
				{
					$showCorrectAnswer = $_POST['showCorrectAnswer'];
				}
				
				// Update the defaults
				update_option('ek-quiz-correct-feedback', $defaultCorrectFeedback);
				update_option('ek-quiz-incorrect-feedback', $defaultIncorrectFeedback);
				update_option('ek-quiz-showCorrectAnswer', $showCorrectAnswer);
				
				
				$min_access_level = $_POST['min_access_level'];
				update_option('min_quiz_access_level', $min_access_level);

				$updateShowCorrectForAll = '';
				if(isset($_POST['updateShowCorrectForAll']) )
				{
					// Get all question types
					$potRS = ekQuiz_queries::getPots();
					foreach ($potRS as $potInfo)
					{
						$potID = $potInfo->ID;
						$args = array("potID" => $potID);
						$questionsRS = ekQuiz_queries::getPotQuestions($args);
						
						foreach($questionsRS as $questionInfo)
						{
							$questionID = $questionInfo->ID;						
							update_post_meta( $questionID, 'showCorrectAnswer', $showCorrectAnswer );		
							
						}
					}
				}			
				
				
				
				echo '<div class="notice notice-success is-dismissible"><p>Settings Updated</p></div>';
			}
			
			
			
		break;
		
		case "deleteAdminData":
		
			if (wp_verify_nonce($retrieved_nonce, 'deleteAdminDataNonce' ) )
			{
				
				// Get editors and admin users
				
				
				
				
				$deleteUsersArray = array();
				
				// get the admins
				$adminUsers = get_users( 'role=administrator' );				
				foreach ($adminUsers as $userMeta)
				{
					$userID = $userMeta-> ID;
					$deleteUsersArray[] = $userID;
				}
				
				// Get the editors
				$editorUsers = get_users( 'role=editor' );				
				foreach ($editorUsers as $userMeta)
				{
					$userID = $userMeta-> ID;
					$deleteUsersArray[] = $userID;
				}			
				global $wpdb;
				global $userResponsesTable;
				global $quizAttemptsTable;
								
				foreach ($deleteUsersArray as $userDeleteID)
				{
					$wpdb->delete( $userResponsesTable, array( 'userID' => $userDeleteID ) );					
					$wpdb->delete( $quizAttemptsTable, array( 'userID' => $userDeleteID ) );					
				}
				
				echo '<div class="notice notice-success is-dismissible"><p>Data Deleted</p></div>';
				
			}
		
		
		break;
			
			
	} // End of switch
		

} // End is action




// Get the defaults
$defaultFeedback  = ekQuiz_utils::getDefaultQuestionFeedback();


$showCorrectAnswer  = ekQuiz_utils::getDefaultShowCorrectAnswer();

$showCorrectAnswerChecked='';
if($showCorrectAnswer=="on")
{
	$showCorrectAnswerChecked = "checked";
}




$minAccessLevel  = get_option('min_quiz_access_level');
if($minAccessLevel==""){$minAccessLevel="edit_pages";}

?>





<form name="settingsForm" action="edit.php?post_type=ek_pot&page=ek-quiz-settings&action=updateSettings"  method="post" enctype="multipart/form-data">


<div class="ek-content-box">
<h2>Default Feedback</h2>
<label for="defaultCorrectFeedback">Default Correct Feedback</label><br/>
<input id="defaultCorrectFeedback" name="defaultCorrectFeedback" value="<?php echo $defaultFeedback['correctFeedback']; ?>">
<br/>

<label for="defaultIncorrectFeedback">Default Incorrect Feedback</label><br/>
<input id="defaultIncorrectFeedback" name="defaultIncorrectFeedback" value="<?php echo $defaultFeedback['incorrectFeedback']; ?>">


<hr/>

<h2>New Question Default Behaviour</h2>
<label for="showCorrectAnswer">
<?php
echo '<input type="checkbox" id="showCorrectAnswer" name="showCorrectAnswer" '.$showCorrectAnswerChecked.'>';
echo 'Show correct answers and feedback on submit';
?>
</label>


<br/><label for="updateShowCorrectForAll">
<?php
echo '<input type="checkbox" id="updateShowCorrectForAll" name="updateShowCorrectForAll">';
echo 'Update all existing questions to this setting (cannot be undone!)';
?>
</label>


<h2>Minimum level required to edit / view questions:</h2>

<?php
$levelArray = array(
array("manage_options", "Administrators"),
array("edit_pages", "Editors"),
array("edit_posts", "Authors"),
);



foreach ($levelArray as $levelMeta)
{
	$thisValue = $levelMeta[0];
	$thisText = $levelMeta[1];
	echo '<label for="'.$thisValue.'">';
	echo '<input type="radio" name="min_access_level" id="'.$thisValue.'" value="'.$thisValue.'"';
	if($minAccessLevel==$thisValue){echo ' checked';}
	echo '>';
	echo $thisText.' ('.$thisValue.')</label><br/>';
}



?>
<br/>
<input type="submit" value="Update" name="submit" class="button-primary" />
<?php
// Add nonce
wp_nonce_field('submitForm');    
?>
</div>




</form>


<div class="ek-content-box">
<h2>Clear up data</h2>
Remove question data and quiz data submitted by <strong>editors</strong> and <strong>admins</strong><br/>
<a class="button-secondary" id="deleteAdminDataCheck">Remove Data</a>
<div id="confirmAdminDataDelete" style="display:none;">

<form action="edit.php?post_type=ek_pot&page=ek-quiz-settings&action=deleteAdminData" method="post">
<br/>Are you sure you want to delete this data? This cannot be undone!
<br/>
<input type="submit" class="button-primary" value="Yes delete this data">
<?php
wp_nonce_field('deleteAdminDataNonce');    

?>
</form>
</div>
</div>

<script>
jQuery( document ).ready(function() {   
	jQuery( "#deleteAdminDataCheck" ).click(function() {
		jQuery( "#confirmAdminDataDelete" ).toggle( "fast");
	});    
});
</script>