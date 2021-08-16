<?php
$ekQuizzes_CPT = new ekQuizzes_CPT();
class ekQuizzes_CPT
{

	var $defaultQuizOptions = array(
		"useQuestionPots"			=> 1, // Use the question pots or not
		"completionMessage"			=> "", // The Message to display after the quiz finishes
		"completionRedirectURL"		=> "", // URL to redirect to after quiz finishes
		"emailOnCompletionList"	    => "", // Email current user when quiz is finised
		"showQuestionFeedback"		=> "on",
		"available_from"			=> "",
		"available_from_hour" => "08",
		"available_from_min" => "00",
		"available_to"			=> "",
		"available_to_hour" => "16",
		"available_to_min" => "00",
		"loginRequired"				=> "on",
		"maxAttempts"				=> "",
		"emailParticipantMark"		=> "",
		"attemptIntervalDays"		=> "",
		"attemptIntervalHours"		=> "",
		"timeLimit"					=> "",
		"timeLimitMinutes"			=> "",
		"allQuestionsPerPage"		=> "on",
		"questionsPerPage"			=> "",
		"quizInstructions"			=> "",
		"is_unavailable" => "",

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
		//Admin Menu
		add_action( 'init',  array( $this, 'create_CPT' ) );

		// Remove and add columns in the admin table
		add_filter( 'manage_ek_quiz_posts_columns', array( $this, 'my_custom_post_columns' ), 10, 2 );
		add_action('manage_ek_quiz_posts_custom_column', array($this, 'customColumnContent'), 10, 2);

		// Save additional  meta for the custom post
		add_action( 'save_post', array($this, 'savePostMeta' ));

		// Add metaboxes
		add_action( 'add_meta_boxes_ek_quiz', array( $this, 'addMetaBoxes' ));


		// Hook to admin_head for the CSS to be applied earlier - removes everything but 'publish' for the CPT
		add_action('admin_head-post.php', array($this, 'hide_publishing_actions') );
		add_action('admin_head-post-new.php', array($this, 'hide_publishing_actions'));

		// Create Boundaries and Results Pages
		add_action( 'admin_menu', array( $this, 'create_AdminPages' ));

		add_action( 'wp_footer', array( $this, 'frontendEnqueues' ) );

		// Customise page row (quick edit etc)
		add_filter( 'post_row_actions', array($this, 'custom_quick_links'), 10, 2 );
		add_filter( 'page_row_actions', array($this,'custom_quick_links'), 10, 2 );


	}


/*	---------------------------
	ADMIN-SIDE MENU / SCRIPTS
	--------------------------- */
	function create_CPT ()
	{


		//Projects
		$labels = array(
			'name'               =>  'My Quizzes',
			'singular_name'      =>  'Quiz',
			'menu_name'          =>  'Quizzes',
			'name_admin_bar'     =>  'Quizzes',
			'add_new'            =>  'Add New Quiz',
			'add_new_item'       =>  'Add New Quiz',
			'new_item'           =>  'New Quiz',
			'edit_item'          =>  'Edit Quiz',
			'view_item'          => 'View Quizzes',
			'all_items'          => 'Quizzes',
			'search_items'       => 'Search Quizzes',
			'parent_item_colon'  => '',
			'not_found'          => 'No quizzes found.',
			'not_found_in_trash' => 'No quizzes found in Trash.'
		);

		$args = array(
			'labels'            	=> $labels,
			'public'            	=> false,
			'exclude_from_search'	=> true,
			'publicly_queryable' 	=> false,
			'show_ui'            	=> true,
			'show_in_nav_menus'		=> false,
			'show_in_menu'      	=> 'edit.php?post_type=ek_pot',
			'query_var'         	=> true,
			'rewrite'           	=> false,
			'capability_type'   	=> 'post',
			'has_archive'       	=> true,
			'hierarchical'      	=> false,
			'menu_position'     	=> 65,
			'supports'          	=> array( 'title'  )

		);

		register_post_type( 'ek_quiz', $args );
	}

	// Remove Date Columns on projects
	function my_custom_post_columns( $columns  )
	{

	  	// Remove Date
		unset(
		$columns['date']
		);
		// Remove Checkbox
		unset(
		$columns['cb']
		);


		//$columns['quiz_name'] = 'Quiz Name';
		//$columns['add_question'] = '';
		$columns['questions'] = 'Questions';
		$columns['shortcode'] = 'Shortcode';
		$columns['boundaries'] = 'Grade Boundaries';
        $columns['question-results-breakdown'] = 'Question Breakdown';
        $columns['results'] = 'Results';
		//$columns['delete_quiz'] = '';
		return $columns;
	}




