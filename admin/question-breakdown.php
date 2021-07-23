<?php
if ( ! defined( 'ABSPATH' ) )
{
	die();	// Exit if accessed directly
}

// Only let them view if admin
	if(!current_user_can('delete_pages'))
{
	die();
}
?>
<h1>Question Breakdown</h1>
<?php

$back_button = array(
    "url" => "edit.php?post_type=ek_quiz",
    "value" => "Back to quizzes",
);
$back_button = \icl_network\draw::back_button($back_button);
echo $back_button;
$quiz_id = $_GET['quiz-id'];

// Get ALL results and add the question IDs to an array
// We need to do this as individual question reults are only stored on single question stuff
// Because quizzes can be randomised, we need to do this for each quiz
$questions_attempted_array = array();
$quiz_attempts = ekQuiz_queries::getQuizResults($quiz_id);

foreach ($quiz_attempts as $attempt_info)
{

    $questions = unserialize($attempt_info['questionOrder']);
    $answers = unserialize($attempt_info['userResponses']);

    foreach ($questions as $queston_meta)
    {
        $q_type = $queston_meta[0]['qType'];
        $question_id = $queston_meta[0]['questionID'];

        $this_response = isset($answers[$question_id]) ? $answers[$question_id] : '';

        $questions_attempted_array[$question_id]['q_type'] = $q_type;
        $questions_attempted_array[$question_id]['responses']['all-responses'][] = $this_response;// add the answer if given

        // ADd the ttoals if single response or checkbox
        if($this_response)
        {
            switch ($q_type)
            {
                case "singleResponse":
                    $current_count = 0;
                    if(isset($questions_attempted_array[$question_id]['responses'][$this_response]) )
                    {
                        $current_count = $questions_attempted_array[$question_id]['responses'][$this_response];
                        $current_count = $current_count + 1;
                    }
                    else
                    {
                        $current_count = 1;
                    }
                    $questions_attempted_array[$question_id]['responses'][$this_response] = $current_count; // add the answer if given
                break;

                case "multiResponse":
                    //Convert the responses into an array as checkboxes can have nultiple answers
                    $responses = explode(",", $this_response);
                    $responses = array_filter($responses); // Remove blank values
                    foreach ($responses as $this_value)
                    {
                        $current_count = 0;
                        if(isset($questions_attempted_array[$question_id]['responses'][$this_value]) )
                        {
                            $current_count = $questions_attempted_array[$question_id]['responses'][$this_value];
                            $current_count = $current_count + 1;
                        }
                        else
                        {
                            $current_count = 1;
                        }
                        $questions_attempted_array[$question_id]['responses'][$this_value] = $current_count; // add the answer if given

                    }

                break;
            }

        }

    }
}



// Now go through the master array and count the number of answers for each question

foreach ($questions_attempted_array as $question_id => $question_info)
{
    $q_type =$question_info['q_type'];

    switch ($q_type)
    {
        case "singleResponse":
        case "multiResponse":
            $response_options = get_post_meta($question_id, "responseOptions", true);

            // Get the question
            $question = apply_filters('the_content', get_post_field('post_content', $question_id));


            // Go through the responses and draw the graph
            $chart_data = array();

            foreach ($response_options as $option_key => $option_info)
            {
                $option_value = $option_info['optionValue'];

                // Trim the words to 15
                $option_value = wp_trim_words( $option_value, 9, '...'); // trim the wors
                $is_correct = isset($option_info['isCorrect']) ? $option_info['isCorrect'] : '';

                if($is_correct==1)
                {
                    $bar_color = "#1b8210";
                }
                else
                {
                    $bar_color = "#a11d1d";
                }

                // Get the number of responses given
                $submitted_count_for_this = isset($questions_attempted_array[$question_id]['responses'][$option_key]) ? $questions_attempted_array[$question_id]['responses'][$option_key] : 0;
                $tooltip = $option_value.' : '.$submitted_count_for_this.' responses';
                $chart_data[] = array(  'value' => $submitted_count_for_this, 'label' => $option_value, 'tooltip' => $tooltip, 'backgroundColor'=> $bar_color, );

            }

            echo \icl_network\draw::content_box_open();
            echo '<h2>'.$question.'</h2>';

            $chart_args = array(
                'legend'    => false,
                'data'    => $chart_data,
            );

            echo '<div style="width:500px">';
            echo \icl_network\imperial_chart::draw( $chart_args, 'bar' );
            echo '</div>';


            echo \icl_network\draw::content_box_close();


        break;


    }



}



?>
