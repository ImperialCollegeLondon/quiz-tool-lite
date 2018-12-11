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
	if (wp_verify_nonce($retrieved_nonce, 'submitForm' ) )
	{

	
	$myAction = $_GET['action'];
	switch ($myAction)
	{
	
		case "updateSettings":
		
			$defaultCorrectFeedback = $_POST['defaultCorrectFeedback'];
			$defaultIncorrectFeedback = $_POST['defaultIncorrectFeedback'];
			
			// Update the defaults
			update_option('ek-quiz-correct-feedback', $defaultCorrectFeedback);
			update_option('ek-quiz-incorrect-feedback', $defaultIncorrectFeedback);
			update_option('ek-quiz-showCorrectAnswer', $showCorrectAnswer);
			
			
			
			$min_access_level = $_POST['min_access_level'];
			update_option('min_quiz_access_level', $min_access_level);
			
			
			echo '<div class="notice notice-success is-dismissible"><p>Settings Updated</p></div>';
			
			
		} // End of nonce check
	}// End if grouopsUpload case	
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
if($minAccessLevel==""){$minAccessLevel="manage_options";}

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
 
</div>
<div class="ek-content-box">
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
</div>


<input type="submit" value="Update" name="submit" class="button-primary" />
<?php
// Add nonce
wp_nonce_field('submitForm');    
?>

</form>