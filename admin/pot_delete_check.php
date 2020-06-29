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


$potID = $_GET['potID'];
$showDeleteCheck = true;

$potName = get_the_title($potID);

echo '<div class="wrap">';


if(isset($_GET['action']))
{
	$action=$_GET['action'];

	switch ($action) {
		case "deleteConfirm":

			// Check the nonce before proceeding;
			$retrieved_nonce="";
			if(isset($_REQUEST['_wpnonce'])){$retrieved_nonce = $_REQUEST['_wpnonce'];}

			if (wp_verify_nonce($retrieved_nonce, 'potDeleteNonce' ) )
			{
                $showDeleteCheck = false;

                $args = array("potID" => $potID);
                $questionsInPot = ekQuiz_queries::getPotQuestions($args);

                $questionCount = count($questionsInPot);



                if($questionCount>=1)
                {
                    foreach ($questionsInPot as $questionMeta)
                    {
                        $questionID = $questionMeta->ID;

                        wp_delete_post($questionID);

                        // Also delete any data
                        ek_quiz_actions::deleteQuestionData($questionID);
                    }
                }

                // Delete the question pot
                wp_delete_post($potID);

                echo '<h1>This pot has been deleted</h1>';
                if($questionCount>=1)
                {
                    echo $questionCount.' question(s) have also been deleted<hr/>';
                }


                echo '<a href="edit.php?post_type=ek_pot" class="backIcon">Return to my pots</a>';


			}


			break;


	}

}

if($showDeleteCheck==true)
{

    ?>
    <h1>Are you sure you want to delete this question pot?</h1>
    <a href="edit.php?post_type=ek_pot" class="backIcon">Return to my pots</a>

    <?php
    echo '<h2>Pot Name : '.$potName.'</h2>';

    // get the question in this pot

    $args = array("potID" => $potID);
    $questionsInPot = ekQuiz_queries::getPotQuestions($args);



    $questionCount = count($questionsInPot);

    if($questionCount>=1)
    {
        echo '<div class="ek-feedback ek-feedback-warning">';
        echo 'You have <strong>'.$questionCount.'</strong> questions in this pot.<hr/>';
        echo 'Deleting this pot will also delete these questions and any scores associated with them!';
        echo '</div>';
    }




    $url = '?page=ek-pot-delete-check&potID='.$potID.'&action=deleteConfirm';
    $url = add_query_arg( '_wpnonce', wp_create_nonce( 'potDeleteNonce' ), $url );

    echo '<hr/>';
    echo 'Are you sure you want to delete this question pot?<br/><br/>';

    echo '<div>';
    echo '<a href="'.$url.'" class="button-primary">Yes, delete this pot</a> <a href="edit.php?post_type=ek_pot"class="button-secondary">Cancel</a>';
    echo '</div>';

}

echo '</div>';

?>
