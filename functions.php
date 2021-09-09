<?php

$ekQuiz = new ekQuiz();
class ekQuiz
{

	public static $defaults =array
	(
		"buttonText" 	=> "Submit Answer",
		"correctFeedback" 	=> "Correct!",
		"incorrectFeedback"	=> "Sorry, that's incorrect",
		"minAccessLevel"	=> "edit_pages",
		"showAnswer"		=> true,
	);

	//~~~~~
	function __construct ()
	{
		$this->addWPActions();
	}

/*	---------------------------
	PRIMARY HOOKS INTO WP
	--------------------------- */
	function addWPActions ()
	{
		//Add Front End Jquery and CSS
		add_action( 'wp_head', array( $this, 'frontendEnqueues' ) );
		add_action( 'wp_head', array( $this, 'jointEnqueues' ) );


		// Back end
		add_action( 'admin_enqueue_scripts', array( $this, 'jointEnqueues' ));
		add_action( 'admin_enqueue_scripts', array( $this, 'adminEnqueues' ));



		// Enable Session Storage
		add_action('init', array($this, 'myStartSession'), 1);
		add_action('wp_logout', array( $this, 'myEndSession') );
		add_action('wp_login', array($this, 'myEndSession'), 1 ) ;

		// Setup shortcodes
		add_shortcode('ek-question', array('ekQuizDraw','drawShortcodeQuestion'));
		add_shortcode('ek-quiz', array('ekQuizDraw','drawShortcodeQuiz'));
		add_shortcode('ek-question-response', array('ekQuizDraw','drawUserResponse'));
		add_shortcode('ek-quiz-leaderboard', array( 'ekQuizDraw', 'drawLeaderboard'));

		// The list of quizzes
		add_filter( 'the_content', array( 'ekQuizDraw', 'draw_quiz_list'));
		add_filter( 'the_content', array( 'ekQuizDraw', 'test_s3'));



		// Legacy Shortcodes
		add_shortcode('QTL-Question', array('ekQuizDraw','drawLegacyShortcodeQuestion'));
		add_shortcode('QTL-Leaderboard', array( 'ekQuizDraw', 'drawLegacyLeaderboard'));

		// Add Settings Page
		add_action( 'admin_menu', array( $this, 'create_AdminPages' ));


	}


	function adminEnqueues()
	{
		wp_enqueue_script('jquery');
        wp_enqueue_script( 'jquery-ui-core' );
        wp_enqueue_script( 'jquery-ui-widget' );
        wp_enqueue_script( 'jquery-ui-mouse' );
        wp_enqueue_script( 'jquery-ui-draggable' );
        wp_enqueue_script( 'jquery-ui-droppable' );
        wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		/* add jquery ui datepicker and theme */
		// get the jquery ui object
		global $wp_scripts;
		global $qtl_version;
		$queryui = $wp_scripts->query('jquery-ui-core');

		// load the jquery ui theme
		$url = "https://ajax.googleapis.com/ajax/libs/jqueryui/".$queryui->ver."/themes/smoothness/jquery-ui.css";
		wp_enqueue_style('jquery-ui-smoothness', $url, false, null);
		wp_enqueue_style( 'ek-quiz-admin-css', EK_QUIZ_PLUGIN_URL . '/css/admin.css' );

		wp_enqueue_script('ek_quiz_response_edit_js', EK_QUIZ_PLUGIN_URL.'/js/response_option_edit.js', array( 'jquery' ), $qtl_version ); #JS for managing resopnse options
		wp_enqueue_script('ek_quiz_quiz_edit_js', EK_QUIZ_PLUGIN_URL.'/js/quiz_settings_page.js', array( 'jquery' ), $qtl_version ); #JS for managing quiz options


		wp_enqueue_style( 'ek-quiz-font-awesome' );

		// Admin JS
		wp_enqueue_script('ek_quiz_admin_js', EK_QUIZ_PLUGIN_URL.'/js/admin.js', array( 'jquery' ), $qtl_version ); #Admin JS




		// Other general JS stuff for admin
		//wp_enqueue_script('ek_quiz_custom_js', EK_QUIZ_PLUGIN_URL.'/js/admin.js', array( 'jquery' ) ); #JS for managing quiz options

		add_thickbox();


		// Data tables
		wp_enqueue_script('ek_datatables-js', '//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js', array( 'jquery' ) );
		wp_enqueue_style( 'ek-datatables-css-js', '//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css' );
		wp_enqueue_style( 'ek-datatables', EK_QUIZ_PLUGIN_URL . '/css/datatables.css' );


		// Localise the admin JS for Ajax
		//Localise the JS file
		$params = array(
		'ajaxurl' => admin_url( 'admin-ajax.php' )
		);

		wp_localize_script( 'ek_quiz_response_edit_js', 'quizAdminAjax', $params );

		wp_localize_script( 'ek_quiz_admin_js', 'quizAdminAjax', $params );



	}

