<?php

	if ( ! defined( 'ABSPATH' ) )
	{
		die();	// Exit if accessed directly
	}

	// Only let them view if admin
	if(!current_user_can(get_option('min_quiz_access_level', 'manage_options')))
	{
		die();
	}


    $s = '';

    if(isset($_POST['s']) )
    {
        $s = $_POST['s'];

    }



    ?>
    <div class="wrap">
    <h1>Search results</h1>
    <a href="edit.php?post_type=ek_pot" class="backIcon">Return to my pots</a>

    <form action="options.php?page=ek-pot-search" method="post" >
    <p>
	<label class="screen-reader-text" for="post-search-input">Search Questions and Pots:</label>
	<input type="search" id="post-search-input" name="s" value="<?php echo $s;?>">
		<input type="submit" id="search-submit" class="button" value="Search Questions and Pots"></p>

    </form>
    <?php

    echo '<h2>Searching all pots and all questions</h2>';

    $results_array = array();


   if($s)
    {

        echo 'Search results for "'.$s.'"<hr/>';

        $args = array(
          'numberposts' => -1,
          'post_type'   => 'ek_pot',
          's'           => $s,
        );

        $pot_results = get_posts( $args );
        $pot_result_count = count($pot_results);

        foreach ($pot_results as $potInfo)
        {
            $ID =  $potInfo ->ID;
            $title = $potInfo ->post_title;
            $results_array[] = array(
                'ID'    => $ID,
                'type'  => 'pot',
                'title' => $title,
            );
        }

        // Also get the qui questions
        $args = array(
          'numberposts' => -1,
          'post_type'   => 'ek_question',
          's'           => $s,
        );

        $question_results = get_posts( $args );
        $question_result_count = count($question_results);


        foreach ($question_results as $questionInfo)
        {
            $ID =  $questionInfo ->ID;
            $title = $questionInfo ->post_title;
            $qType = get_post_meta ( $ID, 'qType', true);
            $thisClass = 'ek_'.$qType;

            $parentID = wp_get_post_parent_id($ID);

            $parentTitle = get_the_title($parentID);

            $qTypeMeta = $thisClass::questionMeta();
            $questionType =  $qTypeMeta['qString'];
            $results_array[] = array(
                'ID'    => $ID,
                'type'  => 'question',
                'title' => $title,
                'qType' => $questionType,
                'parentTitle'=> $parentTitle,
            );
        }

        echo'<table id="results_table">';
        echo '<thead><tr><th>Title</th><th>Type</th></tr><thead>';
        echo '<tbody>';
        foreach ($results_array as $item_info)
        {
            $title = $item_info['title'];
            $ID = $item_info['ID'];
            $type = $item_info['type'];
            $qType = 'Pot';

            $URL = 'post.php?post='.$ID.'&action=edit';



            if($type=="question"){$qType = $item_info['qType'];}
            echo '<tr>';
            echo '<td><a href="'.$URL.'">'.$title.'</a>';
            if($type=="question")
            {
                $parent = $item_info['parentTitle'];
            }
            echo '<br/>Pot : <strong>'.$parent.'</strong>';

            echo '</td>';
            echo '<td>'.$qType.'</td>';
            echo '</tr>';
        }
        echo '</tbody>';

        echo '</table>';

    }
    else
    {
        echo 'No search term found';
    }







?>
</div>
