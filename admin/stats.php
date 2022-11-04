<?php

echo '<h1>Stats Date Picker</h1>';
echo '<form method="post" action="edit.php?post_type=ek_pot&page=ek-quiz-stats&action=showAllStats">';
echo '<label for="startDate">Start Date:<br/>';
echo '<input type="text" id="startDate" name="startDate">';
echo '</label>';

echo '<br/>';
echo '<label for="endDate">End Date:<br/>';
echo '<input type="text" id="endDate" name="endDate">';
echo '</label>';
echo '<br/>';
echo '<hr/>';
echo '<label for="cohort">';
echo 'Student Group<br/>';
echo '<select name="cohort" id="cohort">';
echo '<option value="all">All Students</option>';
$i=1;
while ($i<=6)
{
   echo '<option value="'.$i.'">Y'.$i.'</option>';
   $i++;
}
echo '</select>';
echo '</label>';
echo '<input type="submit" value="Go" class="button-primary">';

echo '</form><hr/>';
if(isset($_GET['action']) )
{
   $myAction = $_GET['action'];

   switch ($myAction)
   {
      case "showAllStats":
         echo qtlStatsDraw::drawOverallStats();
      break;
   }
}
else
{
   echo '<h1>Stats</h1>';
}


?>
<script>
   jQuery(document).ready(function() {
      jQuery("#endDate").datepicker({
         dateFormat : "yy-mm-dd"
      });

   });

   jQuery(document).ready(function() {
      jQuery("#startDate").datepicker({
         dateFormat : "yy-mm-dd"
      });

   });
</script>