	function frontendEnqueues ()
	{
		global $qtl_version;

		// Register question JS front end
		wp_register_script('ek_quiz_js', EK_QUIZ_PLUGIN_URL.'/js/questions_frontend.js', array( 'jquery' ), $qtl_version );

		// Register Ajax script for front end
		wp_register_script('ek_quiz_single_question_ajax', EK_QUIZ_PLUGIN_URL.'/js/question_submit_ajax.js', array( 'jquery' ), $qtl_version ); # AJAX JS for submitting single questions

	}


	function jointEnqueues()
	{
		global $qtl_version;

		//Scripts
		// Custom Styles
		wp_register_style( 'ek-quiz-css', EK_QUIZ_PLUGIN_URL . '/css/styles.css', array(), $qtl_version );


		// Font Awesome CSS
		wp_register_style( 'ek-quiz-font-awesome', '//use.fontawesome.com/releases/v5.2.0/css/all.css' );


	}



	// This function is called when a shortcode runs to only up when required
	public static function registerMyFrontScripts()
	{
		// Load The Main Css
		wp_enqueue_style( 'ek-quiz-css' );
		wp_enqueue_style( 'ek-quiz-font-awesome' );


		/* Javascript */

		// load jQuery
		wp_enqueue_script('jquery');

		wp_enqueue_script('ek_quiz_js');
		wp_enqueue_script('ek_quiz_single_question_ajax');


		//Localise the JS file
		$params = array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'ajax_nonce' => wp_create_nonce('submitQuestion_ajax_nonce'),
		);

		wp_localize_script( 'ek_quiz_single_question_ajax', 'submitQuestionAjax_params', $params );

	}

	// Start Sessions if one does not already exist
	// Used to keep track of which pot ID a question is in without worrying about query strings
	function myStartSession() {
		if(!session_id()) {
			session_start();
		}
	}
	// Delete sessions upon logout and login
	function myEndSession() {
		session_start();
		session_destroy ();
	}



	// This key functions returns ann question types in an array
	// Array key is the qType stored in post meta
	// It looks in the question-types sub folder for any files - each file is a question type
	// Also has a hook 'ek_getOtherQuestionTypes' so you can extend with other question types with add-ons
	static function getQuestionTypesArray()
	{
		$directory = EK_QUIZ_PATH.'question-types';

		$questionTypeNames = array_diff(scandir($directory), array('..', '.'));

		$qTypesArray = array();
		foreach($questionTypeNames as $qTypeNameString)
		{

			// Remove the php extension_loaded
			$fileParts = pathinfo($qTypeNameString);
			$thisQType = $fileParts['filename'];

			// Get the name of the class for this qType - will be the name of the filename with "ek_"  prepended
			$className = 'ek_'.$thisQType;

			// Initalise the class
			$thisClass = new $className();

			// Get this question meta
			$qMeta  = $thisClass->questionMeta();


			// Add the question meta to the array with the qType string as the key
			$qTypesArray[$thisQType] = $qMeta;
		}

		// Get any additional question types from add ons
		$qTypesArray = apply_filters( 'ek_getOtherQuestionTypes', $qTypesArray) ;



		return $qTypesArray;

	}



	function create_AdminPages()
	{

		$minAccessLevel = get_option('min_quiz_access_level', 'manage_options');

		/* Create Export Pages */
		$parentSlug = "edit.php?post_type=ek_pot";
		$page_title="Import / Export";
		$menu_title="Import / Export";
		$menu_slug="ek-quiz-export";
		$function=  array( $this, 'drawExportPage' );
		$myCapability = $minAccessLevel;
		add_submenu_page($parentSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);

		/* Create Settings Pages */
		$parentSlug = "edit.php?post_type=ek_pot";
		$page_title="Settings";
		$menu_title="Settings";
		$menu_slug="ek-quiz-settings";
		$function=  array( $this, 'drawPluginSettings' );
		$myCapability = $minAccessLevel;
		add_submenu_page($parentSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);


        $parentSlug = "edit.php?post_type=ek_pot";
        $page_title="Help";
        $menu_title="Help";
        $menu_slug="ek-qtl-help";
        $function=  array( $this, 'drawHelpPage' );
        $myCapability = $minAccessLevel;
        add_submenu_page($parentSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);
	}

	function drawPluginSettings()
	{
		require_once EK_QUIZ_PATH.'admin/settings.php'; # Results
	}


	function drawExportPage()
	{
		require_once EK_QUIZ_PATH.'admin/export.php'; # Results
	}

    function drawHelpPage()
    {
        require_once EK_QUIZ_PATH.'admin/help.php'; # Results
    }




}
?>
