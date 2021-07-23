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

$questionID = $_GET['questionID'];
$parentID = $_GET['potID'];
$potName = get_the_title($parentID);

if(!$questionID)
{
	echo 'No Question ID found';
	return;
}

$questionTitle = get_the_title($questionID);

$qType = get_post_meta($questionID, "qType", true);

$resultsType='score';
$thisClass = 'ek_'.$qType;

$qMeta = $thisClass::questionMeta();

if(isset($qMeta['resultsType']) )
{
   $resultsType =  $qMeta['resultsType'];
}



// Get all results for this question and add to array with user ID as the key
$userArray = ekQuiz_queries::getBlogUsers();

// Also get all answers for this single question
$questionResults = ekQuiz_queries::getQuestionResults($questionID);
$resultsLookupArray = array();
$totalAttempts = count($questionResults);
$totalAttemptsCorrect = 0;



echo '<h1>'.$questionTitle.'</h1>';
echo '<a href="edit.php?post_type=ek_question&potID='.$parentID.'">'.ekQuizDraw::backIcon().'Return to '.$potName.' questions</a>';

if($resultsType=="text")
{
	echo '<h2>Latest Student Entries</h2>';


	foreach($questionResults as $resultMeta)
	{
		$userID = $resultMeta['userID'];
		$dateSubmitted = $resultMeta['dateSubmitted'];
		$response = $resultMeta['userResponse'];

		$resultsLookupArray[$userID][] = array(
			"response" => $response,
			"dateSubmitted" => $dateSubmitted,
		);
	}



	// Set Some Defaults
	$lastDate = '-';

	echo '<table id="userTable">';
	echo '<thead><tr><th>Name</th><th>Username</th><th>Role</th><th>Response</th><th>Last Attempt Date</th></tr></thead>';


	// now go through all users and add to table, along with how many times they've done the question etc
	foreach ( $userArray as $userID => $userInfo )
	{
		$fullname = $userInfo['fullname'];
		$firstName = $userInfo['firstName'];
		$surname = $userInfo['surname'];
		$username = $userInfo['username'];
		$role = $userInfo['role'];

		$response = '';



		if(isset($resultsLookupArray[$userID] ) )
		{

			$userAttempts = $resultsLookupArray[$userID];

			$lastEntry = end($userAttempts);

			$lastDate = $lastEntry['dateSubmitted'];
			$response = $lastEntry['response'];
			$response =  wpautop(ekQuiz_utils::formatResponse($response));



		}

		echo '<tr>';
		echo '<td>'.$fullname.'</td>';
		echo '<td>'.$username.'</td>';
		echo '<td>'.$role.'</td>';
		echo '<td>'.$response.'</td>';
		echo '<td>'.$lastDate.'</td>';

		echo '</tr>';



	}


}

else
{


	echo '<h2>Results</h2>';



	foreach($questionResults as $resultMeta)
	{
		$userID = $resultMeta['userID'];
		$gotItCorrect = $resultMeta['gotItCorrect'];


		if($gotItCorrect==1)
		{
			$totalAttemptsCorrect++;
		}
		$dateSubmitted = $resultMeta['dateSubmitted'];
		$resultsLookupArray[$userID][] = array(
			"gotItCorrect" => $gotItCorrect,
			"dateSubmitted" => $dateSubmitted,
		);
	}

	// Get the number of unique users who have attempted this question
	$uniqueUsers = count($resultsLookupArray);

	echo 'There have been <b>'.$totalAttempts.'</b> attempts by <b>'.$uniqueUsers.'</b> people.<br/>';
	if($totalAttempts>=1)
	{
		$percentageGotCorrect = round (($totalAttemptsCorrect / $totalAttempts )*100, 0 );
		echo '<b>'.$percentageGotCorrect.'%</b> of attempts were correct.';
	}


	echo '<table id="userTable">';
	echo '<thead><tr><th>Name</th><th>Username</th><th>Role</th><th>Correct Attempts</th><th>Total Attempts</th><th>Last Attempt Date</th></tr></thead>';


	// now go through all users and add to table, along with how many times they've done the question etc
	foreach ( $userArray as $userID => $userInfo )
	{
		$fullname = $userInfo['fullname'];
		$firstName = $userInfo['firstName'];
		$surname = $userInfo['surname'];
		$username = $userInfo['username'];
		$role = $userInfo['role'];

		// get the user attempts
		$attemptCount = 0;
		$correctCount = "-";
		$lastDate = '-';
		if(isset($resultsLookupArray[$userID] ) )
		{
			$userAttempts = $resultsLookupArray[$userID];
			$attemptCount = count($userAttempts);


			$userAttempts = $resultsLookupArray[$userID];


			$correctCount = 0;
			foreach($userAttempts as $attemptMeta)
			{
				$lastDate = $attemptMeta['dateSubmitted'];
				$gotItCorrect = $attemptMeta['gotItCorrect'];
				if($gotItCorrect==1)
				{
					$correctCount++;
				}
			}
		}


		echo '<tr>';
		echo '<td>'.$fullname.'</td>';
		echo '<td>'.$username.'</td>';
		echo '<td>'.$role.'</td>';
		echo '<td>'.$correctCount.'</td>';
		echo '<td>'.$attemptCount.'</td>';
		echo '<td>'.$lastDate.'</td>';

		echo '</tr>';

	}

	echo '</table>';

}
?>

<script>
	jQuery(document).ready(function(){
		if (jQuery('#userTable').length>0)
		{
			jQuery('#userTable').dataTable({
				"bAutoWidth": true,
				"bJQueryUI": true,
				"sPaginationType": "full_numbers",
				"iDisplayLength": 50, // How many numbers by default per page
				"order": [[2, "desc"]]
			});
		}

	});
</script>
