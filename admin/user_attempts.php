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


    $locale = get_locale();
    

$userID = $_GET['userID'];
$quizID = $_GET['quizID'];
echo '<h1>User Attempts</h1>';
	echo '<a href="edit.php?post_type=ek_quiz">'.ekQuizDraw::backIcon().'Back to Quizzes</a>';
echo '<table id="attemptsTable">';
echo '<thead><tr><th>Score</th><th>Time Taken(HIDDEN)</th><th>Time Taken</th><th>Date Started(HIDDEN)</th><th>Date Started</th><th>Date Finished</th><th>Results</th></tr></thead>';

$ekQuiz_queries = new ekQuiz_queries();
$quizAttempts = $ekQuiz_queries->getUserAttempts($quizID, $userID);
echo '<pre>';
//print_r($quizAttempts);
echo '</pre>';
// Array of WP_User objects.
foreach ( $quizAttempts as $attemptInfo )
{
	$attemptID =  $attemptInfo['attemptID'];
	$score =  $attemptInfo['score'];
	$dateStarted =  $attemptInfo['dateStarted'];
	$dateFinished =  $attemptInfo['dateFinished'];
	if($score==""){$score='-';}else{$score = $score.'%';}
	$dateStartedFormatted = date('jS F Y, g:ia', strtotime($dateStarted));
	if($dateFinished=="0000-00-00 00:00:00") // Set end date to - if not finished
	{
		$dateFinishedFormat='-';}else{	$dateFinishedFormat = date('jS F Y, g:ia', strtotime($dateFinished));
	}


	// Get the time taken if finished
	$timeTaken = '-';
	$timeTakenSeconds = '-';
	if($dateFinishedFormat<>"-")
	{
		$timeTakenInfo = ekQuiz_utils::dateDiff($dateStarted, $dateFinished);
		$timeTaken = $timeTakenInfo['str'];
		$timeTakenSeconds = $timeTakenInfo['seconds'];
	}

	echo '<tr>';
	echo '<td><b>'.$score.'</b></td>';
	echo '<td>'.$timeTakenSeconds.'</td>';
	echo '<td>'.$timeTaken.'</td>';
	echo '<td>'.$dateStarted.'</td>';
	echo '<td>'.$dateStartedFormatted.'</td>';
	//echo '<td>'.$dateFinished.'</td>';
	echo '<td>'.$dateFinishedFormat.'</td>';
	echo '<td><a href="?page=ek-user-attempt&attemptID='.$attemptID.'">View Results</a></td>';
	echo '</tr>';
}
echo '</table>';
?>
<script>
	jQuery(document).ready(function(){
		if (jQuery('#attemptsTable').length>0)
		{
			jQuery('#attemptsTable').dataTable({
				"searching": false,
				"bAutoWidth": true,
				"bJQueryUI": true,
				"sPaginationType": "full_numbers",
				"iDisplayLength": 50, // How many numbers by default per page
				"order": [[ 3, "desc" ]], // Show in date order
				"columnDefs": [
					{// Hide the date finished mysql date
						"targets": [ 1, 3 ],
						"visible": false,
						"searchable": false
					},
					{  // Order Time Taken by seconds (hidden column)
						"orderData":[ 1 ],
						"targets": [ 2 ] ,
					},// Order date started by mysqldate (hidden column)
					{
						"orderData":[ 3 ],
						"targets": [ 4 ] ,
					},
					{
						"targets":[ 5, 6 ],
						"orderable": false,
					},

				],


			});
		}

	});
</script>
