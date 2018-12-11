<?php
$ekPots_CPT = new ekPots_CPT();
class ekPots_CPT
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
		
		/* Add Metaboxes */
		add_action( 'add_meta_boxes_ek_pot', array( $this, 'addMetaBoxes' ));
		
		// Remove and add columns in the admin table
		add_filter( 'manage_ek_pot_posts_columns', array( $this, 'my_custom_post_columns' ), 10, 2 );		
		add_action('manage_ek_pot_posts_custom_column', array($this, 'customColumnContent'), 10, 2);
		
		
		// Hook to admin_head for the CSS to be applied earlier - removes everything but 'publish' for the CPT
		add_action('admin_head-post.php', array($this, 'hide_publishing_actions') );
		add_action('admin_head-post-new.php', array($this, 'hide_publishing_actions'));		
		
		// Customise layout of the posts list table
		add_filter( 'admin_head-edit.php', array($this,  'potAdminListFunctions' ));
		
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
			'name'               =>  'My Question Pots',
			'singular_name'      =>  'Question Pot',
			'menu_name'          =>  'Quiz Tool Pro',
			'name_admin_bar'     =>  'Questions',
			'add_new'            =>  'Add New Pot',
			'add_new_item'       =>  'Add New Question Pot',
			'new_item'           =>  'New Question Pot',
			'edit_item'          =>  'Edit Question Pot',
			'view_item'          => 'View Question Pots',
			'all_items'          => 'Question Pots',
			'search_items'       => 'Search Question Pots',
			'parent_item_colon'  => '',
			'not_found'          => 'No pots found.',
			'not_found_in_trash' => 'No pots found in Trash.'
		);
	
		$args = array(
			'menu_icon' => 'dashicons-forms',		
			'labels'             	=> $labels,
			'public'             	=> false,
			'publicly_queryable' 	=> false,
			'exclude_from_search'	=> true,			
			'show_ui'           	=> true,
			'show_in_nav_menus'		=> false,
			'show_in_menu'      	=> true,
			'query_var'         	=> true,
			'rewrite'           	=> false,
			'capability_type'   	=> 'post',
			'has_archive'      		=> true,
			'hierarchical'      	=> false,
			'menu_position'     	=> 65,
			'supports'          	=> array( 'title'  )
			
		);
		
		register_post_type( 'ek_pot', $args );
		//remove_post_type_support('ek_pots', 'editor');		
	}
	
	
	
	// Register the metaboxes on  CPT
	function  addMetaBoxes()
	{	
		// Get the pot status. Only show this metabox if its been published
		global $post;
		$postID = $post->ID;
		$postStatus = get_post_status( $postID );

		if($postStatus=="publish")
		{

	
			//Add new question to this pot metabox etc
			$id 			= 'pot_meta';
			$title 			= 'Question Pot Information';
			$drawCallback 	= array( $this, 'drawInfoMetaBox' );
			$screen 		= 'ek_pot';
			$context 		= 'normal';
			$priority 		= 'default';

			
			add_meta_box( 
				$id, 
				$title, 
				$drawCallback, 
				$screen, 
				$context,
				$priority
			);	
		}
	}
	
	
	function drawInfoMetaBox($post, $callbackArgs)
	{
		// Get the question count and other information
		$potID = $post->ID;
		$args = array(
		"potID"=>$potID
		);
		
		// Set the Current Pot ID to this pot
		$_SESSION['currentPotID']=$potID;

		
		$myQuestions = ekQuiz_queries::getPotQuestions($args);
		$questionCount = count($myQuestions);
		echo 'You have <b>'.$questionCount.'</b> question(s) in this pot.<br/>';
		if($questionCount>=1)
		{
			echo '<br/><a href="edit.php?post_type=ek_question&potID='.$potID.'" class="button-secondary">View your questions</a>';
		}
		
		
		
		echo '<hr/><a href="admin.php?page=my-custom-submenu-page" class="button-primary">Add a question to this pot</a>';
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
		
		
	//	$columns['pot_name'] = 'Pot Name';	
		$columns['add_question'] = '';	
		$columns['questions'] = 'Questions';
		$columns['delete_pot'] = '';	
		return $columns;
	}	
	
	
	
	
	// Content of the custom columns for Topics Page
	function customColumnContent($column_name, $post_ID)
	{
		
		switch ($column_name)
		{
			
			
			case "questions":			
			
				// Get the number of questions
				$ekQuiz_queries = new ekQuiz_queries();
				$args = array("potID" => $post_ID);
				$potQuestions = $ekQuiz_queries->getPotQuestions($args);
				
				$questionCount = count($potQuestions);
			
				$questionListURL = get_admin_url().'edit.php?post_type=ek_question&potID='.$post_ID;
				
				echo $questionCount.' questions<br/>';
				//echo '<a href="'.$questionListURL.'">View Questions</a>';
			break;		
			case "add_question":
				$addQuestionURL = get_admin_url().'post-new.php?post_type=ek_question&potID='.$post_ID;
				
				$questionListURL = get_admin_url().'edit.php?post_type=ek_question&potID='.$post_ID;
				
				echo '<a href="'.$questionListURL.'" class="button-secondary">View Questions</a>';
				echo ' <a href="'.$addQuestionURL.'" class="button-primary">Add Question</a>';
			break;	
			
			case "delete_pot":
			echo '<a href="#">Delete Pot</a>';
			break;	
			
		}		
	}	
		
	// Hide everything but 'publish' from the publish box for notes
	function hide_publishing_actions()
	{
		global $post;
		if($post->post_type == "ek_pot")
		{
			echo '<link rel="stylesheet" href="'.EK_QUIZ_PLUGIN_URL.'/css/pot-admin.css" type="text/css" media="all" />';
		}
	}	
	
	
	function potAdminListFunctions()
	{
		// Add the custom CSS for this post type when on the edit screen
		$post_type = get_post_type();
		if($post_type == "ek_quiz")
		{
			echo '<link rel="stylesheet" href="'.EK_QUIZ_PLUGIN_URL.'/css/pot-admin.css" type="text/css" media="all" />';
		}	
		
		// Hide the 'Add new pot' from the sub menu as we onyl want top level CPTs
		global $submenu;
		unset($submenu['post-new.php?post_type=ek_pot'][10]);
		unset($submenu['edit.php?post_type=ek_pot'][10]);
		
		//remove_submenu_page( 'edit.php?post_type=ek_pot', 'post-new.php?post_type=ek_pot' );
	}
	
	
	
	// Remove the quick edit from this post type
	function custom_quick_links( $actions = array(), $post = null ) {
		// Abort if the post type is not "ek_question"
		if ( ! is_post_type_archive( 'ek_pot' ) ) {
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
	
	
	
	
	
} //Close class
?>