	// Content of the custom columns for Topics Page
	function customColumnContent($column_name, $post_ID)
	{
		switch ($column_name)
		{
			case "questions":
				// Get The meta aray of questions

				$useQuestionPots = get_post_meta($post_ID, 'useQuestionPots', true);
				$myQuestionPotListValues = get_post_meta($post_ID, 'myQuestionPotListValues', true);
				$totalQcount = 0;
				if($useQuestionPots==1)
				{
					foreach($myQuestionPotListValues as $thisQCount)
					{
						$thisQcount = intval($thisQCount);
						$totalQcount = $totalQcount+intval ($thisQcount);
					}
				}
				else
				{
					$questionListManualIDs = get_post_meta($post_ID, 'questionListManualIDs', true);
					// Convert to array for counting and remove blank array elements
					$manualQ_array = array_filter(explode(",", $questionListManualIDs));
					$totalQcount = count($manualQ_array);
				}

				echo '<b>'.$totalQcount.'</b> question(s)';

			break;

			case "shortcode":
				echo '[ek-quiz id='.$post_ID.']';
			break;

			case "boundaries":
				echo '<a href="options.php?page=ek-quiz-boundaries&quizID='.$post_ID.'">Grade boundaries</a>';
			break;

            case "question-results-breakdown":
                echo '<a href="options.php?page=ek-quiz-q-breakdown&quiz-id='.$post_ID.'">Question Results</a>';
            break;

			case "results":
				echo '<a href="options.php?page=ek-quiz-results&quizID='.$post_ID.'">Results</a>';
			break;

		}
	}

	// Hide everything but 'publish' from the publish box for notes
	function hide_publishing_actions()
	{
		global $post;
		if($post->post_type == "ek_quiz")
		{
			echo '<link rel="stylesheet" href="'.EK_QUIZ_PLUGIN_URL.'/css/pot-admin.css" type="text/css" media="all" />';
		}
	}




		// Register the metaboxes on  CPT
	function  addMetaBoxes()
	{

		global $post;

		//Quiz Meta Metabox
		$id 			= 'quiz_meta';
		$title 			= 'Quiz Options';
		$drawCallback 	= array( $this, 'drawOptionsMetaBox' );
		$screen 		= 'ek_quiz';
		$context 		= 'normal';
		$priority 		= 'default';
		$callbackArgs 	= array();

		add_meta_box(
			$id,
			$title,
			$drawCallback,
			$screen,
			$context,
			$priority,
			$callbackArgs
		);

		// Add Availability and Time limit

		//Availability Meta Metabox
		$id 			= 'quiz_dates_meta';
		$title 			= 'Availability';
		$drawCallback 	= array( $this, 'drawDatesMetaBox' );
		$screen 		= 'ek_quiz';
		$context 		= 'side';
		$priority 		= 'default';
		$callbackArgs 	= array();

		add_meta_box(
			$id,
			$title,
			$drawCallback,
			$screen,
			$context,
			$priority,
			$callbackArgs
		);

		//Time Limit Meta Metabox
		$id 			= 'quiz_timeLimit_meta';
		$title 			= 'Time Limit';
		$drawCallback 	= array( $this, 'drawTimeLimitMetaBox' );
		$screen 		= 'ek_quiz';
		$context 		= 'side';
		$priority 		= 'default';
		$callbackArgs 	= array();

		add_meta_box(
			$id,
			$title,
			$drawCallback,
			$screen,
			$context,
			$priority,
			$callbackArgs
		);


		//Shortcode metabox
		$id 			= 'quiz_shortcode';
		$title 			= 'Shortcode and Results';
		$drawCallback 	= array( $this, 'drawShortcodeMetaBox' );
		$screen 		= 'ek_quiz';
		$context 		= 'side';
		$priority 		= 'default';
		$callbackArgs 	= array();

		add_meta_box(
			$id,
			$title,
			$drawCallback,
			$screen,
			$context,
			$priority,
			$callbackArgs
		);
	}


	function drawShortcodeMetaBox($post,$metabox)
	{

		$quizID = $post->ID;
		echo 'To add this quiz to any page, copy and paste the shortcode below<br/><br/>';
		echo '[ek-quiz id='.$quizID.']<hr/>';


		echo '<a href="options.php?page=ek-quiz-results&quizID='.$quizID.'">View Quiz Results</a>';


	}

