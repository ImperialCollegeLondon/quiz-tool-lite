<?php
$ekQuestions_CPT = new ekQuestions_CPT();
class ekQuestions_CPT
{
	
	

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
		
		// Redirect to qType select page if creating new question
		add_action( 'admin_init',  array( $this, 'qTypePageRedirect' ) );	
		
		// Add the qType select page
		add_action('admin_menu', array( $this, 'create_qTypeSelectPage') );
		
		// Save additional  meta for the custom post
		add_action( 'save_post', array($this, 'savePostMeta' ), 10 );
		
		// Add metaboxes
		add_action( 'add_meta_boxes_ek_question', array( $this, 'addMetaBoxes' ));
		
		// Modify question list main query to only show questions with correct potID parent	
		add_action( 'pre_get_posts', array($this, 'modify_admin_list_query' ) );
		
		// Remove and add columns in the admin table
		add_filter( 'manage_ek_question_posts_columns', array( $this, 'my_custom_post_columns' ), 10, 2 );		
		add_action('manage_ek_question_posts_custom_column', array($this, 'customColumnContent'), 10, 2);	
		
		// Hook to admin_head for the CSS to be applied earlier - removes everything but 'publish' for the CPT
		add_action('admin_head-post.php', array($this, 'hide_publishing_actions') );
		add_action('admin_head-post-new.php', array($this, 'hide_publishing_actions'));			
		
		// Adds correct potID session to the question list page
		add_action( 'admin_head-edit.php', array($this, 'questionsListAdminHook' ) );
		
		
		add_action( 'all_admin_notices', array($this, 'addBackButton_on_editPage' ) );
		
		// Add help text for certain question types
		add_action( 'edit_form_after_title', array($this, 'myprefix_edit_form_after_title' ) );

		
		// Add duplicate post link and remove quick edit
		add_filter( 'post_row_actions', array($this, 'custom_quick_links'), 10, 2 );
		add_filter( 'page_row_actions', array($this,'custom_quick_links'), 10, 2 );		
		
		//Check for Conversions
		add_action( 'admin_action_ek_question_convertToMultipleResponse', array($this, 'ek_question_convertToMultipleResponse') );		
		
		// Make sure you can't see the question when viewing fron front end unless admin	
		add_action( 'the_content', array( $this, 'filterQuestionView' ), 100 );
		
		// Create Single Results Pages
		add_action( 'admin_menu', array( $this, 'create_AdminPages' ));
		
		
		// Force update question title to be a shorteneded version of the content
		add_filter( 'save_post' , array($this, 'add_question_title') , 200 ) ; // Grabs the inserted post data so you can modify it.

		// Create pot array for all questions if it's the question list - for loading duplicate to drop downs
		add_action( 'admin_enqueue_scripts', array ($this, 'questionAdminListFunctions') );
		
		
		// Add custom JS if on questoin list edit page
		add_action( 'admin_head', array ($this, 'questionAdminListJS') );
		
		add_action( 'admin_notices', array($this, 'checkForCustomFeedback' ) );
		
		
				
		
		
	}
	
	
	
