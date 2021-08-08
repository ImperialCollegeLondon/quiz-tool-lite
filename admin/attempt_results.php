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


$attemptID = $_GET['attemptID'];
global $ek_quiz_database;
echo '<h1>Attempts Results</h1>';
/* Get the quiz ID based on the attemptID */
$attemptInfo = $ek_quiz_database->getAttemptInfo( $attemptID );
$quizID = $attemptInfo['quizID'];
$userID = $attemptInfo['userID'];

$args = array(
"attemptID"	=>$attemptID,
"currentPage"	=> "answers",
"quizID"	=> $quizID,
"quizReportScreen"	=> true,
);


echo '<a href="options.php?page=ek-user-attempts&quizID='.$quizID.'&userID='.$userID.'">'.ekQuizDraw::backIcon().'Back to all user attempts</a>';
echo ekQuizDraw::drawQuizPage($args);
?>