	function  drawOptionsMetaBox($post,$metabox)
	{

		wp_nonce_field( 'save_ek_quiz_metabox_nonce', 'ek_quiz_metabox_nonce' );

		$questionID = $post->ID;


		// Get default values and post meta
		$defaultQuizOptions = $this-> defaultQuizOptions;
		$quizMeta = get_post_meta($questionID);

		$useQuestionPots = isset( $quizMeta['useQuestionPots'] ) ? $quizMeta['useQuestionPots'][0] : $defaultQuizOptions['useQuestionPots'];
		$completionMessage = isset( $quizMeta['completionMessage'] ) ? $quizMeta['completionMessage'][0] : $defaultQuizOptions['completionMessage'];
		$completionRedirectURL = isset( $quizMeta['completionRedirectURL'] ) ? $quizMeta['completionRedirectURL'][0] : $defaultQuizOptions['completionRedirectURL'];
		$emailOnCompletionList = isset( $quizMeta['emailOnCompletionList'] ) ? $quizMeta['emailOnCompletionList'][0] : $defaultQuizOptions['emailOnCompletionList'];
		$showQuestionFeedback = isset( $quizMeta['showQuestionFeedback'] ) ? $quizMeta['showQuestionFeedback'][0] : $defaultQuizOptions['showQuestionFeedback'];
		$allQuestionsPerPage = isset( $quizMeta['allQuestionsPerPage'] ) ? $quizMeta['allQuestionsPerPage'][0] : $defaultQuizOptions['allQuestionsPerPage'];
		$questionsPerPage = isset( $quizMeta['questionsPerPage'] ) ? $quizMeta['questionsPerPage'][0] : $defaultQuizOptions['questionsPerPage'];

		$emailParticipantMark = isset( $quizMeta['emailParticipantMark'] ) ? $quizMeta['emailParticipantMark'][0] : $defaultQuizOptions['emailParticipantMark'];
		$quizInstructions = isset( $quizMeta['quizInstructions'] ) ? $quizMeta['quizInstructions'][0] : $defaultQuizOptions['quizInstructions'];

		$questionListManualIDs = isset( $quizMeta['questionListManualIDs'] ) ? $quizMeta['questionListManualIDs'][0] : "";
		$myQuestionPotListValues = get_post_meta($questionID, 'myQuestionPotListValues', true);

		echo '<div id="ekTabs">';
		echo '<ul>';
		echo '<li>Quiz Questions</li>';
		echo '<li>Quiz Format</li>';
		echo '<li>Completion options</li>';
		echo '<li>Quiz Instructions</li>';
		echo '</ul>';
		echo '</div>';


		echo '<div id="ekTabsContent" class="quizOptionsBox">';

		// Quiz Questions Select
		echo '<div>';
		echo '<h3>Quiz Questions</h3>';


		echo '<label for="useQuestionPots"><input type="radio" name="useQuestionPots" id="useQuestionPots" value="1"';
		if($useQuestionPots==1){echo ' checked ';}
		echo '/>Use Question Pots</label>';

		echo '<label for="addQuestionsFromList"><input type="radio" name="useQuestionPots" value="0" id="addQuestionsFromList" ';
		if($useQuestionPots==0){echo ' checked ';}
		echo '/>Define Questions from list (advanced)</label>';
		echo '<hr/>';

		echo '<div class="quizQuestionsList" id="quizQuestionsList"';
		if($useQuestionPots==0){echo ' style="display:none" ';}

		echo '>';

		echo '<i>Select the questions for this quiz</i><br/><br/>';

		// Get all the question pots
		 $args = array(
			'posts_per_page'   => -1,
			'post_type'        => 'ek_pot',
            'orderby'             => 'title',
            'order'             => 'ASC',
		);
		$pot_array = get_posts( $args );


		foreach($pot_array as $potMeta)
		{
			$potName = $potMeta->post_title;
			$potID = $potMeta->ID;

			// Get the question count
			$ekQuiz_queries = new ekQuiz_queries();
			$args = array(
				"potID" => $potID,
				"exclude_reflective" => true, // We don't want reflective question types
			);
			$potQuestions = $ekQuiz_queries::getPotQuestions($args);

			$questionCount = count($potQuestions);

			$i=0;
			echo '<select name="potSelect'.$potID.'_questions">';
			while ($i<=$questionCount)
			{
				echo '<option value="'.$i.'"';
				if(isset($myQuestionPotListValues[$potID]) )
				{
					if($myQuestionPotListValues[$potID]==$i){echo ' selected ';}
				}

				echo '>'.$i.'</option>';
				$i++;
			}
			echo '</select> ';
			echo $potName.'<br/>';

		}

		echo '</div>'; // End of Question Drop Down Div


		echo '<div id="questionsFromIDsDiv"';
		if($useQuestionPots==1)
		{
			echo ' style="display:none" ';
		}
		else
		{
			// Get the manual IDs
			$questionListManualIDs = get_post_meta($questionID, 'questionListManualIDs', true);
		}
		echo '>';
		echo '<i>Add question IDs sepearted by a comma e.g. 223, 45, 23</i><br/><br/>';


		echo '<input type="text" name="questionListManualIDs" value="'.$questionListManualIDs.'" /> Manual IDs';
		echo '</div>';

		echo '</div>';

		// Quiz Format Tab
		echo '<div>';
		echo '<h3>Feedback</h3>';
		echo '<label for="showFeedback"><input type="radio" id="showFeedback" name="showQuestionFeedback" value="true" ';
		if($showQuestionFeedback=="true"){echo ' checked  ';}
		echo ' />Show individual question feedback</label>';

		echo '<label for="hideFeedback"><input type="radio" id="hideFeedback" name="showQuestionFeedback" value="false" ';
		if($showQuestionFeedback<>"true"){echo ' checked  ';}
		echo ' />Do not show question feedback</label>';

		echo '<hr/>';

		echo '<h3>Questions Per Page</h3>';

		echo '<label for="allQuestionsPerPage">';
		echo '<input type="checkbox" name="allQuestionsPerPage" id="allQuestionsPerPage" ';
		if($allQuestionsPerPage=="on"){echo ' checked '; }
		echo ' />Show all questions on one page</label>';

		echo '<div id="questionsPerPageDiv"';
		if($allQuestionsPerPage=="on"){echo ' style="display:none;"';}
		echo '>';
		echo '<label for="questionsPerPage">';
		echo '<input type="text" size="2" id="questionsPerPage" name="questionsPerPage" value="'.$questionsPerPage.'"/>';
		echo 'Questions Per Page</label>';
		echo '</div>';

		echo '</div>';



		// Quiz Completion Options
		echo '<div>';

		echo '<h3>Completion Options</h3>';
		echo 'Message Displayed after quiz is complete';
		wp_editor($completionMessage, 'completionMessage', array(
		'wpautop'		=>      true,
		'media_buttons' =>      true,
		'textarea_name' =>      'completionMessage',
		'textarea_rows' =>      5,
		'teeny'                 =>      true
		));

		echo '<label for="completionRedirectURL">Redirection URL after completion (optional)</label>';
		echo '<input type="text" size="60" id="completionRedirectURL" name="completionRedirectURL" value="'.$completionRedirectURL.'" />';


		// Email admin on completion
		echo '<hr/>';
        echo '<label for="emailOnCompletionList">Email the following people when this quiz has been taken.<br/>';
        echo '<span class="smallText">Seperate each email by a comma e.g. email1@example.com, email2@example.com</span></label>';
		echo '<input type="text" size="60" id="emailOnCompletionList" name="emailOnCompletionList" value="'.$emailOnCompletionList.'" />';


		// Email user on completion
		echo '<hr/>';
		echo '<label for="emailParticipantMark"><input type="checkbox" id="emailParticipantMark" name="emailParticipantMark" ';
		if($emailParticipantMark=="on"){echo ' checked ';}
		echo '/>Email particiant their mark upon completion</label>';


		echo '</div>'; // End of quiz options wrapper
		// Quiz Completion Options
		echo '<div>';
		echo '<h3>Quiz Instructions (Optional)</h3>';
		echo 'Information here will appear before participants take a quiz<br/><br/>';
		wp_editor($quizInstructions, 'quizInstructions', array(
		'wpautop'		=>      true,
		'media_buttons' =>      true,
		'textarea_name' =>      'quizInstructions',
		'textarea_rows' =>      5,
		'teeny'                 =>      true
		));
		echo '</div>'; // End of quiz instructions

		echo '</div>';


	}

