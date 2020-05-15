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



$qTypesArray = ekQuiz::getQuestionTypesArray();


$qCatArray = array();
echo '<h1>Choose your question type</h1>';
foreach ($qTypesArray as $qType => $qTypeMeta)
{


	$qButtonStr= '<a href="post-new.php?post_type=ek_question&qType='.$qType;


	// Set the PotID in the query string if it exsits in the session
	if(isset($_SESSION['currentPotID']) )
	{
		$currentPotID=$_SESSION['currentPotID'];
		if($currentPotID>=1)
		{
			$qButtonStr.='&potID='.$currentPotID;
		}
	}

	$qButtonStr.='">';
	$qButtonStr.= '<div class="qTypeChooserBox">';
	$qButtonStr.= $qTypeMeta['qString'];
	$qIcon = 'fa-check-square'; // Default Icon
	$qCat = "Other Question Types";
	$qCat = $qTypeMeta['qCat'];
	$qCatOrder = 0;
	if(isset($qTypeMeta['qCatOrder'] ) )
	{

		$qCatOrder = $qTypeMeta['qCatOrder'];
	}


	if(isset($qTypeMeta['qIcon'] ) )
	{

		$qIcon = $qTypeMeta['qIcon'];
	}


	//$icon = $qTypeMeta['icon'];
	//$qIcon = EK_QUIZ_PLUGIN_URL.'/question-types/'.$qType.'/icon.png';
	//$qIcon = EK_QUIZ_PLUGIN_URL.'/question-types/'.$qType.'/icon.png';

	/*

	$qButtonStr.='<div class="fa-4x">';
	$qButtonStr.='<span class="fa-layers fa-fw">';
	$qButtonStr.='<i class="fas fa-circle"></i>';
	$qButtonStr.='<i class="fa-inverse fas '.$qIcon.'" data-fa-transform="shrink-8" style="color:#fff"></i>';
	$qButtonStr.='</span>';
	$qButtonStr.='</div>';

	*/

	$qButtonStr.='<div class="fa-stack fa-3x" style="margin:10px;">';
	$qButtonStr.='<i class="fa fa-circle fa-stack-2x"></i>';
	$qButtonStr.='<i class="fa '.$qIcon.' fa-stack-1x fa-inverse fa-1x"></i>';
	$qButtonStr.='</div>';






	$qButtonStr.= '</div></a>';
	if($qCatOrder>=1)
	{
			$qCatArray[$qCat][$qCatOrder] = $qButtonStr;
	}
	else
	{
	$qCatArray[$qCat][] = $qButtonStr;
	}

}


ksort($qCatArray);

foreach ($qCatArray as $qCat => $qTypes)
{
	echo '<h2 class="qTypeChooseHeader">'.$qCat.'</h2>';


	ksort($qTypes);
	foreach($qTypes as $qString)
	{
		echo $qString;
	}
}
?>
