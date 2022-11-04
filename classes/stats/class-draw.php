<?php
class qtlStatsDraw
{


	static function drawOverallStats($CSV=false)
	{

		$html='';
		$csvArray = array();
		//dataTables js
		//qtl_utils::loadDataTables();

     // $startDate = $_POST['startDate'];
      //$endDate = $_POST['endDate'];

     // $startDate = "2019-03-01";
     // $endDate = "2019-04-01";



      $startDate = '';
      $endDate = '';
      $cohort = "all";

      if(isset($_POST['startDate']))
      {
         $startDate = $_POST['startDate'];
      }

      if(isset($_POST['endDate']))
      {
         $endDate = $_POST['endDate'];
      }


      if(isset($_POST['cohort']))
      {
         $cohort = $_POST['cohort'];
      }

      if($startDate=="")
      {
         return "Start Date Cannot be blank";
      }

      if($endDate=="")
      {
         return "End Date Cannot be blank";
      }


      // if cohort is anything other than all then get array of wordpress IDs for this cohort to check against
      if($cohort<>"all")
      {
         $studentList = imperialQueries::getStudentsByYOS($cohort);

         // Create array of WP IDs to check against
         $checkIDarray = array();

         foreach ($studentList as $userMeta)
         {
            $username = $userMeta['username'];
            $WPuserMeta = get_user_by( 'login', $username );

            if($WPuserMeta<>false)
            {
               $WPuserID = $WPuserMeta-> ID;
               $checkIDarray[] = $WPuserID;
            }
         }
      }

      $totalAttempts = 0;

      $dateArray = array();

      // Generate array of dates
      // Specify the start date. This date can be any English textual format
      $date_from = strtotime($startDate); // Convert date to a UNIX timestamp

      // Specify the end date. This date can be any English textual format
      $date_to = strtotime($endDate); // Convert date to a UNIX timestamp

      // Loop from the start date to end date and output all dates inbetween
      for ($i=$date_from; $i<=$date_to; $i+=86400) {
          $thisDate = date("Y-m-d", $i);
          $dateArray[$thisDate] = array();
      }


      $startDateObj = new DateTime($startDate);
      $startDateStr = $startDateObj->format('jS F, Y');
      $endDateObj = new DateTime($endDate);
      $endDateStr = $endDateObj->format('jS F, Y');

      $html.='<h1>Stats between '.$startDateStr.' and '.$endDateStr.'</h1>';
      $html.='<h3>Cohort Year : '.$cohort.'</h3>';
		$myData = qtlStatsQueries::getAllSubmissions($startDate, $endDate);

      $totalAttempts = count($myData); // We will recalculate this if the cohort is not all

      if($totalAttempts==0)
      {
         $html.= 'No attempts found for this period';
         return $html;
      }
      $qTypeLookupArray = qtlStatsQueries::getQuestionTypeArray();

      // Get an array of all q types with total question count
      $qTypeTotalArray =array_count_values($qTypeLookupArray);

      $totalGotItCorrect =0;
      $qTypesAttemptedArray = array(); // Create blank array to ckkep track of qtypes attempted
      $masterAttemptArray = array(); // Master user attempt lookup


      // Reset total attempts in case of cohort check
      $totalAttempts = 0;
		foreach($myData as $answerMeta)
		{

         $userID = $answerMeta['userID'];

         if($cohort<>"all")
         {
            if(!in_array($userID, $checkIDarray) )
            {
               continue; // Skip this user ID
            }
         }


         $questionID = $answerMeta['questionID'];
         $origDate = $answerMeta['dateSubmitted'];

         $thisQType = $qTypeLookupArray[$questionID];
         $attemptDate = date("Y-m-d", strtotime($origDate));
         $gotItCorrect = $answerMeta['gotItCorrect'];

         if($gotItCorrect == 1)
         {
            $totalGotItCorrect++;
         }

         // Get the qType based on lookup
         $qTypesAttemptedArray[$thisQType][] = array($attemptDate, $gotItCorrect);

         $masterAttemptArray[$userID][] = $attemptDate;

         $totalAttempts++; // incremenet total attempts
         $dateArray[$attemptDate][] =array ($userID, $gotItCorrect);

		}


      if($totalAttempts==0)
      {
         $html.= 'No attempts found for this period';
         return $html;

      }
      $uniqueUsers = count($masterAttemptArray);


      $html.='<div class="qtlStatsTablesTop">';
      $html.='<div><h2>Total Question Counts</h2>';
      $html.=  qtlStatsDraw::drawTotalQTypeCounts($qTypeTotalArray);
      $html.='</div>';
      $html.='<div><h2>Question Type Attempts for this period</h2>';
      $html.=qtlStatsDraw::drawQTypesAttemptedTable ($qTypesAttemptedArray);
      $html.='</div>';


      $html.='</div>';

      if($totalGotItCorrect==0)
      {
         $percentageCorrect = 0;
      }
      else
      {
         $percentageCorrect = round(($totalGotItCorrect / $totalAttempts)*100 ,1);
      }

      $html.='<h2>General Stats for this period</h2>';
      $html.= 'Total Attempts : '.$totalAttempts.'<hr/>';
      $html.= 'Percentage Attempts Correct : '.$percentageCorrect.'%<hr/>';
      $html.= 'Unique Users  : '.$uniqueUsers.'<hr/>';


      $html.= '<div id="chart_div" style="height:500px;"></div>';

      $html.= '<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>';
      $html.= "<script>

      google.charts.load('current', {packages: ['corechart', 'bar']});
   google.charts.setOnLoadCallback(drawBasic);

function drawBasic() {

      var data = google.visualization.arrayToDataTable([

         ";

         $html.= "   ['Date', 'AttemptCount'],";


              foreach ( $dateArray as $thisDate => $attempts)
              {


                 $attemptCount = 0;
                 if(is_array($attempts))
                 {
                    $attemptCount = count($attempts);
                 }

                 $html.= "['".$thisDate."', ".$attemptCount."],";

              }
           $html.= " ]);

      var options = {
        title: 'Question Attempts Over Time',
        hAxis: {
          title: 'Date',
          textStyle: {fontSize: 7},
          slantedText: true,
          slantedTextAngle: 90,

        },
        vAxis: {
          title: 'Attempts',
           format: '0',


       },
       legend: {position: 'none'}
      };

      var chart = new google.visualization.ColumnChart(
        document.getElementById('chart_div'));

      chart.draw(data, options);
    }

    </script>";


		if($CSV==true)
		{
			return $csvArray;
		}
		else
		{
			return $html;
		}

	}

   public static function drawTotalQTypeCounts($qTypeTotalArray)
   {


      // Get total question count
      $totalCount = 0;
      foreach ($qTypeTotalArray as $thisCount)
      {
         $totalCount = $totalCount + $thisCount;
      }

      $qTypesNameArray = qtlStatsQueries::getQTypesNameArray();


      $str = '<table class="ekQTL_stat_table"><tr><th>Question Type</th><th>Question Count</th><th>Percent of total</th></tr>';
      foreach ($qTypeTotalArray as $qType => $thisCount)
      {
         $percentTotal = round(($thisCount/$totalCount)*100, 1);
         $str.= '<tr>';
         $str.= '<td>'.$qTypesNameArray[$qType].' </td>';
         $str.='<td><strong>'.$thisCount.'</strong></td>';
         $str.='<td><strong>'.$percentTotal.'%</strong><td/>';
         echo '</tr>';
      }
      $str.= '</table>';
      return $str;

   }

   public static function drawQTypesAttemptedTable ($qTypesAttemptedArray)
   {

      $str='';
      $qTypesNameArray = qtlStatsQueries::getQTypesNameArray();

      $str.='<table class="ekQTL_stat_table"><tr><th>Question Type</th><th>Total Attempts</th><th>Total Correct</th><th>Percent Correct</th></tr>';

      foreach ($qTypesAttemptedArray as $qType => $attemptMeta)
      {
         $qTypeName = $qTypesNameArray[$qType];

         $str.= '<tr><td>'.$qTypeName.'</td>';

         $thisQTypeAttemptCount = 0;
         $thisQTypeAttemptCorrect = 0;
         foreach ($attemptMeta as $thisAttempt)
         {

            $thisQTypeAttemptCount++;
            $tempCorrect = $thisAttempt[1];
            if($tempCorrect==1)
            {
               $thisQTypeAttemptCorrect++;
            }
         }


         if($thisQTypeAttemptCorrect==0)
         {
            $thisPercentCorrect = 0;
         }
         else
         {
            $thisPercentCorrect = round(($thisQTypeAttemptCorrect/$thisQTypeAttemptCount)*100, 1);
         }
         $str.= '<td>'.$thisQTypeAttemptCount.'</td><td>'.$thisQTypeAttemptCorrect.'</td><td>'.$thisPercentCorrect.'%</td></tr>';

      }

      $str.='</table>';


      return $str;

   }



}


?>
