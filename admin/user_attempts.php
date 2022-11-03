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


    $locale = get_locale();


$userID = $_GET['userID'];
$quizID = $_GET['quizID'];

echo '<div class="content box">';
echo '<h1>User Attempts</h1>';
	echo '<a href="edit.php?post_type=ek_quiz">'.ekQuizDraw::backIcon().'Back to Quizzes</a>';
echo '<table id="attemptsTable" class="table is-fullwidth">';
echo '<thead><tr><th>Score</th><th>Time Taken(HIDDEN)</th><th>Time Taken</th><th>Date Started(HIDDEN)</th><th>Date Started</th><th>Date Finished</th><th>Results</th><th></th></tr></thead>';

$ekQuiz_queries = new ekQuiz_queries();
$quizAttempts = $ekQuiz_queries->getUserAttempts($quizID, $userID);

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
	echo '<td><a href="'.admin_url("?my-action=quiz-attempt-delete&attempt-id=$attemptID&user-id=$userID&quiz-id=$quizID").'">Delete Attempt</a></td>';
	echo '</tr>';
}
echo '</table>';
echo '</div>';
?>

<script>
jQuery(document).ready( function () {

	var table = jQuery('#attemptsTable').DataTable( {
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
		buttons: [
		{
			extend: 'copy',
			text: '<span class="icon"><i class="fas fa-copy"></i></span><span>Copy to clipboard</span>',
			className: 'button',

		},
		{
			extend: 'excel',
			text: '<span class="icon"><i class="fas fa-file-excel"></i></span><span>Download Excel</span>',
			className: 'button',

		},
	]
	} );

	// Insert at the top left of the table
	table.buttons().container()
		.appendTo( jQuery('div.column.is-half', table.table().container()).eq(0) );

} );




</script>