	// Date Availability
	function  drawDatesMetaBox($post,$metabox)
	{

		$quiz_id = $post->ID;
		// Get default values and post meta
		$defaultQuizOptions = $this-> defaultQuizOptions;

		// Create the form object
		$form = new \imperial_form();

		// Get the question meta
		$quizMeta = get_post_meta($quiz_id);


		// Make the quiz available or not
		$is_unavailable= isset( $quizMeta['is_unavailable'] ) ? $quizMeta['is_unavailable'][0] : $defaultQuizOptions['is_unavailable'];

		// Get the values for this metabox
		$loginRequired = isset( $quizMeta['loginRequired'] ) ? $quizMeta['loginRequired'][0] : $defaultQuizOptions['loginRequired'];
		$maxAttempts = isset( $quizMeta['maxAttempts'] ) ? $quizMeta['maxAttempts'][0] : $defaultQuizOptions['maxAttempts'];
		$attemptIntervalHours = isset( $quizMeta['attemptIntervalHours'] ) ? $quizMeta['attemptIntervalHours'][0] : $defaultQuizOptions['attemptIntervalHours'];
		$attemptIntervalDays = isset( $quizMeta['attemptIntervalDays'] ) ? $quizMeta['attemptIntervalDays'][0] : $defaultQuizOptions['attemptIntervalDays'];

		// Time Limit Meta
		$timeLimit 	= isset( $quizMeta['timeLimit'] ) ? $quizMeta['timeLimit'][0] : $defaultQuizOptions['timeLimit'];
		$timeLimitMinutes 	= isset( $quizMeta['timeLimitMinutes'] ) ? $quizMeta['timeLimitMinutes'][0] : $defaultQuizOptions['timeLimitMinutes'];




		// Process the start and end date
		$available_from = isset( $quizMeta['available_from'] ) ? $quizMeta['available_from'][0] : $defaultQuizOptions['available_from'];
		$available_from_hour = isset( $quizMeta['available_from_hour'] ) ? $quizMeta['available_from_hour'][0] : $defaultQuizOptions['available_from_hour'];
		$available_from_min = isset( $quizMeta['available_from_min'] ) ? $quizMeta['available_from_min'][0] : $defaultQuizOptions['available_from_min'];

		$available_to = isset( $quizMeta['available_to'] ) ? $quizMeta['available_to'][0] : $defaultQuizOptions['available_to'];
		$available_to_hour = isset( $quizMeta['available_to_hour'] ) ? $quizMeta['available_to_hour'][0] : $defaultQuizOptions['available_to_hour'];
		$available_to_min = isset( $quizMeta['available_to_min'] ) ? $quizMeta['available_to_min'][0] : $defaultQuizOptions['available_to_min'];

		$available_from_date = '';
		if($available_from)
		{
			$available_from_date = $available_from.' '.$available_from_hour.':'.$available_from_min.':00';
		}

		$available_to_date = '';
		if($available_to)
		{
			$available_to_date = $available_to.' '.$available_to_hour.':'.$available_to_min.':00';
		}

		$from_date_input = array(
			"type" => "datetime",
			"value" => $available_from_date,
			"id" => "available_from",
			"label" => "Available from (optional)"
		);


		$end_date_input = array(
			"type" => "datetime",
			"value" => $available_to_date,
			"id" => "available_to",
			"label" => "Available to (optional)"
		);


		// If $is_unavailable is on make it unavaiable
		$available_style = '';
		if($is_unavailable=="on")
		{
			$available_style = 'display:none';

		}
		$is_unavailable_input = array(
			"type" => "checkbox",
			"value" => $is_unavailable,
			"id" => "is_unavailable",
			"label" => "Make quiz unavailable",
		);

		echo '<div id="is_unavailable_wrap">';
		echo $form->form_item($is_unavailable_input);
		echo '</div>';

		echo '<div style="'.$available_style.'" id="availability_options_wrap">';
		echo $form->form_item($from_date_input);
		echo $form->form_item($end_date_input);

		echo '<div class="quizOptionsBox">';

		echo '<hr/><label for="loginRequired"><input type="checkbox" ';
		if($loginRequired=="on"){ echo ' checked ';}
		echo 'name="loginRequired" id="loginRequired" />';
		echo ' Participants must be logged in</label>';

		echo '<div id="loginRequiredOptions"';
		if($loginRequired<>"on"){ echo ' style="display:none" ';}

		echo '>';
		echo '<label for="maxAttempts">';
		echo '<input type="test" id="maxAttempts" name="maxAttempts" size="2" value="'.$maxAttempts.'" /> ';
		echo 'Max number of attempts</label><hr/>';

		echo 'Minimum time between attempts<br/>';
		echo '<input type="text" size="2" name="attemptIntervalHours" id="attemptIntervalHours" value="'.$attemptIntervalHours.'" />';
		echo ' hour(s)';
		echo '<input type="text" size="2" name="attemptIntervalDays" id="attemptIntervalDays" value="'.$attemptIntervalDays.'" />';
		echo ' day(s)';


		echo '</div>'; // End of hidden participant options when must be logged in

		echo '</div>'; // End of metabox wrap

		echo '</div>';


		?>
		<script>
		jQuery( "#is_unavailable" ).click(function()
		{
			  jQuery("#availability_options_wrap").slideToggle('fast');
			});
		</script>

		<?php


	}

