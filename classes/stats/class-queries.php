<?php
class qtlStatsQueries
{


	static function getAllSubmissions($startDate, $endDate)
	{
      global $wpdb;
		global $userResponsesTable;

		$SQL='Select * FROM '.$userResponsesTable.' Where dateSubmitted>= "'.$startDate.'" and dateSubmitted <= "'.$endDate.'" ORDER by dateSubmitted';
      $results =  $wpdb->get_results( $SQL, ARRAY_A );

		return $results;


   }


   static function getQuestionTypeArray()
   {
      $qTypeArray = array();


      $args = array(
         'posts_per_page'   => -1,
         'post_type'        => 'ek_question',
         'post_status'      => 'publish',
      );



      $posts_array = get_posts( $args );

      foreach ($posts_array as $questionMeta)
      {
         $questionID = $questionMeta->ID;
         $qType = get_post_meta($questionID, 'qType', true);

         $qTypeArray[$questionID] = $qType;
      }

      return $qTypeArray;
   }

   static function getQTypesNameArray()
   {
      $qTypesLookup = array
      (
         "multiBlanks" => "Fill in the blanks",
         "multiResponse" => "Multiple Response",
         "shortTextResponse" => "Short Text Response",
         "reflectiveText" => "Essay Style",
         "singleResponse" => "Single Response (SBA)",
      );

      return $qTypesLookup;
   }

}


?>
