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
<h1>Question Breakdown</h1>
<?php

$back_button = array(
    "url" => "edit.php?post_type=ek_quiz",
    "value" => "Back to quizzes",
);
$back_button = \icl_network\draw::back_button($back_button);
echo $back_button;
$quiz_id = $_GET['quiz-id'];




$this_blog_id = get_current_blog_id();
echo ekQuizDraw::option_count_breakdown($this_blog_id, $quiz_id);

?>