	// Time Limit
	function  drawTimeLimitMetaBox($post,$metabox)
	{
		$quiz_id = $post->ID;

		// Get default values and post meta
		$defaultQuizOptions = $this-> defaultQuizOptions;
		$quizMeta = get_post_meta($quiz_id);
		// Set Var name with array key for defaults

		$timeLimit = isset( $quizMeta['timeLimit'] ) ? $quizMeta['timeLimit'][0] : $defaultQuizOptions['timeLimit'];
		$timeLimitMinutes = isset( $quizMeta['timeLimitMinutes'] ) ? $quizMeta['timeLimitMinutes'][0] : $defaultQuizOptions['timeLimitMinutes'];

		echo '<div class="quizOptionsBox">';
		echo '<label for="timeLimit"><input type="checkbox" name="timeLimit" id="timeLimit" ';
		if($timeLimit=="on"){ echo ' checked ';}
		echo '/>Add a time limit to this quiz</label>';


		// Hidden options unless clicked
		echo '<div id="timeLimitOptions"';
		if($timeLimit<>"on"){ echo ' style="display:none" ';}

		echo '>';

		// Minutes
		echo '<select id="timeLimitMinutes" name="timeLimitMinutes">';
		$i=1;
		while($i<=90)
		{
			echo '<option value="'.$i.'"';
			if($i==$timeLimitMinutes){echo ' selected ';}
			echo ' />'.$i.'</option>';
			$i++;
		}
		echo '</select> minutes ';

		// Seconds
		/*
		echo '<select id="timeLimitSeconds" name="timeLimitSeconds">';
		$i=0;
		while($i<=59)
		{
			echo '<option value="'.$i.'"';
			if($i==$timeLimitSeconds){echo ' selected ';}
			echo ' />'.$i.'</option>';
			$i++;
		}
		echo '</select> seconds';
		*/

		echo '</div>'; // End of hidden time options

		echo '</div>';


	}



