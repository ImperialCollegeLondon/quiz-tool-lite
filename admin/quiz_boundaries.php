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
?>
<?php
$quizID = $_GET['quizID'];
$gradeBoundaries = get_post_meta($quizID, 'gradeBoundaries', true);
// If its not an array i.e. doesn't exist then crate blank array
$gradeBoundaries = is_array( $gradeBoundaries ) ? $gradeBoundaries : array();	
if(isset($_GET['action']))
{
	$action=$_GET['action'];
	
	switch ($action) {
		case "boundaryEdit":
		
			// Check the nonce before proceeding;	
			$retrieved_nonce="";
			if(isset($_REQUEST['_wpnonce'])){$retrieved_nonce = $_REQUEST['_wpnonce'];}
			if (wp_verify_nonce($retrieved_nonce, 'gradeBoundaryNonce' ) )
			{
			
				// Get the Existing grade boundaries and add to the meta
				$feedback = $_POST['feedback'];
				$minGrade = $_POST['minGrade'];
				$maxGrade = $_POST['maxGrade'];
				$boundaryID	= $_POST['boundaryID'];	
				$boundaryMeta = array(
					"feedback" => $feedback,
					"minGrade" => $minGrade,
					"maxGrade" => $maxGrade,
				);
				
				
				if(array_key_exists ( $boundaryID , $gradeBoundaries ))
				{
					$gradeBoundaries[$boundaryID] = $boundaryMeta;
				}
				else
				{
					$gradeBoundaries[] = $boundaryMeta;
				}
				
				// Update the meta
				update_post_meta( $quizID, 'gradeBoundaries', $gradeBoundaries  );
			}
			
			
			
			break;
			
		case "boundaryDelete":
			$boundaryID = $_GET['boundaryID'];
			////qtl_actions::gradeBoundaryDelete($boundaryID);
			//$feedback = '<div class="updated">Boundary Deleted</div>';
			unset ($gradeBoundaries[$boundaryID]);
			update_post_meta( $quizID, 'gradeBoundaries', $gradeBoundaries  );
				
			break;
	}	
	
}
// Sort the array so its in the correct order
uasort($gradeBoundaries, 'sortMultiArrayByValue');
// Reorder the boundaries by MIN GRADE
// The draw function that adds the button
function drawAddBoundaryRow($boundaryID, $quizID, $min, $max)
{
	echo '<tr>';
	echo '<td valign="top" colspan="4">';
	echo '<a href="options.php?page=ek-quiz-edit-boundaries&boundaryID='.$boundaryID.'&quizID='.$quizID.'&min='.$min.'&max='.$max.'" class="button-secondary">Add new boundary here</a>';
	echo '</td>';	
}
function sortMultiArrayByValue($a, $b) {
    return $a['minGrade'] - $b['minGrade'];
}
function get_next_boundary($array, $key)
{
   $currentKey = key($array);
   while ($currentKey !== null && $currentKey != $key) {
       next($array);
       $currentKey = key($array);
   }
   return next($array);
}
function get_prev_boundary($array, $key)
{
	
	while(key($array) !== $key) next($array);
	$prev_val = prev($array);
	return $prev_val;
}
?>
<h2>Grade Boundaries</h2>
<a href="edit.php?post_type=ek_quiz" class="backIcon">Return to my quizzes</a>
<?php
$graphDataLabels = array();
$dataArray = array();
$previousBoundaryArray=array
(
'minGrade' => '',
'maxGrade' => '',
);
$minGrade = '';
$maxGrade='';
// Show all the other grade boundaries now
$boundaryCount = count($gradeBoundaries);
if($boundaryCount==0)
{
	
	
	echo '<br/><br/>You currently have no grade boundaries defined';
	echo '<hr/><a href="options.php?page=ek-quiz-edit-boundaries&boundaryID=new&quizID='.$quizID.'&min=0&max=100" class="button-primary">Add new grade boundary</a>';
	
}
else
{
	
	echo '<div id="quiztable">';
	echo '<table><th>Grade Window</th><th>Feedback</th><th></th><th></th></tr>';
	
	$nothingDefinedText = "No boundary defined";
	$totalChartAreas = 0; // The total number of boundaries including not defined. Incremented to make the chart the right height
	$initialEmptyBoundary=false; // Set this true later if there is a missing boundary from the start
	
	$tempTotal=0;
	
	$currentBoundary=1;
	$previousMaxGrade=0; // set to 0 as no previous grade recored
	$prevMaxGrade = 0;
	
	foreach($gradeBoundaries as $boundaryID => $thisBoundary)
	{
		$minGrade = $thisBoundary['minGrade'];
		$maxGrade = $thisBoundary['maxGrade'];
		$feedback = $thisBoundary['feedback'];
		//$feedback = wpautop($thisBoundary);
		
		//$nextBoundaryInfo = get_next_boundary($gradeBoundaries, $boundaryID);
		
		$lastBoundaryIsBlank=false;
		
		// its the first boundary and there is space BEFORE the boundary for a new one
		// Therefore add the blank boundary to the data array
		if($currentBoundary==1 && $minGrade>0)
		{
			drawAddBoundaryRow("new", $quizID, 0, $minGrade-1);
			
			$nextBoundaryInfo = $gradeBoundaries[$boundaryID];
			
			$nextMinGrade=$nextBoundaryInfo['minGrade']-1;			
			// Determine the size of this blank boundary
			$thisDataValue = $minGrade;
			
			$dataArray[] = array(
			"boundaryID" => "new",
			"minGrade"		=> 0,
			"maxGrade"		=> $nextMinGrade,
			"thisValue"		=> $thisDataValue,
			"isBlank"		=> true,
			);
			
			$initialEmptyBoundary=true;
			$lastBoundaryIsBlank=true;
			
			
		}
		elseif($minGrade>($previousMaxGrade+1)) // Its a blank boundary in the MIDDLE of the table
		{
			$nextBoundaryInfo = $gradeBoundaries[$boundaryID];
			
			$nextMinGrade=$nextBoundaryInfo['minGrade']-1;				
			
			// Determine the size of this
			$thisDataValue = ($nextMinGrade-$previousMaxGrade)-1;
			
			$dataArray[] = array(
			"boundaryID" => "new",
			"minGrade"		=> $previousMaxGrade+1,
			"maxGrade"		=> $nextMinGrade,
			"thisValue"		=> $thisDataValue,
			"feedback"		=> "",
			"isBlank"		=> true,
			);
			
			// Also add boundary if the previous min value had a gap for another
			drawAddBoundaryRow("new", $quizID, $previousMaxGrade+1, $nextMinGrade);
			
			$totalChartAreas++;
			
			$lastBoundaryIsBlank=true;		
		}
		
		
		// First of the proper boundaries
		if($currentBoundary==1) // Its the FIRST of the boundaries
		{
			$nextBoundaryInfo = get_next_boundary($gradeBoundaries, $boundaryID);
			$nextBoundaryMin = $nextBoundaryInfo['minGrade'];
			$min=0;
			if($boundaryCount==1)
			{
				$max=100;
			}
			else
			{
				
				
				$nextMinGrade=$minGrade;
				$nextMaxGrade=$maxGrade;
				$max = $nextBoundaryMin-1;
				$min = 0;
				
			}
			
			
		}
		elseif($currentBoundary==$boundaryCount) // Its the LAST of the boundaries
		{
			$thisBoundaryArray = $gradeBoundaries[$boundaryID];
			$prevBoundaryInfo = get_prev_boundary($gradeBoundaries, $boundaryID);
			$prevBoundaryMax = $prevBoundaryInfo['maxGrade'];		
			
			$min=$prevBoundaryMax+1;
			$max=100;
			
		}
		else // Its a standard in the middle one
		{
			//$previousBoundaryArray = $myBoundaries[$currentBoundary-2];
			//$prevMinGrade=$previousBoundaryArray['minGrade'];
			//$prevMaxGrade=$previousBoundaryArray['maxGrade'];	
			$thisBoundaryArray = $gradeBoundaries[$boundaryID];
			$nextBoundaryInfo = get_next_boundary($gradeBoundaries, $boundaryID);
			$min=$thisBoundaryArray['minGrade'];
			$max=$nextBoundaryInfo['minGrade']-1;
			
						
		}
		
		if($minGrade==$maxGrade)
		{
			$gradeInfo = 'Exactly '.$minGrade.'%';
		}
		else
		{
			$gradeInfo = $minGrade.'% - '.$maxGrade.'%';
		}
		
		$graphDataLabels[] = $gradeInfo;
		
		echo '<tr>';
		echo '<td valign="top">'.$gradeInfo.'</td>';
		echo '<td valign="top">'.$feedback.'</td>';	
		echo '<td valign="top"><a href="?page=ek-quiz-edit-boundaries&boundaryID='.$boundaryID.'&quizID='.$quizID.'&min='.$min.'&max='.$max.'" class="editIcon"">Edit</a>';
		echo '</td>';
		echo '<td valign="top">';
		echo '<a href="#TB_inline?width=400&height=150&inlineId=deleteCheck'.$boundaryID.'" class="thickbox deleteIcon">Delete</a>';
		echo '<div id="deleteCheck'.$boundaryID.'" style="display:none">';
		echo '<div style="text-align:center">';
		echo '<h2>Are you sure you want to delete this grade boundary?</h2>';		
		echo '<input type="submit" value="Yes" onclick="location.href=\'?page=ek-quiz-boundaries&boundaryID='.$boundaryID.'&quizID='.$quizID.'&action=boundaryDelete\'" class="button-primary">';
		echo '<input type="submit" value="Cancel" onclick="self.parent.tb_remove();return false" class="button-secondary">';
		echo '</div>';
		echo '</div>';			
		echo '</td>';
		
		echo '</tr>';
		
		// Dealing with graph stuff
		// Regular boundary
		// Determine the width of the boundary
		$prevMinGrade=$previousBoundaryArray['minGrade'];
		
		//$prevMaxGrade=$previousBoundaryArray['maxGrade'];
		
		//if($prevMaxGrade==""){$prevMaxGrade=0;}
		
		//echo 'prevMaxGrade = '.$prevMaxGrade.'<hr/>';
		
		$thisDataValue = ($maxGrade-$minGrade);
		if(($prevMaxGrade+1)==$minGrade)
		{
			if($lastBoundaryIsBlank==false)
			{
				$thisDataValue=$thisDataValue+1;
			}
		}
			
		if($thisDataValue==0){$thisDataValue=1;}
		
		$dataArray[] = array(
		"boundaryID"	 => $boundaryID,
		"minGrade"		=> $min,
		"maxGrade"		=> $max,
		"thisValue"		=> $thisDataValue,
		"feedback"		=> $feedback,
		"isBlank"		=> false,
		);
		
		$lastBoundaryIsBlank=false;
		
		// Last boundary does not bring it up to 100%
		if($currentBoundary==$boundaryCount) 
		{
			// Add new boundry if its the last one and its NOT up to 100 yet
			if($maxGrade<100)
			{
				drawAddBoundaryRow("new", $quizID, $maxGrade+1, 100);
				$thisDataValue = (100-$maxGrade);
				
				//$dataArray[] = array("new", $maxGrade+1, 100, $thisDataValue, $feedback, true);
				
				$dataArray[] = array(
				"boundaryID"	 => "new",
				"minGrade"		=> $maxGrade+1,
				"maxGrade"		=> 100,
				"thisValue"		=> $thisDataValue,
				"feedback"		=> $feedback,
				"isBlank"		=> true,
				);	
				
				
				// Set the clour oft his bar to grey
				$totalChartAreas++;	
				
							
			}
			else
			{
				if($maxGrade==99)
				{
					$thisDataValue = 1; // it MUST be one as its the top mark of 100
					
				}
				else
				{
					$thisDataValue = 100-$maxGrade;
				}
					
			
				// Set the clour oft his bar to grey
				$totalChartAreas++;
				
			}
		}		
		
		// End this loop increment values
		$previousMaxGrade = $maxGrade;
		$currentBoundary++; // Up current boundary by one
		$totalChartAreas++; // Up total chart areas by one
		
	}
	
	echo '</table></div>';
	
	
	
	
	
	
	// Draw the Graph representation of this data
	$totalValue=0;
	$divHeight="50px";
	
	echo '<h3>Grade Boundaries Graphical Representation</h3>';
	echo '<div id="boundaryGraphWrapper">';
	echo '<div style="height:'.$divHeight.';  margin-right:10px;">0%</div>';
	
	$colourGradient= ekQuiz_utils::generateRedGreenColourArray($boundaryCount);
	
	$currentBoundary=0;
	
	foreach($dataArray as $boundaryData)
	{
		$isBlank='';
		$boundaryID =$boundaryData['boundaryID'];
		$min=$boundaryData['minGrade'];
		$max=$boundaryData['maxGrade'];		
		$thisValue = $boundaryData['thisValue'];
		$isBlank = $boundaryData['isBlank'];
		$divWidth = $thisValue*6;

		$feedback = '';
		if(isset($boundaryData['feedback']) )	
		{
			$feedback= $boundaryData['feedback'];
		}

		
		if($feedback=="")
		{
			$feedback = '<p>No Feedback Given</p>';
		}
		
		
		if($isBlank==true)
		{
			$divColour = '#ccc';
			$thisLink = "?page=ek-quiz-edit-boundaries&boundaryID=new&quizID=".$quizID."&min=".$min."&max=".$max;
			$label = 'Undefined Boundary - Click to create';
			$strippedFeedbackText = "";
		}
		else
		{
			$divColour='';
			$label='';
			if(isset($colourGradient[$currentBoundary])){$divColour = $colourGradient[$currentBoundary];}
			$thisLink = "?page=ek-quiz-edit-boundaries&boundaryID=".$boundaryID."&quizID=".$quizID."&min=".$min."&max=".$max;
			if(isset($graphDataLabels[$currentBoundary])){$label = $graphDataLabels[$currentBoundary];}
			
			
			$strippedFeedbackText = preg_replace('/<a href=\"(.*?)\">(.*?)<\/a>/', "\\2", $feedback);
			$currentBoundary++;
			
		}
		
		
		$feedback = $label.'<hr/>'.$strippedFeedbackText;
		
		
		
		echo '<a href="'.$thisLink.'" class="tooltip">';
		echo '<div style="border:1px solid #fff; height:'.$divHeight.'; width:'.($divWidth).'; background:'.$divColour.'; width:'.$divWidth.'px">';
		echo '<span>'.$feedback.'</span>';
		echo '</div></a>';
		
		$totalValue = $totalValue+$thisValue;
	}
	
	
	
	echo '<div style="height:'.$divHeight.'; margin-left:15px;">100%</div>';
	echo '</div>'; //Close the wrapper div
	
	echo '<br/><br/><br/><br><br/><br/><br/>';
	
}
?>