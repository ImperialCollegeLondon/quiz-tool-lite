<?php
/*
Plugin Name: Quiz Tool Pro by EduKit
Plugin URI: https://wordpress.org/plugins/quiz-tool-lite/
Description: Powerful quiz tool for displaying questions and quizzes on pages and posts
Version: 3.0.1
Author: Alex Furr and Simon Ward
Author URI: http://www.edu-kit.org
License: GPL
GitHub Plugin URI: https://github.com/ImperialCollegeLondon/quiz-tool-lite
*/

date_default_timezone_set('UTC');

// Global defines
define( 'EK_QUIZ_PLUGIN_URL', plugins_url('quiz-tool-lite', dirname( __FILE__ )) );
define( 'EK_QUIZ_PATH', plugin_dir_path(__FILE__) );

require_once EK_QUIZ_PATH.'functions.php'; # Initialise
require_once EK_QUIZ_PATH.'/classes/class-pots.php'; # Question Pots
require_once EK_QUIZ_PATH.'/classes/class-questions.php'; # Questions
require_once EK_QUIZ_PATH.'/classes/class-quizzes.php'; # Quizzes
require_once EK_QUIZ_PATH.'/classes/class-queries.php'; # Queries
require_once EK_QUIZ_PATH.'/classes/class-custom-feedback.php'; # Custom Feedback Messages
require_once EK_QUIZ_PATH.'/classes/class-draw.php'; # Draw Functions
require_once EK_QUIZ_PATH.'/classes/class-utils.php'; # Utility Functions
require_once EK_QUIZ_PATH.'/classes/class-ajax.php'; # Ajax Functions
require_once EK_QUIZ_PATH.'/classes/class-database.php'; # Database Functions
require_once EK_QUIZ_PATH.'/classes/class-actions.php'; # Action Functions
require_once EK_QUIZ_PATH.'/classes/class-export.php'; # Export Functions

require_once EK_QUIZ_PATH.'/classes/class-upgrade.php'; # UPDATE checker



/* Helpers */
require_once EK_QUIZ_PATH.'/libs/ek-tabs/ek-tabs.php'; # Tabs Library


// SI's change

// Load up all the question type classes
$qDirectory = 'question-types';
$qFullDirectory = EK_QUIZ_PATH.$qDirectory;
$questionTypeNames = array_diff(scandir($qFullDirectory), array('..', '.'));

foreach($questionTypeNames as $qTypeNameString)
{
	
	require_once $qFullDirectory.'/'.$qTypeNameString.'/'.$qTypeNameString.'.php'; # Each Q Type Class
	
	// Include the Ajax File
//	require_once $qFullDirectory.'/'.$qTypeNameString.'/ajax.php'; # Require Ajax



}


?>