/*	---------------------------
	ADMIN-SIDE MENU / SCRIPTS 
	--------------------------- */
	function create_CPT ()
	{
		
	
		//Projects
		$labels = array(
			'name'               =>  'Questions',
			'singular_name'      =>  'Questions',
			'menu_name'          =>  'Questions',
			'name_admin_bar'     =>  'Questions',
			'add_new'            =>  'Add New Question',
			'add_new_item'       =>  'Add New Question ',
			'new_item'           =>  'New Question',
			'edit_item'          =>  'Edit Question',
			'view_item'          => 'View Question',
			'all_items'          => 'All Questions',
			'search_items'       => 'Search Questions',
			'parent_item_colon'  => '',
			'not_found'          => 'No questions found.',
			'not_found_in_trash' => 'No questions found in Trash.'
		);
	
		$args = array(
			'labels'            	=> $labels,
			'public'             	=> false,
			'exclude_from_search'	=> true,
			'publicly_queryable' 	=> false,
			'show_ui'            	=> true,
			'show_in_nav_menus'	 	=> false,
			'show_in_menu'       	=> false,
			'query_var'         	=> true,
			'rewrite'           	=> false,
			'capability_type'   	=> 'post',
			'has_archive'       	=> true,
			'hierarchical'      	=> false,
			'menu_position'     	=> 65,
			'supports'          	=> array( 'editor'  )
			
		);
		
		register_post_type( 'ek_question', $args );
	}
	
	
	// Modify the query for admin list questions to only show questions in this pot
	function modify_admin_list_query( $query )
	{
		// Check if on frontend and main query is modified
		if(  is_admin() && $query->is_main_query() && $query->query_vars['post_type'] == 'ek_question' )
		{
	
			if(isset($_GET['potID']) )
			{
				$_SESSION['currentPotID']=$_GET['potID'];
			}	
		
			
			$potID = $_SESSION['currentPotID'];
			$query->set('post_parent', $potID);
			return $query;
		}
	 
	}	
	
	
	
	// Register the metaboxes on  CPT
	function  addMetaBoxes()
	{
		
		global $post;
				
		// Get the qType and show relvent meta boxes
		$qType = "";
		if(isset($_GET['qType']))
		{
			$qType=$_GET['qType'];
		}
		else
		{
			$qType = get_post_meta ( $post->ID, 'qType', true);
		}
			
			
			
		//Questino Meta Metabox
		$id 			= 'question_meta';
		$title 			= 'Question Shortcode';
		$drawCallback 	= array( $this, 'drawMetaBox' );
		$screen 		= 'ek_question';
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
		
			
		
		
			
		//Correct / incorrect feedback
		$id 			= 'question_feedback';
		$title 			= 'Question Feedback';
		$drawCallback 	= array( $this, 'drawFeedbackMetaBox' );
		$screen 		= 'ek_question';
		$context 		= 'normal';
		$priority 		= 'default';
		$callbackArgs 	= array(
			"qType"	=> $qType,
		);		
		add_meta_box( 
			$id, 
			$title, 
			$drawCallback, 
			$screen, 
			$context,
			$priority, 
			$callbackArgs 
		);	
		
		
		// Only show this box if its single or multiple responseMeta
		
		
		$responseTypeArray = array ("singleResponse", "multiResponse", "shortTextResponse", "multiBlanks");
		
		if(in_array($qType, $responseTypeArray))
		{
			

			// Question Feedback Behaviour
			$id 			= 'question_behaviour';
			$title 			= 'Question Behavior';
			$drawCallback 	= array( $this, 'drawBehaviourMetaBox' );
			$screen 		= 'ek_question';
			$context 		= 'side';
			$priority 		= 'default';
			$callbackArgs 	= array(
				"qType"	=> $qType,
			);		
			add_meta_box( 
				$id, 
				$title, 
				$drawCallback, 
				$screen, 
				$context,
				$priority, 
				$callbackArgs 
			);	
			
			
			
			
			
			
			
			
			$metaBoxTitle = "Response options";
			
			//$textTypeAnswers = array("shortTextResponse", "multiBlanks");
			if(ekQuiz_utils::isTextBasedAnswer($qType)==true)
			{
				$metaBoxTitle = "Possible Answers";
			}	
			
		
			// Response Options
			$id 			= 'response_options';
			$title 			= $metaBoxTitle;
			$drawCallback 	= array( $this, 'drawResponseOptionEditBox' );
			$screen 		= 'ek_question';
			$context 		= 'normal';
			$priority 		= 'high';
			$callbackArgs 	= array(
				"qType"	=> $qType,
			);
			
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
	}
	
	function drawBehaviourMetaBox($post, $callbackArgs)
	{
		$qType = $callbackArgs['args']['qType'];
		
		$questionID = $post->ID;
		
		// Get the question behaviour
		$showCorrectAnswer = ekQuiz_utils::getQuestionShowAnswer($questionID);
		
		$showCorrectAnswerChecked = '';
		if($showCorrectAnswer=="on")
		{
			$showCorrectAnswerChecked = 'checked';
		}
		
		echo '<label for="showCorrectAnswer">';
		echo '<input type="checkbox" id="showCorrectAnswer" name="showCorrectAnswer" '.$showCorrectAnswerChecked.'>';
		echo 'Show correct answers and feedback on submit';		
		
	}
	
	function  drawFeedbackMetaBox($post,$callbackArgs)
	{
		
		// Get the qType
		$qType = $callbackArgs['args']['qType'];
		
		if($qType=="reflectiveText")
		{
			$title="Reflection Feedback";
		}
		else
		{
			$title="Correct Feedback";
		}
		
		$correctFeedback = get_post_meta($post->ID,'correctFeedback',true);
		$incorrectFeedback = get_post_meta($post->ID,'incorrectFeedback',true);
		
		
		echo '<h1>'.$title.'</h1>';
		wp_editor($correctFeedback, 'correctFeedback', array(
		'wpautop'		=>      true,
		'media_buttons' =>      true,
		'textarea_name' =>      'correctFeedback',
		'textarea_rows' =>      5,
		//'teeny'                 =>      true
		)); 
		
		if($qType<>"reflectiveText")
		{
			
			echo '<h1>Incorrect Feedback</h1>';
			wp_editor($incorrectFeedback, 'incorrectFeedback', array(
			'wpautop'		=>      true,
			'media_buttons' =>      true,
			'textarea_name' =>      'incorrectFeedback',
			'textarea_rows' =>      5,
			//'teeny'                 =>      true
			)); 	
		
		}
		
	}
	
	
	
	function drawMetaBox($post, $callbackArgs)
	{
		
		// Add Nonce Field
		wp_nonce_field( 'save_ek_question_metabox_nonce', 'ek_question_metabox_nonce' );
		
		if(isset($_GET['potID']))
		{
			$_SESSION['currentPotID']=$_GET['potID'];
			
		}
		
		$currentPotID = $_SESSION['currentPotID'];
		
		
		// Get the qType for this post if it exists
		$qType = get_post_meta ( $post->ID, 'qType', true);
		
		if(isset($_GET['qType']))
		{
			$qType=$_GET['qType'];
		}
		
		//echo 'Current Pot : '.get_the_title($currentPotID).' ['.$currentPotID.']<br/>';
		
		
		if($post->ID)
		{
			$thisID = $post->ID;
			echo '[ek-question id='.$thisID.']';
		}
		
		// Add Checkbox for including text area box for reflective questions
		
		if($qType=="reflectiveText")
		{
			
			
			
			$addTextarea = get_post_meta ( $post->ID, 'addTextarea', true);
			
			global $pagenow;			
			// If its a new post then set the default to checked for this
			if (in_array( $pagenow, array( 'post-new.php' ) ) )
			{
	   			$addTextarea = "on";
			}			
			
			
			
			
			echo '<hr/><label for="addTextarea">';
			echo '<input type="checkbox" name="addTextarea" id="addTextarea" ';
			if($addTextarea=="on"){echo ' checked ';}
			echo '/>';
			echo 'Include feedback text box</label><hr/>';
		}
		
		
		
		echo '<input type="hidden" value="'.$qType.'" name="qType" id="qType" />';
	}
	
	
	// Remove Date Columns on projects
	function my_custom_post_columns( $columns )
	{
		unset(
			$columns['date']
		);			
		$columns['qType'] = 'Type';	
		$columns['qShortcode'] = 'Shortcode';
		$columns['responses'] = 'Responses';	
		
		 
	  return $columns;
	}		
	
	
		// Content of the custom columns for Topics Page
	function customColumnContent($column_name, $post_ID)
	{
		switch ($column_name)
		{
			case "qShortcode":			
				echo '[ek-question id='.$post_ID.']';
			break;
			case "qType":
				// Get the qType
				$qType = get_post_meta($post_ID, 'qType', true);
				
				/* GEt the Actual description of this qType */
				$qTypeName = '';
				$qTypeClass = 'ek_'.$qType;
				$thisClass = 'ek_'.$qType;
				
				$qTypeMeta = $thisClass::questionMeta();
				
				echo $qTypeMeta['qString'];
			break;	

			case "responses":
			
				// Get the parent ID i.e the pot ID
				$parentPotID = wp_get_post_parent_id($post_ID);
				//echo '<a href="">Results</a>';
				echo '<a href="options.php?page=ek-question-results&questionID='.$post_ID.'&potID='.$parentPotID.'"><i class="fas fa-chart-pie"></i> Results</a>';

			
			break;
		}
			
	}	
		
	
	
	// If its on the questions list admin page then update the session if it does not exist with the GET var
	function questionsListAdminHook()
	{
		global $post_type;
		if($post_type=="ek_question")
		{
			
			// Also Pull in custom CSS
			echo '<link rel="stylesheet" href="'.EK_QUIZ_PLUGIN_URL.'/css/question-admin.css" type="text/css" media="all" />';
			
			
			
		}
	
	}	
		
	
	// Hide everything but 'publish' from the publish box for notes
	function hide_publishing_actions()
	{
		global $post_type;
		global $pagenow;
		
		if($post_type == "ek_question")
		{
			echo '<link rel="stylesheet" href="'.EK_QUIZ_PLUGIN_URL.'/css/question-admin.css" type="text/css" media="all" />';
			
		}
	}		
	
	function addBackButton_on_editPage()
	{
		global $post_type, $pagenow, $post;
		$potID = '';
		$questionID='';
		if($post && $post_type=="ek_question")
		{
			$questionID = $post->ID;
		}
		
		// If the question ID exists get the parent ID
		if($questionID)
		{
			$potID = wp_get_post_parent_id($questionID);
			
			if($potID)
			{
				$_SESSION['currentPotID'] = $potID; // Set the session as well
			}			
			
		}
		
		// Get the Parnet ID
		// Get the Parnet ID		
		if(isset($_GET['potID']))
		{
			$potID = $_GET['potID'];
			$_SESSION['currentPotID'] = $potID; // Set the session as well
			$potName = get_the_title($potID);			
			

		}
		elseif(isset($_SESSION['currentPotID']))
		{
			$potID = $_SESSION['currentPotID'];		
			$potName = get_the_title($potID);			

		}
		
		
		if(($pagenow == "post.php" || $pagenow=="post-new.php") && $post_type=="ek_question")
		{
		
			if($potID=="")
			{
				$questionID = $post->ID;
				$potID = wp_get_post_parent_id( $questionID );
				$_SESSION['currentPotID'] = $potID; // Set the session as well
				$potName = get_the_title($potID);			
			}
		
		}

		
		// Overide with the query string if it exists		
		if(($pagenow == "post.php" || $pagenow=="post-new.php") && $post_type=="ek_question")
		{
			
			
			
			global $post;			
		
			echo '<h1>'.$potName.' : Questions</h1>';
			$href = get_admin_url().'edit.php?post_type=ek_question&potID='.$potID;
			echo '<a href="'.$href.'">'.ekQuizDraw::backIcon().'Back to '.$potName.' questions</a>';
			
		}
		elseif($post_type=="ek_question")
		{

			?>
			<script>
			jQuery(document).ready(function() {
			jQuery( ".wp-heading-inline" ).prepend( "<?php echo $potName; ?> " );
			});
			</script>
			
			<?php
			return;
		}
		
	}	
	
	
	

	function myprefix_edit_form_after_title()
	{
		
		global $post_type, $pagenow, $post;
				
		if(($pagenow == "post.php" || $pagenow=="post-new.php") && $post_type=="ek_question")
		{
			$qType = get_post_meta($post->ID, 'qType', true);
			switch ($qType)
			{
				case "multiBlanks":
					echo 'Add your sentence with [blank] for each missing word<br/>';
					echo '<i>e.g. One small step for [blank], one giant leap for [blank]</i>';
				
				break;
			}
	
		}		
		
		
		
	}
	
	function drawResponseOptionEditBox ( $post, $callbackArgs )
    {
		// Get the qType
		$qType = $callbackArgs['args']['qType'];
		
		// Set Var if the qType is text based for various optios later on
		$textBasedResponse=false;
		

		
		if(ekQuiz_utils::isTextBasedAnswer($qType)==true)
		{
			$textBasedResponse=true;
		}
			
		if($qType=="multiBlanks")
		{
			$placeholderText = 'Possible Answers';
			$buttonText = 'Add Answers';
		}
		else
		{
			$placeholderText = 'Possible Answer';
			$buttonText = 'Add Answer';
		}		


        // Question Meta
        $responseOptions = get_post_meta ( $post->ID, 'responseOptions', true);
        $responseOptions = ! is_array( $responseOptions ) ? array() : $responseOptions;
		


        $caseSensitive = get_post_meta ( $post->ID, 'caseSensitive', true);
        $randomiseOptions = get_post_meta ( $post->ID, 'randomiseOptions', true);
        $next_key = get_post_meta( $post->ID, 'autoIncrementOptionKeyID', true );

        $next_key = ! $next_key ? 1 : $next_key;
		
		//echo 'next_key = '.$next_key.'<br/>';
//		echo 'randomiseOptions = '.$randomiseOptions;
		
	//	print_r($responseOptions);
		
		
		echo '<input type="hidden" id="initial_key" value="'.$next_key.'">';
        
        echo '<div id="options_list_wrap">';
		
		$displayNumber = 1;
		foreach ( $responseOptions as $key => $optionInfo )
		{
			
			$responseText = $optionInfo["optionValue"];
			
			$feedbackIfSelected = '';
			$feedbackIfNotSelected = '';

			if(isset($optionInfo["feedbackIfSelected"]) ){$feedbackIfSelected = $optionInfo["feedbackIfSelected"];}
			if(isset($optionInfo["feedbackIfNotSelected"]) ){$feedbackIfNotSelected = $optionInfo["feedbackIfNotSelected"];}
			
			
			
			$isCorrect = '';
			if(isset($optionInfo["isCorrect"]))
			{
				$isCorrect = $optionInfo["isCorrect"];
			}
			
			$optionInfo['dataKey'] = $key;
			$optionInfo['displayNumber'] = $displayNumber;
			
			
			
			//echo '<pre>';
			//print_r($optionInfo);
			//echo '</pre>';
			
			echo ekQuestions_CPT::drawResponseOptionEditDiv($optionInfo, $qType);
			
			
			
			
			
			/*
			
			echo '<div class="option-item';
			if ($isCorrect==true){echo ' isCorrect ';}
			echo '" data-key="' . $key . '" id="response_option_wrap_'.$key.'">';
			echo '<div class="option-display-number">' .$display_number. '. </div>';
			echo '<div class="option-value">';
			echo '<input type="text" class="option-value" name="shortlist_option_values[]" value="' . $responseText . '"/>';
			
			
			// Do not show the tick box if its a short answer as they are ALL correct
			if($textBasedResponse==false )
			{
				echo '<br/><label for="option'.$key.'_isCorrect"><input type="checkbox" id="option'.$key.'_isCorrect" name="option'.$key.'_isCorrect" ';
				if ($isCorrect==true){echo ' checked ';}
				echo '/>Correct Answer</label>';
			}
			
			
			echo '<br/>';
			
			
			$advancedStyle = '';			
			if ($qType<>"radio" && $qType<>"check")
			{
				$advancedStyle = 'display:none;';
			}
			
			echo '<div class="option-feedback-toggle" id="feedback_'.$key.'" style="'.$advancedStyle.'">Advanced Feedback</div>';	
			
			echo '<div id="option_feedback_'.$key.'" style="display:none" class="feedbackTextareaDiv">';
			
			echo '<h3>Feedback if selected</h3>';			
			echo '<textarea name="feedbackIfSelected_'.$key.'">'.$feedbackIfSelected.'</textarea>';
			
			echo '<h3>Feedback if not selected</h3>';
			echo '<textarea name="feedbackIfNotSelected_'.$key.'">'.$feedbackIfNotSelected.'</textarea>';			
			echo '</div>';
			echo '</div>';
			
			echo '<div class="option-item-remove button-secondary" id="remove_'.$key.'">Remove</div>';
			
			
			echo '<br style="clear:both;">';
			echo '<input type="hidden" name="shortlist_option_keys[]" value="' . $key . '"/>';
			
			echo '</div>';
			*/
			$displayNumber += 1;
			
		}
		echo '</div>';
       
	   
		// Render the 'Add New Item Box'
		
		echo '<div id="options_input_wrap">';
		echo '<h3>New Response Option</h3>';
		echo '<input type="text" name="new_option_text" class="newResponseOption" id="new_option_text" placeholder="'.$placeholderText.'" /><br/>';

		// Do not show the tick box if its a short answer as they are ALL correct
		if($textBasedResponse==false )
		{
			echo '<label for="new_is_correct"><input type = "checkbox" id="new_is_correct"  name="new_is_correct"/>Correct Answer</label>';
		}
		echo '<hr/><a href="#" class="button-secondary" id="add_new_option">'.$buttonText.'</a>';
		echo '<span id="add_option_feedback"></span>';
		echo '<input type="hidden" name="next_key" id="next_key"  value="'.$next_key.'" />';
		echo '</div>';
		
		if($qType=="multiBlanks" )
		{
			echo 'Seperate each possible answer with a comma e.g. answer 1, answer , answer 3.<br/>';
			echo 'Add possible answers for each blank in the order they appear.';
			echo '<hr/>';
		}	
		if($textBasedResponse==true )
		{
			
			echo '<h3>Additional Settings</h3>';
			echo '<label for="caseSensitive">';
			echo '<input type = "checkbox" id="caseSensitive"  name="caseSensitive" ';
			if($caseSensitive=="on"){echo ' checked '; }
			echo '/>Answers are case senstive</label>';
		}
		else
		{
			echo '<h3>Additional Settings</h3>';
			echo '<label for="randomiseOptions">';
			echo '<input type = "checkbox" id="randomiseOptions"  name="randomiseOptions" ';
			if($randomiseOptions=="on"){echo ' checked '; }
			echo '/>Randomise responses</label>';
		}		


		
    }
	
	public static function drawResponseOptionEditDiv($optionInfo, $qType)
	{
		
		$key = $optionInfo['dataKey'];
		$displayNumber = $optionInfo['displayNumber'];
		
		$html = '';
		$responseText = $optionInfo["optionValue"];
		
		
		$feedbackIfSelected = '';
		$feedbackIfNotSelected = '';

		if(isset($optionInfo["feedbackIfSelected"]) ){$feedbackIfSelected = $optionInfo["feedbackIfSelected"];}
		if(isset($optionInfo["feedbackIfNotSelected"]) ){$feedbackIfNotSelected = $optionInfo["feedbackIfNotSelected"];}
		

		
		$textBasedResponse = false;

		if(ekQuiz_utils::isTextBasedAnswer($qType)==true)
		{
			$textBasedResponse=true;
		}
		
		$responseText = htmlentities($responseText);

		
		
		$isCorrect = '';
		if(isset($optionInfo["isCorrect"]))
		{
			$isCorrect = $optionInfo["isCorrect"];
		}
		$html.= '<div class="option-item';
		if ($isCorrect==true){$html.= ' isCorrect ';}
		$html.= '" data-key="' . $key . '" id="response_option_wrap_'.$key.'">';
		$html.= '<div class="option-display-number">' .$displayNumber. '. </div>';
		$html.= '<div class="option-value">';
		$html.= '<input type="text" class="option-value" name="shortlist_option_values[]" value="' . $responseText . '"/>';
		
		
		
		// Do not show the tick box if its a short answer as they are ALL correct
		if($textBasedResponse==false )
		{
			$html.= '<br/><label for="option'.$key.'_isCorrect"><input type="checkbox" id="option'.$key.'_isCorrect" name="option'.$key.'_isCorrect" ';
			if ($isCorrect==true){$html.= ' checked ';}
			$html.= '/>Correct Answer</label>';
		}
		
		
		$html.= '<br/>';

		$advancedStyle = '';			
		if ($qType<>"singleResponse" && $qType<>"multiResponse")
		{
			$advancedStyle = 'display:none;';
		}
		
		$html.= '<div class="option-feedback-toggle" id="feedback_'.$key.'" style="'.$advancedStyle.'">Advanced Feedback</div>';	
		
		$html.= '<div id="option_feedback_'.$key.'" style="display:none" class="feedbackTextareaDiv">';
		
		$html.= '<h3>Feedback if selected</h3>';			
		$html.= '<textarea name="feedbackIfSelected_'.$key.'">'.$feedbackIfSelected.'</textarea>';
		
		$html.= '<h3>Feedback if not selected</h3>';
		$html.= '<textarea name="feedbackIfNotSelected_'.$key.'">'.$feedbackIfNotSelected.'</textarea>';			
		$html.= '</div>';
		$html.= '</div>';
		
		$html.= '<div class="option-item-remove button-secondary" id="remove_'.$key.'">Remove</div>';
		
		
		$html.= '<br style="clear:both;">';
		$html.= '<input type="hidden" name="shortlist_option_keys[]" value="' . $key . '"/>';
		
		$html.= '</div>';
			
		return $html;
		

	}
			
	
	
		// Save metabox data on edit slide
	function savePostMeta ( $postID )
	{
		global $post_type;
		
		if($post_type=="ek_question")
		{
		
			// Check if nonce is set.
			if ( ! isset( $_POST['ek_question_metabox_nonce'] ) ) {
				return;
			}
			
			// Verify that the nonce is valid.
			if ( ! wp_verify_nonce( $_POST['ek_question_metabox_nonce'], 'save_ek_question_metabox_nonce' ) ) {
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
	
			if($potID=="")
			{
				$potID = $_SESSION['currentPotID'];
				wp_update_post(
					array(
						'ID' => $postID, 
						'post_parent' => $potID
					)
				);
			}	
			
			// Store thte Question Type - default to free text if for any reason its not been set yet
			$qType 	= isset( $_POST['qType'] ) 	?  	$_POST['qType']  : 'freeText';
			update_post_meta( $postID, 'qType', $qType );		
			// Save the Correct and incorrect feedback
			$correctFeedback 	= isset( $_POST['correctFeedback'] ) 	?  		$_POST['correctFeedback']  		: '';	
			$incorrectFeedback 	= isset( $_POST['incorrectFeedback'] ) 	?  		$_POST['incorrectFeedback']  		: '';					
			update_post_meta( $postID, 'correctFeedback', $correctFeedback );		
			update_post_meta( $postID, 'incorrectFeedback', $incorrectFeedback );
			
			
			/* Response Options */	
			$option_values  = isset( $_POST['shortlist_option_values'] ) && is_array( $_POST['shortlist_option_values'] ) ? $_POST['shortlist_option_values'] : array();
			$option_keys    = isset( $_POST['shortlist_option_keys'] ) && is_array( $_POST['shortlist_option_keys'] ) ? $_POST['shortlist_option_keys'] : array();
			
			// Reflective Feedback Options 
			$addTextarea 	= isset( $_POST['addTextarea'] ) 	?  		$_POST['addTextarea']  		: '';					
			update_post_meta( $postID, 'addTextarea', $addTextarea );
			
			$options_data   = array();
			foreach( $option_keys as $i => $key ) {
				$intkey = intval( $key );
				
				
				$responseMeta = array(
				"optionValue" => $option_values[ $i ]
				);
				
				$isThisCorrect = '';
				if(isset($isCorrectValues[ $i ]) )
				{
					$isThisCorrect = $isCorrectValues[ $i ];
				}
				
				$feedbackIfSelected = $_POST['feedbackIfSelected_'.$key];
				$feedbackIfNotSelected = $_POST['feedbackIfNotSelected_'.$key];
				
				$responseMeta['feedbackIfSelected'] = $feedbackIfSelected;
				$responseMeta['feedbackIfNotSelected'] = $feedbackIfNotSelected;
				
				echo $i.'= '.htmlentities(stripslashes($option_values[ $i ])).'<br/>';
				
				
				
				$options_data[ $intkey ] = array (
					"responseValue" => htmlspecialchars(stripslashes($option_values[ $i ] )),
					"isCorrect" => $isThisCorrect,
					"feedbackIfSelected" => $feedbackIfSelected,
					"incorrectFeedback" => $feedbackIfNotSelected
					
				);
				
				$checkboxID = 'option'.$intkey.'_isCorrect';

				if(isset($_POST[$checkboxID]))
				{
					$responseMeta["isCorrect"] = true;
				}
				

				
				
				$options_data[ $intkey ] = $responseMeta;
				
			}
			

			
			foreach ($options_data as $intKey => $responseMeta)
			{
				
				$optionValue = $responseMeta['optionValue'];
				
				if($optionValue=="")
				{
					unset($options_data[$intKey]);
				}
			}
			

			
			$next_key = ! empty( $_POST['next_key'] ) ? intval( $_POST['next_key'] ) : 1;
			$next_key = $next_key ? $next_key : 1;
			
			
			update_post_meta( $postID, 'responseOptions', $options_data );        
			update_post_meta( $postID, 'autoIncrementOptionKeyID', $next_key );
			/* End reponse options */
			
			/* Save if case sensitive */
			$caseSensitive 	= isset( $_POST['caseSensitive'] ) 	?  		$_POST['caseSensitive']  		: '';					
			update_post_meta( $postID, 'caseSensitive', $caseSensitive );
			
			/* Randomise Options */
			$randomiseOptions 	= isset( $_POST['randomiseOptions'] ) 	?  		$_POST['randomiseOptions']  		: '';					
			update_post_meta( $postID, 'randomiseOptions', $randomiseOptions );
	
			// Save the behavior
			$showCorrectAnswer 	= isset( $_POST['showCorrectAnswer'] ) 	?  		$_POST['showCorrectAnswer']  		: '0';					
			update_post_meta( $postID, 'showCorrectAnswer', $showCorrectAnswer );		
	
	
		}		
	
	}	
	
	
	function qTypePageRedirect()
	{
		
		global $pagenow;
		
		$post_type="";
		
		if(isset($_GET['post_type']))
		{
			$post_type=$_GET['post_type'];
		}
				
		if($post_type == "ek_question" && $pagenow== "post-new.php")
		{
			
			if(!isset($_GET['qType']))
			{
				// Get the potID and set as session
				if(isset($_GET['potID']))
				{
					$_SESSION['currentPotID']=$_GET['potID'];
				}
				wp_redirect(admin_url('/admin.php?page=my-custom-submenu-page') );
			
			exit;
			}
		}
	}
	
	// Create the qType selection page
	// This is the page that gets shown before the question edit page if its a new post
	function create_qTypeSelectPage() {
		add_submenu_page(
			'null',
			'My Custom Submenu Page',
			'My Custom Submenu Page',
			'manage_options',
			'my-custom-submenu-page',
			array ($this, 'draw_qTypeSelectPage' ) );
	}
	 
	function draw_qTypeSelectPage() {
	
		require_once EK_QUIZ_PATH.'admin/qType_choose.php'; # Load admin pages
	}
	
	
	
	// Remove the quick edit from this post type
	function custom_quick_links( $actions = array(), $post = null ) {
		
		
		$postParentID = $post->post_parent;
		// Abort if the post type is not "ek_question"
		if ( ! is_post_type_archive( 'ek_question' ) ) {
			return $actions;
		}
		// Remove the Quick Edit link
		if ( isset( $actions['inline hide-if-no-js'] ) ) {
			unset( $actions['inline hide-if-no-js'] );
			
			
		}
			
		if (current_user_can('edit_posts'))
		{
			if(isset($_GET['potID']) )
			{
				$_SESSION['currentPotID']=$_GET['potID'];

			}
			
			$questionID = $post->ID;
			
			global $qPots;
			
			$potID = $_SESSION['currentPotID'];
			//$actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?type=ek_question&potID='.$potID.'&action=ek_question_duplicate&post=' . $questionID, basename(__FILE__), 'duplicate_nonce' ) . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
						
			$duplicateString = '
			<a href="" id="duplicateClick'.$questionID.'">Duplicate</a>
			<div id="duplicateOptions'.$questionID.'" style="color:#000; display:none">Duplicate To:<br/>			
			';
			
			//$duplicateString.='<form method="post" action="edit.php?post_type=ek_question&potID='.$postParentID.'&action=ek_question_duplicate">';
			$duplicateString.='<select name="targetPot_'.$questionID.'" id="targetPot_'.$questionID.'">';
			foreach($qPots as $potInfo)
			{
				$tempPotID = $potInfo->ID;
				$tempPotName = $potInfo->post_title;
				$duplicateString.='<option value="'.$tempPotID.'"';
				if($postParentID==$tempPotID)
				{
					$duplicateString.= ' selected ';
				}
				
				$duplicateString.='>';
				$duplicateString.=$tempPotName;
				if($postParentID==$tempPotID)
				{
					$duplicateString.= ' (Current Pot) ';
				}
				$duplicateString.='</option>';
			}
			$duplicateString.='</select>';
			
			
			$duplicateString.='<a href="#" data-potid="'.$postParentID.'" data-questionid="'.$questionID.'" class="button-secondary confirmDuplication">Duplicate</a>';
			$duplicateString.='</div>
			<script>
			
			jQuery( "#duplicateClick'.$questionID.'" ).click(function() {
				event.preventDefault();
				jQuery( "#duplicateOptions'.$questionID.'" ).toggle( "fast" );
			});
			


			</script>
			';
			
			$actions['duplicate'] = $duplicateString;
			
			
			// Also look at the current question type = if its single response then let them convert to multiResponse
			$qType = get_post_meta($questionID, 'qType', true);
			
			if($qType=="singleResponse")
			{
				$actions['convertType'] = '<a href="' . wp_nonce_url('admin.php?potID='.$potID.'&type=ek_question&questionID='.$questionID.'&action=ek_question_convertToMultipleResponse&post=' . $questionID, basename(__FILE__), 'duplicate_nonce' ) . '" title="Convert to multiple response" rel="permalink">Convert to Multiple Response</a>';
			}
			
			
			
		}		
		// Return the set of links without Quick Edit
		return $actions;
	}
	/*
	 * Function creates post duplicate as a draft and redirects then to the edit post screen
	 */
	function ek_question_duplicate($targetQuestionID, $targetPotID)
	{
	
	
		
		global $wpdb;
		
		$post = get_post( $targetQuestionID );
	 
		/*
		 * if you don't want current user to be the new post author,
		 * then change next couple of lines to this: $new_post_author = $post->post_author;
		 */
		$current_user = wp_get_current_user();
		$new_post_author = $current_user->ID;
		

		/*
		 * new post data array
		 */
		$args = array(
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'post_author'    => $new_post_author,
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_name'      => $post->post_name,
			'post_parent'    => $targetPotID,
			'post_password'  => $post->post_password,
			'post_status'    => 'publish',
			'post_title'     => $post->post_title,
			'post_type'      => $post->post_type,
			'to_ping'        => $post->to_ping,
			'menu_order'     => $post->menu_order
		);
 
		/*
		 * insert the post by wp_insert_post() function
		 */
		$new_post_id = wp_insert_post( $args );
 
		/*
		 * get all current post terms ad set them to the new post draft
		 */
		$taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
		foreach ($taxonomies as $taxonomy) {
			$post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
			wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
		}
 
		/*
		 * duplicate all post meta just in two SQL queries
		 */
		$post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$targetQuestionID");
		if (count($post_meta_infos)!=0) {
			$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
			foreach ($post_meta_infos as $meta_info) {
				$meta_key = $meta_info->meta_key;
				if( $meta_key == '_wp_old_slug' ) continue;
				$meta_value = addslashes($meta_info->meta_value);
				$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
			}
			$sql_query.= implode(" UNION ALL ", $sql_query_sel);
			$wpdb->query($sql_query);
		}

	}
		
	// Make sure you can't see the question when viewing fron front end unless admin	
	function filterQuestionView($theContent)
	{
		global $post;
		$postID = get_the_ID();
		$thisPostType = get_post_type( $postID );
		// hide the question title AND the question content so it can only be viewed from shortcode or from quiz
		if($thisPostType=="ek_question") 
		{
			$theContent = '<style>
			.entry-title, .nav-links { 
			display:none; 
			}
			</style>Forbidden';
		}
		
		return $theContent;
	}
		
	function create_AdminPages()
	{
		
		/* Create Question Results Pages */		
		$parentSlug = "no_parent";
		$page_title="Question Results";
		$menu_title="";
		$menu_slug="ek-question-results";
		$function=  array( $this, 'drawResultsPage' );
		$myCapability = "manage_options";
		add_submenu_page($parentSlug, $page_title, $menu_title, $myCapability, $menu_slug, $function);
		
	}		
	
	function drawResultsPage	()
	{
		require_once EK_QUIZ_PATH.'admin/question_results.php'; # Grade Boundaries
	}	
	
	
	// Force update the title name to the content trim - because questions don't need titles
	

	function add_question_title ($post_id) {
		
        //echo '<pre>';
        //print_r($_POST);
        //echo '</pre>';
        
        
        if ( $post_id == null || empty($_POST) )
			return;

		if ( !isset( $_POST['post_type'] ) || $_POST['post_type']!='ek_question' )  
			return; 

		if ( wp_is_post_revision( $post_id ) )
			$post_id = wp_is_post_revision( $post_id );

		//global $post;  
		//if ( empty( $post ) )
		//	$post = get_post($post_id);
        
        if ( ! isset( $_POST['content'] ) ) {
            return;
        }
        
        global $wpdb;
        
        //$title = substr(preg_replace("/[^A-Za-z0-9 ]/", ' ', strip_tags($post->post_content) ), 0, 80);
        $title = substr(preg_replace("/[^A-Za-z0-9 ]/", ' ', strip_tags( $_POST['content'] ) ), 0, 80);
        $title=$title.'...';
        
        $where = array( 'ID' => $post_id );
        $wpdb->update( $wpdb->posts, array( 'post_title' => $title ), $where );
	}	
	
	
	
	// Convert to multiple response
	static function ek_question_convertToMultipleResponse()
	{
		
		global $wpdb;
		if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'ek_question_duplicate' == $_REQUEST['action'] ) ) ) {
			wp_die('No post to convert has been supplied!');
		}
	 
		/*
		 * Nonce verification
		 */
		if ( !isset( $_GET['duplicate_nonce'] ) || !wp_verify_nonce( $_GET['duplicate_nonce'], basename( __FILE__ ) ) )
			return;
	 
		/*
		 * get the original post id
		 */
		$questionID = $_GET['questionID'];
		
		$potID = $_GET['potID'];			
		update_post_meta( $questionID, "qType", "multiResponse" );	
		wp_redirect( admin_url( 'edit.php?post_type=ek_question&potID='.$potID ) );
	

	}
	
	
	
	
	static function questionAdminListFunctions($hook_suffix)
	{
		
		global $post_type;
		$screen = get_current_screen();
		
		
		if( $hook_suffix== "edit.php" && $post_type=="ek_question")
		{
			
			
			// Generate global array of all questoin pots
			global $qPots;
			$qPots = ekQuiz_queries::getPots();
			
			
		}
    
	}
	
	static function questionAdminListJS()
	{
		global $pagenow ;		
		global $typenow;

		
		if( $pagenow== "edit.php" && $typenow=="ek_question")
		{
			
			
			?>
			<script>
			
			jQuery( document ).ready(function()
			{
		
			
				jQuery(".confirmDuplication").on('click', function(event){
					
					
									
					var targetQuestionID = jQuery( this ).data( "questionid" );
					var thisPotID = jQuery( this ).data( "potid" );
					var targetPotID = jQuery('#targetPot_'+targetQuestionID).val();
					
					
					ek_question_duplicate(targetQuestionID, targetPotID, thisPotID);						
					
					event.stopPropagation();
					
					

					
					
				});		

			});			

			
			
			</script>
			<?php
			
		}
    
	}	
	
	
	function checkForCustomFeedback()
	{
		
		global $pagenow ;		
		global $typenow;

		
		if( $pagenow== "edit.php" && $typenow=="ek_question")
		{
			
			if(isset($_GET['feedback']) )
			{
				$feedbackType = $_GET['feedback'];
				
				switch ($feedbackType)
				{
					
					case "questionduplicated":
		
						echo '<div class="notice notice-success is-dismissible">';
						echo '<p>Question Duplicated</p>';
						echo '</div>';					
					
					break;					
					
				}
			}
		}
	}
	
	
	

		
		
			
	 
	
} //Close class


?>