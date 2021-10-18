<?php

qtl_check_for_admin_actions::init();
class qtl_check_for_admin_actions
{


/*	---------------------------
	PRIMARY HOOKS INTO WP
	--------------------------- */
	static function init ()
	{
		add_action('template_redirect ', __NAMESPACE__.qtl_check_for_admin_actions::check_for_admin_actions() ); // This must be called AFTER the security hook

		//add_action( 'admin_post_quiz_clone', __NAMESPACE__.qtl_check_for_admin_actions::quiz_clone() );


	}


	public static function check_for_admin_actions()
	{


		if(isset($_GET['my-action']) )
		{


			$action = $_GET['my-action'];


			switch ($action)
			{


				case "quiz-attempt-delete":

                    $attempt_id = $_GET['attempt-id'];
                    $user_id = $_GET['user-id'];
                    $quiz_id = $_GET['quiz-id'];
                    ek_quiz_actions::quiz_attempt_delete($attempt_id);

                    $redirect_url = admin_url('/options.php?page=ek-user-attempts&quizID='.$quiz_id.'&userID='.$user_id);

                     header('Location: '.$redirect_url); // Why is wp_redirect not working?
                //    wp_redirect($redirect_url);
                    exit();
                break;


			}

		}
	}


}


add_action( 'admin_post_quiz_clone', 'quiz_clone' );
function quiz_clone()
{
	$quiz_id = $_GET['quiz-id'];
	ek_quiz_actions::quiz_clone($quiz_id);
	$redirect_url = admin_url('/edit.php?post_type=ek_quiz');
    wp_redirect($redirect_url);
	exit();
}

?>
