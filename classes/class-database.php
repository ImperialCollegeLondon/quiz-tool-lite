<?php
$ek_quiz_database = new ek_quiz_database();
class ek_quiz_database {

	//~~~~~
	function __construct ()
	{

		global $wpdb;
		// Define the table as a global
		global $quizAttemptsTable;
		$quizAttemptsTable = $wpdb->prefix . 'ek_quiz_attempts';

		// Define the table as a global
		global $userResponsesTable;
		$userResponsesTable = $wpdb->prefix . 'ek_user_responses';

		// Define the table as a global
		global $legacyLookupTable;
		$legacyLookupTable = $wpdb->prefix . 'ek_quiz_legacy';

		add_action( 'plugins_loaded', array($this, 'myplugin_update_db_check' ) );

	}

	// Function to check latest version and then update DB if needed
	function myplugin_update_db_check()
	{
		global $qtl_version;

		$savedVersion = get_option( 'ekQuizVersion' );

		if($savedVersion=="")
		{
			update_option( 'ekQuizVersion', $qtl_version );
			$this->installDB();
		}
		elseif ( $savedVersion< $qtl_version )
		{
			// Update version op
			update_option( 'ekQuizVersion', $qtl_version );
			$this->installDB();
		}

		// Overrider for testing
		//$this->installDB();

	}

	//~~~~~
	function installDB()
	{

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		// Get plugin version and set option to current version
		global $wpdb;
		global $quizAttemptsTable;
		global $userResponsesTable;
		global $legacyLookupTable;

		$charSet = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $quizAttemptsTable (
		attemptID int NOT NULL AUTO_INCREMENT,
		quizID mediumint(9) NOT NULL,
		dateStarted datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		dateFinished datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		userID bigint(20) NOT NULL,
		questionOrder text,
		attemptNumber mediumint(9),
		score mediumint(9),
		userResponses text,
		PRIMARY KEY (attemptID)
		) ".$charSet;
		dbDelta( $sql );

		/* This table stores the individual question responses NOT the quiz attempt responses */
		$sql = "CREATE TABLE $userResponsesTable (
		responseID int NOT NULL AUTO_INCREMENT,
		userID int NOT NULL,
		dateSubmitted datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		userResponse text,
		questionID int,
		gotItCorrect int,
		PRIMARY KEY (responseID)
		) ".$charSet;

		dbDelta( $sql );

		// Lookup Legacy table for displaying using the old shortcode
		$sql = "CREATE TABLE $legacyLookupTable (
		itemType varchar(255),
		originalID int,
		newID int
		) ".$charSet;

		dbDelta( $sql );



	}





	/**
	* Gets one row from table by post id and user id
	* @param int $post_id
	* @param int $user_id
	* @return stdClass data set
	*/
	public static function getFormSubmissions( $formID, $userID )
	{
		global $wpdb;
		global $formSquaredTable;
		$query = $wpdb->prepare( "SELECT * FROM $formSquaredTable WHERE formID= %d AND userID = %d", $formID, $userID );
		$formSubmissions = $wpdb->get_results($query, ARRAY_A);

		return $formSubmissions;
	}

	/**
	* Gets one row from table by post id and user id
	* @param int $post_id
	* @param int $user_id
	* @return stdClass data set
	*/
	public  function getAttemptInfo( $attemptID )
	{
		global $wpdb;
		global $quizAttemptsTable;
		$query = $wpdb->prepare( "SELECT * FROM $quizAttemptsTable WHERE attemptID= %d",
		$attemptID );
		$attemptInfo = $wpdb->get_row($query, ARRAY_A);

		return $attemptInfo;
	}
	public  function getAllQuizAttemptsByUser( $quizID, $userID )
	{
		global $wpdb;
		global $quizAttemptsTable;

		$query = $wpdb->prepare( "SELECT * FROM $quizAttemptsTable WHERE quizID= %d and userID = %d",
		$quizID, $userID );
		$attemptInfo = $wpdb->get_results($query, ARRAY_A);

		return $attemptInfo;
	}
	/**
	* Inserts new entry into table.
	* @param array $note
	*/
	public function saveAttemptInfo( $args )
	{
		global $wpdb;
		global $quizAttemptsTable;

		$quizID = $args['quizID'];
		$thisAttemptNumber = $args['thisAttemptNumber'];
		$quizQuestionsArray = serialize($args['quizQuestionsArray']);

		$dateStarted =  date("Y-m-d H:i:s");

		$currentUserID = get_current_user_id();



		/*
		if(count($checkExists)>=1) // This already exists so update
		{

						$currentDate  =  date('Y-m-d H:i:s'); // The Date Updated
						$wpdb->query( $wpdb->prepare(
										"UPDATE   ".$formSquaredTable." SET lastUpdate=%s, formData=%s, formAttachments=%s
										WHERE submissionID = %d;",
										$currentDate,
										$dataArray,
										$quizQuestionsArray,
										$submissionID
						));

		}
		else
		{

			*/
		$wpdb->query( $wpdb->prepare(
		"INSERT INTO ".$quizAttemptsTable." (quizID, userID, questionOrder, dateStarted, attemptNumber ) VALUES ( %d, %d, %s, %s, %d )",
		array(
			$quizID,
			$currentUserID,
			$quizQuestionsArray,
			$dateStarted,
			$thisAttemptNumber
			)
		));
		$attemptID = $wpdb->insert_id;
		return $attemptID;

	}

	public function savePageResponses($attemptID, $userResponses)
	{

		global $wpdb;
		global $quizAttemptsTable;

		// seriliase the userREsoies
		$userResponses = serialize($userResponses);

		/* TO DO Security that they can do they quiz and they are the logged user */
		//$currentDate  =  date('Y-m-d H:i:s'); // The Date
		$wpdb->query( $wpdb->prepare(
			"UPDATE   ".$quizAttemptsTable." SET userResponses=%s WHERE attemptID = %d;",
			$userResponses,
			$attemptID
		));


	}

	public function saveQuizScore($attemptID, $score)
	{

		global $wpdb;
		global $quizAttemptsTable;



		/* TO DO Security that they can do they quiz and they are the logged user */
		$currentDate  =  date('Y-m-d H:i:s'); // The Date
		$wpdb->query( $wpdb->prepare(
			"UPDATE   ".$quizAttemptsTable." SET score=%d, dateFinished=%s WHERE attemptID = %d;",
			$score,
			$currentDate,
			$attemptID
		));



	}

}
?>
