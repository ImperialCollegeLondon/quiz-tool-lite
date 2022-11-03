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
?>
<?php
$quizID="";
if(isset($_GET['quizID']))
{
	$quizID = $_GET['quizID'];
	$view = isset($_GET['view']) ? $_GET['view'] : '';

	$args = [];
	if($view=="students")
	{
		$args['students_only'] = true;
	}


	$quizName = get_the_title($quizID);
	echo '<h1>'.$quizName.' : Results</h1>';
	echo '<div class="block">';
	echo '<a href="edit.php?post_type=ek_quiz"><i class="fas fa-chevron-circle-left"></i> Pick a different quiz</a>';
	echo '</div>';

	echo '<div class="box is-size-4">';

	//echo '<div class="columns is-desktop">';
	//echo '<div class="column">';
	//echo '<a href="?page=ek-quiz-results&quizID='.$quizID.'&view='.$view.'&myAction=exportQuizResultsCSV" class="button is-primary">Download this data</a>';
	//echo '</div>';

//	echo '<div class="column aR is-size-4">';
	if($view=="")
	{
		echo 'Viewing all users';
	}
	else
	{
		echo '<a href="?page=ek-quiz-results&quizID='.$quizID.'">View all users</a>';
	}

	echo ' | ';


	if($view=="students")
	{
		echo 'Viewing only students';
	}
	else
	{
		echo '<a href="?page=ek-quiz-results&quizID='.$quizID.'&view=students">View only students</a>';
	}

	//echo '</div>';
	echo '</div>';
	echo '<div class="box">';

	echo ekQuizDraw::drawUserResults($quizID, $args);
	echo '</div>';
}



?>
<script>
jQuery(document).ready( function () {

	var table = jQuery('#userTable').DataTable( {
		"bAutoWidth": true,
		"bJQueryUI": true,
		"sPaginationType": "full_numbers",
		"iDisplayLength": 50, // How many numbers by default per page
		"order": [[0, "asc"]],
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