		// Save metabox data on quiz
	function savePostMeta ( $postID )
	{
		global $post_type;
		global $post;

		if($post_type=="ek_quiz")
		{

			// Check if nonce is set.
			if ( ! isset( $_POST['ek_quiz_metabox_nonce'] ) ) {
				return;
			}

			// Verify that the nonce is valid.
			if ( ! wp_verify_nonce( $_POST['ek_quiz_metabox_nonce'], 'save_ek_quiz_metabox_nonce' ) ) {
				return;
			}

			// If this is an autosave, our form has not been submitted, so we don't want to do anything.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			// Check the user's permissions.
			if ( ! current_user_can( 'edit_post', $postID ) ) {
				return;
			}

			// check if there was a multisite switch before
			if ( is_multisite() && ms_is_switched() ) {
				return $post_id;
			}
			// Gett the pot ID (parent) and set it as the parent ID
			$potID = wp_get_post_parent_id( $postID );


			// Now save all the post meta		// Get default values and post meta
			$defaultQuizOptions = $this-> defaultQuizOptions;
			$quizMeta = get_post_meta($postID);
			// Set Var name with array key for defaults

			foreach($defaultQuizOptions as $KEY => $VALUE )
			{
				$metaValue='';
				if(isset($_POST[$KEY]) )
				{
					$metaValue = $_POST[$KEY];
				}

				switch ($KEY)
				{
					case "useQuestionPots":
						update_post_meta( $postID, $KEY, $metaValue  );

						if($metaValue==1) // If useQuestionPots is true
						{
							// Go through the question pots and get the values, store in array in quiz meta

							// Get all the question pots
							 $args = array(
								'posts_per_page'   => -1,
								'post_type'        => 'ek_pot',
							);
							$pot_array = get_posts( $args );

							$myQuestionPotArrayValues=array();
							foreach($pot_array as $potMeta)
							{
								$potID = $potMeta->ID;

								// Get the question count
								$thisValue = $_POST['potSelect'.$potID.'_questions'];

								$myQuestionPotArrayValues[$potID] = $thisValue;
							}



							// Now save that array of question data
							update_post_meta( $postID, 'myQuestionPotListValues', $myQuestionPotArrayValues  );
						}
						else // Its manual IDs so get the list
						{
							$questionListManualIDs = $_POST['questionListManualIDs'];

							$questionListManualIDs = str_replace(' ', '', $questionListManualIDs);



							update_post_meta( $postID, 'questionListManualIDs', $questionListManualIDs  );
						}

					break;

                    case "emailAdminOnCompletion":
                        $emailOnCompletionList = get_post_meta($quizID, "emailAdminOnCompletion", true);


                    break;


					default:


						update_post_meta( $postID, $KEY, $metaValue  );
					break;
				}

			}

		}
	}



	function create_AdminPages()
	{

		/* Create Admin Pages */
		/* Boundaries Page */
		$parentSlug = "no_parent";
		$page_title="Grade boundaries";
		$menu_title="";
		$menu_slug="ek-quiz-boundaries";
		$function=  array( $this, 'drawBoundariesPage' );
		$myCapability = "delete_pages";
		add_submenu_page($parentSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);

		/* Boundaries Edit Page */
		$parentSlug = "no_parent";
		$page_title="Edit Grade Boundary";
		$menu_title="";
		$menu_slug="ek-quiz-edit-boundaries";
		$function=  array( $this, 'drawEditBoundariesPage' );
		$myCapability = "delete_pages";
		add_submenu_page($parentSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);

        /* Question Breakdown Page */
        $parentSlug = "no_parent";
        $page_title="Question Result Breakdown";
        $menu_title="";
        $menu_slug="ek-quiz-q-breakdown";
        $function=  array( $this, 'draw_q_breakdown_page' );
        $myCapability = "delete_pages";
        add_submenu_page($parentSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);

		/* Results Page */
		$parentSlug = "no_parent";
		$page_title="Quiz Results";
		$menu_title="";
		$menu_slug="ek-quiz-results";
		$function=  array( $this, 'drawResultsPage' );
		$myCapability = "delete_pages";
		add_submenu_page($parentSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);
		/* Individual User Results Page */
		$parentSlug = "no_parent";
		$page_title="User Attempts";
		$menu_title="";
		$menu_slug="ek-user-attempts";
		$function=  array( $this, 'drawUserAttemptsPage' );
		$myCapability = "delete_pages";
		add_submenu_page($parentSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);

		/* Single Quiz Attempt resuts for a User  */
		$parentSlug = "no_parent";
		$page_title="User Attempts";
		$menu_title="";
		$menu_slug="ek-user-attempt";
		$function=  array( $this, 'drawSingleAttemptPage' );
		$myCapability = "delete_pages";
		add_submenu_page($parentSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);




	}
	function drawBoundariesPage	()
	{
		require_once EK_QUIZ_PATH.'admin/quiz_boundaries.php'; # Grade Boundaries
	}

	function drawEditBoundariesPage()
	{
		require_once EK_QUIZ_PATH.'admin/quiz_boundaries_edit.php'; # Grade Boundaries
	}

	function drawResultsPage	()
	{
		require_once EK_QUIZ_PATH.'admin/quiz_results.php'; # Results
	}
	function drawUserAttemptsPage()
	{
		require_once EK_QUIZ_PATH.'admin/user_attempts.php'; # Results
	}

