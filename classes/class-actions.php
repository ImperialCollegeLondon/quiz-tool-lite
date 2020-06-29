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
}


?>
