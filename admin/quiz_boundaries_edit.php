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
$boundaryID = $_GET['boundaryID'];
$quizID = $_GET['quizID'];
// get the grade boundaries as array
$myBoundaries = get_post_meta($quizID, 'gradeBoundaries', true);

// Define some vars

$minGrade = '';
$feedback  = '';

// Set some vars
if($boundaryID<>"new")
{
	$thisBoundaryInfo = $myBoundaries[$boundaryID];
	$minGrade= $thisBoundaryInfo['minGrade'];
	$maxGrade= $thisBoundaryInfo['maxGrade'];
	$feedback = $thisBoundaryInfo['feedback'];
}
$minAllowedScore = $_GET['min'];
$maxAllowedScore = $_GET['max'];
?>
<h2>Edit Boundary</h2>
<a href="options.php?page=ek-quiz-boundaries&quizID=<?php echo $quizID; ?>" class="backIcon">Return to my boundaries</a><br/><br/>
<form action="options.php?page=ek-quiz-boundaries&quizID=<?php echo $quizID ?>&action=boundaryEdit" method="post">
<?php
$thisMinAllowedScore = $minAllowedScore;
echo '<select id="minGrade" name="minGrade">';
while($thisMinAllowedScore<=$maxAllowedScore)
{
	echo '<option value="'.$thisMinAllowedScore.'"';
	if($minGrade==$thisMinAllowedScore){echo ' selected';}
	echo '>'.$thisMinAllowedScore;
	echo '</option>';	
	$thisMinAllowedScore++;
}
echo '</select>';
echo '<label for="minGrade">Minimum Score</label>';
echo '<hr/>';
$thisMinAllowedScore = $minAllowedScore;
if($boundaryID=="new")
{
	$maxGrade = $maxAllowedScore; // set the default max allowed to the max score
}
echo '<select id="maxGrade" name="maxGrade">';
while($thisMinAllowedScore<=$maxAllowedScore)
{
	echo '<option value="'.$thisMinAllowedScore.'"';
	if($maxGrade==$thisMinAllowedScore){echo ' selected';}
	echo '>'.$thisMinAllowedScore;
	echo '</option>';	
	$thisMinAllowedScore++;
}
echo '</select>';
echo '<label for="maxGrade">Maximum Score</label>';
echo '<hr/>';
echo '<b>Feedback to display</b><br/>';
wp_editor($feedback, 'feedback', '', true);
echo '<input type="hidden" value="'.$quizID.'" name="quizID">';
echo '<input type="hidden" value="'.$boundaryID.'" name="boundaryID">';
// Also add nonce field
wp_nonce_field('gradeBoundaryNonce');    
?>
<input type="submit" value="Update" class="button-primary"/>
</form>