	function drawSingleAttemptPage()
	{
		require_once EK_QUIZ_PATH.'admin/attempt_results.php'; # Results
	}

    function draw_q_breakdown_page()
    {
        require_once EK_QUIZ_PATH.'admin/question-breakdown.php'; # Results
    }




	function frontendEnqueues ()
	{

		// Register Ajax script for front end
		wp_enqueue_script('ek_quiz_page_ajax', EK_QUIZ_PLUGIN_URL.'/js/quiz_ajax.js', array( 'jquery' ) ); # AJAX functions for the quiz


		//Localise the JS file
		$params = array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'ajax_nonce' => wp_create_nonce('quiz_page_ajax_nonce'),
		);
		wp_localize_script( 'ek_quiz_page_ajax', 'quizPageAjax_params', $params );

	}



	public function generateQuizQuestionArray($quizID)
	{
		$quizMeta = get_post_meta($quizID);

		// Temp Array to get initial question IDs

		$qidArray = array();
		// Create Variables from all the quiz meta
		foreach($quizMeta as $metaKey => $metaValue){$$metaKey = $metaValue[0];}

		// Generate the quiz structure
		// Firstly get the question list IDs
		$myQuestionArray = array();
		if($useQuestionPots==1)
		{

			// Unserialise the array
			$myQuestionPotListValues = unserialize($myQuestionPotListValues);
			foreach($myQuestionPotListValues as $potID => $thisQCount)
			{
				$thisQcount = intval($thisQCount);

				// If there are questions in the pot then get all questions and take random number from that pot
				if($thisQcount>0)
				{

					// Get all question IDs from this pot
					$args = array(
						"potID" 				=> $potID,
						"qCount"				=> $thisQcount,
						"exclude_reflective"	=> true,
					);

					$ekQuiz_queries = new ekQuiz_queries();
					$potQuestions = $ekQuiz_queries::getPotQuestions($args);

					// Get the post IDs and add to the question array
					foreach($potQuestions as $questionMeta)
					{
						$questionID = $questionMeta->ID;
						$qidArray[] = $questionID;

					}
				}

			}
		}
		else
		{
			// Get the manual IDS and make sure there are no white spaces
			$questionListManualIDs = str_replace(' ', '', get_post_meta($quizID, 'questionListManualIDs', true));

			// Convert to array for counting and remove blank array elements
			$qidArray = array_filter(explode(",", $questionListManualIDs));
		}


		foreach($qidArray as $questionID)
		{
			$qType = get_post_meta($questionID, 'qType', true);

			$responseOptionsArray = array();
			$responseOptionsTypeQuestions = array("singleResponse", "multiResponse");

			if(in_array($qType, $responseOptionsTypeQuestions) )
			{
				$responseOptions = get_post_meta($questionID, 'responseOptions', true);
				$randomiseOptions = get_post_meta($questionID, 'randomiseOptions', true);

				foreach($responseOptions as $optionID => $responseMeta)
				{
					$responseOptionsArray[] = $optionID;
				}

				if($randomiseOptions=="on")
				{
					shuffle($responseOptionsArray);
				}

			}

			// Get the response options if its a multichoice or single choice
			$myQuestionArray[] = array(
				"questionID"	=> $questionID,
				"responseOptions"	=> $responseOptionsArray
			);
		}


		// Finally shuffle this new array to mix question pots
		if($useQuestionPots==1)
		{
			shuffle($myQuestionArray);
		}



		$quizQuestionCount = count($myQuestionArray);

		// Create the master array for pagination
		$quizQuestionsArray = array();

		// Now add those IDs to the quiz structure (questions per page)

		$i=1;
		$tempArray=array();
		$currentQcount=1;
		$pagedQuizQuestionArray = array();

		foreach ($myQuestionArray as $questionArrayInfo)
		{


			$questionID = $questionArrayInfo['questionID'];
			$responseOptions = $questionArrayInfo['responseOptions'];
			// Add the question ID and qType to the array
			$qType = get_post_meta($questionID, 'qType', true);
			$tempArray[] = array(
				"qType"	=> $qType,
				"questionID"	=> $questionID,
				"responseOptions"	=> $responseOptions,

			);


			if($allQuestionsPerPage<>"on")
			{
				// We are at the limit of questions per page
				// So add the temp array to the master array, clear it and move on
				if($i==$questionsPerPage || $currentQcount==$quizQuestionCount)
				{
					$i=0; // Reset the counter
					$pagedQuizQuestionArray[] = $tempArray;
					$tempArray = array(); // Clear the array
				}
			}

			$i++;
			$currentQcount++;



		}

		if($allQuestionsPerPage=="on")
		{
			$quizQuestionsArray[0] = $tempArray;
		}
		else
		{
			$quizQuestionsArray = $pagedQuizQuestionArray;
		}




		return $quizQuestionsArray;
	}


	// Remove the quick edit from this post type
	function custom_quick_links( $actions = array(), $post = null ) {
		// Abort if the post type is not "ek_quiz"
		if ( ! is_post_type_archive( 'ek_quiz' ) ) {
			return $actions;
		}
		// Remove the Quick Edit link
		if ( isset( $actions['inline hide-if-no-js'] ) )
		{
			unset( $actions['inline hide-if-no-js'] );
		}

		// Remove the View link
		if ( isset( $actions['view'] ) )
		{
			unset( $actions['view'] );	// view
		}

		// Return the set of links without Quick Edit
		return $actions;
	}


	function checkQuizAccess($quizID)
	{


		// Does a quiz with this ID exist?
		$post_type = get_post_type($quizID);
		if ( $post_type<>"ek_quiz")
		{
			$accessCheck[0] = false;
			$accessCheck[1] = 'This is not a valid quiz ID';
			return $accessCheck;
		}


		// Create some default vars
		$defaultQuizOptions = $this-> defaultQuizOptions;
		$quizMeta = get_post_meta($quizID);
		// Set Var name with array key for defaults

		foreach($defaultQuizOptions as $KEY => $VALUE )
		{
			$$KEY = $VALUE;
		}



		$quizMeta = get_post_meta($quizID);
		$accessCheck = array();
		$accessCheck[0] = true; // Allow access by default

		// Is it set to unavailable?
		$is_unavailable = isset($quizMeta['is_unavailable'][0]) ? $quizMeta['is_unavailable'][0] : '';

		if($is_unavailable=="on")
		{
			$accessCheck[0] = false;
			$accessCheck[1] = 'This quiz is not available';
			return $accessCheck;
		}


		// Get the current datetime
		$date = new \DateTime("now", new DateTimeZone('Europe/London') );
		$currentDate =  $date->format('Y-m-d H:i:s');

		$currentDate_TS = strtotime($currentDate); // Create time stamp as well


		// Create values from the keys
		foreach($quizMeta as $metaKey => $metaValue){$$metaKey = $metaValue[0];}

		// check if has to be logged in
		if($loginRequired=="on" )
		{

			if(!is_user_logged_in() )
			{
				$accessCheck[0] = false;
				$accessCheck[1] = 'You must be logged in to take this quiz';
				return $accessCheck;
			}


			/* Get the number of attempts this person has made */
			$currentUserID = get_current_user_id();

			$previousAttempts = ekQuiz_queries::getUserAttempts($quizID, $currentUserID);
			$userAtttempCount = count($previousAttempts);

			if($userAtttempCount>=$maxAttempts && $maxAttempts<>"")
			{
				$accessCheck[0] = false;
				$accessCheck[1] = 'You can only make '.$maxAttempts.' attempt(s) at this quiz.';
				return $accessCheck;
			}

			$lastAttemptInfo = end($previousAttempts);

			$lastDateStarted = '';
			if(is_array($lastAttemptInfo) )
			{
				$lastDateStarted = $lastAttemptInfo['dateStarted'];

			}

			// Check difference between attempts
			$minTimeBetweenAttempts = 0;
			if($attemptIntervalHours)
			{
				$minTimeBetweenAttempts = ($attemptIntervalHours*60*60);
			}

			if($attemptIntervalDays)
			{
				$minTimeBetweenAttempts = $minTimeBetweenAttempts+($attemptIntervalDays*24*60*60);
			}

			if($minTimeBetweenAttempts>0)
			{
				$lastDateStarted_TS = strtotime($lastDateStarted); // Get timestamp of last attempt
				$TStoCheck = $lastDateStarted_TS + $minTimeBetweenAttempts; // Get timestamp of next attempt allowed i./ last attempt + time interval

				// If current Date time is less than the allowed then return false
				if($currentDate_TS<$TStoCheck)
				{

					// get the time until the next allowed attempt
					$timeLeft = ($TStoCheck - $currentDate_TS);

					$min = floor($timeLeft / 60) % 60;
					$hours = floor($timeLeft / 3600) % 24;
					$days = floor($timeLeft / 86400);

					$accessCheck[0] = false;
					$accessCheck[1] = 'You can next take this test in <b>'.$days.' day(s),  '.$hours.' hours and '.$min.' minutes</b>';
					return $accessCheck;

				}
			}

		}

		// Check the date Available
		if($available_from)
		{

			// Create the available from date
			$available_from_date = $available_from.' '.$available_from_hour.':'.$available_from_min;
			$available_from_object = new \DateTime($available_from_date);
			if($currentDate<$available_from_date)
			{

				$availableDateStr = $available_from_object->format('l jS F, Y, g:i a');

				$accessCheck[0] = false;
				$accessCheck[1] = 'This quiz is not available until '.$availableDateStr.'.';
			}
		}

		// Check the date to
		if($available_to)
		{
			// Create the available from date
			$available_to_date = $available_to.' '.$available_to_hour.':'.$available_to_min;
			$available_to_object = new \DateTime($available_to_date);
			if($currentDate>$available_to_date)
			{

				$availableDateStr = $available_from_object->format('jS F, Y, g:i a');

				$accessCheck[0] = false;
				$accessCheck[1] = 'This quiz is now closed';
			}
		}

		// If we get this far they can take the quiz
		return $accessCheck;
	}




} //Close class
?>
