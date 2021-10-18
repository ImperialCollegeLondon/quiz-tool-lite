<?php



class ek_quiz_actions
{
	function saveUserResponse($questionID, $userResponse, $gotItCorrect)
	{
		global $wpdb;
		$userID = get_current_user_id();
		$currentDate  =  date('Y-m-d H:i:s'); // The Date
		global $userResponsesTable;
		$feedback = $wpdb->query( $wpdb->prepare(
		"INSERT INTO ".$userResponsesTable." (questionID, userID, userResponse, gotItCorrect, dateSubmitted) VALUES ( %d, %d, %s,%d, %s )",
		array(
			$questionID,
			$userID,
			$userResponse,
			$gotItCorrect,
			$currentDate
			)
		));
	}

    public static function deleteQuestionData($questionID)
    {
        global $wpdb;
		global $userResponsesTable;

        $wpdb->delete($userResponsesTable, array('questionID' => $questionID), array('%d'));
    }

	public static function deleteAllUserData()
	{
		global $wpdb;
		global $userResponsesTable;
		global $quizAttemptsTable;

		$delete = $wpdb->query("TRUNCATE TABLE $userResponsesTable");
		$delete = $wpdb->query("TRUNCATE TABLE $quizAttemptsTable");
	}

	public static function quiz_attempt_delete($attempt_id)
	{
		global $wpdb;
		global $quizAttemptsTable;

		$wpdb->delete($quizAttemptsTable, array('attemptID' => $attempt_id), array('%d'));
	}


	/**
	* Clone a quiz including all meta data etc
	* @param int The quiz ID for the CPT
	*/
	public static function quiz_clone($quiz_id)
	{

		// Get the quiz info
		$new_quiz_name = get_the_title($quiz_id).' (Copy)';

		// ADd the new post
		// Create post object
		$my_post = array(
		    'post_title'    => $new_quiz_name,
		    'post_status'   => 'publish',
		    'post_type'   => 'ek_quiz',
		);

		// Insert the post into the database
		$new_post_id = wp_insert_post( $my_post);

		// Get all the quiz meta
		$quiz_meta = get_post_meta($quiz_id);

		// Array of all the quiz meta that we want to copy
		$meta_to_ignore = array(
			"_edit_lock",
			"_edit_last",
		);

		$meta_as_array = array(
			"myQuestionPotListValues",
		);

		foreach ($quiz_meta as $meta_key => $meta_value)
		{
			// If the post meta is NOT in the array to ignore then add it
			if(!in_array($meta_key, $meta_to_ignore) )
			{

				$meta_value = $meta_value[0];
				// If its an array deseiralize and then re add it so it gets serialised and stored properly
				if(in_array($meta_key, $meta_as_array) )
				{
					$meta_value = unserialize($meta_value);
				}

				add_post_meta( $new_post_id, $meta_key, $meta_value  );
			}

		}


	}
}


?>
