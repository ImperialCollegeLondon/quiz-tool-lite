<?php
class ekQuiz_queries
{

	// Get an array of blog users and their role
	public static function getBlogUsers($args=array())
	{

		$userArray = array();
		$blogusers = get_users();

		// Array of WP_User objects.
		foreach ( $blogusers as $userInfo )
		{
			$userID = $userInfo->ID;
			$fullname = esc_html( $userInfo->display_name );
			$firstName= esc_html( $userInfo->first_name );
			$surname= esc_html( $userInfo->last_name );
			$username = $userInfo->user_login;
			$roles = $userInfo->roles;
			if($roles)
			{
				$userlevel = $roles[0];
			}
			else
			{
				$userlevel = "";
			}

			$userArray[$userID] = array
			(
				"fullname"	=> esc_html( $userInfo->first_name ).' '.esc_html( $userInfo->last_name ),
				"firstName"	=> esc_html( $userInfo->first_name ),
				"surname"	=> esc_html( $userInfo->last_name ),
				"username"	=> $userInfo->user_login,
				"role"		=> $userlevel,
			);
		}

		return $userArray;

	}


	public static function getPots()
	{
		// Get all the question pots
		 $args = array(
			'posts_per_page'   => -1,
			'post_type'        => 'ek_pot',
		);
		$pot_array = get_posts( $args );

		return $pot_array;



	}


	public static function getPotQuestions($args)
	{
		$returnQcount="";
		$potID = $args['potID'];
		$qCount="";
		$exclude_reflective = "";
		$metaQuery = "";

		if(isset($args['qCount'])){$returnQcount = $args['qCount'];}
		if(isset($args['exclude_reflective']) ){


			// Why isn't this working?
			$metaQuery = array(
				'key' => "qType",
				'value' => "reflectiveText",
				'compare' => "!=",
				);

				$exclude_reflective=true;
		}


		$args = array(
			'posts_per_page'   => -1,
			'order'            => 'ASC',
			'post_type'        => 'ek_question',
			'post_parent'		=> $potID,
			'post_status'      => 'publish',
			'meta_query'		=> $metaQuery,
		);



		$posts_array = get_posts( $args );


		if($exclude_reflective==true )
		{

			foreach ($posts_array as $questionKey => $questionMeta)
			{
				$qType = get_post_meta($questionMeta->ID, 'qType', true);

				if($qType=="reflectiveText")
				{
					unset($posts_array[$questionKey]); // Remove if its a reflective question
				}
			}
		}

		if($returnQcount>=1)
		{
			shuffle($posts_array);
			// Now return the first X items of this randomised array
			$posts_array = array_slice($posts_array, 0, $returnQcount);
		}


		return  $posts_array;


	}


	public static function getBoundaryFeedback($percentageScore, $quizID)
	{
		// Get all the boundaries and stick them in an array
		$gradeBoundaries = get_post_meta($quizID, 'gradeBoundaries', true);

		// If its not an array i.e. doesn't exist then crate blank array
		$gradeBoundaries = is_array( $gradeBoundaries ) ? $gradeBoundaries : array();
		$feedback="";
		foreach($gradeBoundaries as $boundaryInfo)
		{
			$minGrade = $boundaryInfo['minGrade'];
			$maxGrade = $boundaryInfo['maxGrade'];

			if($percentageScore>=$minGrade && $percentageScore<=$maxGrade)
			{
				$feedback = ekQuiz_utils::formatMetaboxText($boundaryInfo['feedback']);
			}
		}
		return $feedback;
	}

    public static function getQuizzes()
    {
        $args = array(
			'posts_per_page'   => -1,
			'order'            => 'ASC',
			'post_type'        => 'ek_quiz',
			'post_status'      => 'publish',
		);



		$posts_array = get_posts( $args );
		return  $posts_array;


    }


	public static function getQuizResults($quizID)
	{
		global $wpdb;
		global $quizAttemptsTable;
		$SQL='Select * FROM '.$quizAttemptsTable.' Where quizID = '.$quizID.' ORDER by userID';

		$rs = $wpdb->get_results( $SQL, ARRAY_A );
		return $rs;


	}

	public static function getUserAttempts($quizID, $userID)
	{
		global $wpdb;
		global $quizAttemptsTable;
		$SQL='Select * FROM '.$quizAttemptsTable.' Where quizID = '.$quizID.' and userID = '.$userID.' ORDER by attemptID ASC';
		$rs = $wpdb->get_results( $SQL, ARRAY_A );
		return $rs;
	}


	public static function get_attempt_info($attempt_id)
	{
		global $wpdb;
		global $quizAttemptsTable;
		$SQL='Select * FROM '.$quizAttemptsTable.' Where attemptID = '.$attempt_id;
		$rs = $wpdb->get_row( $SQL  );
		return $rs;
	}

	public static function getUserResponse($questionID, $userID)
	{
		global $wpdb;
		global $userResponsesTable;

		$SQL='Select * FROM '.$userResponsesTable.' Where questionID = '.$questionID.' and userID = '.$userID.' ORDER by dateSubmitted DESC LIMIT 1';
		$rs = $wpdb->get_row( $SQL, ARRAY_A );

		return $rs;
	}


	// Gets all submissions for a single question
	public static function getQuestionResults($questionID)
	{
		global $wpdb;
		global $userResponsesTable;
		$SQL='Select * FROM '.$userResponsesTable.' Where questionID = '.$questionID.' ORDER by dateSubmitted ASC';


		$rs = $wpdb->get_results( $SQL, ARRAY_A );

		return $rs;

	}

	public static function getNewQuestionID_fromLegacy($questionID)
	{
		global $wpdb;
		global $legacyLookupTable;
		$SQL='Select * FROM '.$legacyLookupTable.' Where originalID = '.$questionID.' and itemType="question"';

		$rs = $wpdb->get_row( $SQL, ARRAY_A );

		$newID = $rs['newID'];




		return $newID;
	}

	/* Generate the number of complete quiz takers, as well ass current quiz takers
	*/
	public static function get_quiz_submission_overview($quiz_id)
	{
		$attempts = ekQuiz_queries::getQuizResults($quiz_id);

		$completed_count = 0;
		$participant_count = 0;
		$incomplete_count = 0;
		$total_attempts = 0;

		$user_attempts_array = array();
		foreach ($attempts as $attempt_info)
		{
			$user_id = $attempt_info['userID'];
			$user_attempts_array[$user_id][] = $attempt_info; // ADd this attempt to the user attmpts array

			$date_finished = $attempt_info['dateFinished'];
			if($date_finished == "0000-00-00 00:00:00")
			{
				$incomplete_count++;
			}
			else
			{
				$completed_count++;
			}
			$total_attempts++; // Regardless, up the total attempt count
		}

		// Count the number of unique users
		$unique_users = count($user_attempts_array);

		$return_array = array(
			"completed" => $completed_count,
			"incomplete" => $incomplete_count,
			"total_users" => $unique_users,
			"total_attempts" => $total_attempts,
		);

		return $return_array;
	}

} //Close class
?>
