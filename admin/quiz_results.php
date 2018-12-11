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
?>
<?php
$quizID="";
if(isset($_GET['quizID']))
{
	$quizID = $_GET['quizID'];
	
	
	$quizName = get_the_title($quizID);
	echo '<h2>'.$quizName.' : Results</h2>';
	echo '<a href="edit.php?post_type=ek_quiz"><i class="fas fa-chevron-circle-left"></i> Pick a different quiz</a>';
	echo '<hr/><a href="?page=ek-quiz-results&quizID='.$quizID.'&myAction=exportQuizResultsCSV" class="button-primary">Download as CSV</a>';
	
	echo ekQuizDraw::drawUserResults($quizID);
}


	
?>