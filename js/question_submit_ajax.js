// This handles the single question submission
function singleQuestionSubmit(passData)
{
	
	var args = JSON.parse(passData);
	
	var questionID = args['questionID'];
	var saveResponse = args['saveResponse'];
	var showAnswer=true;
	var userID = args['userID'];
	var uniqueKey = args['randomKey'];
	var qType = args['qType'];
	var correctFeedback = args['correctFeedback'];
	var incorrectFeedback = args['incorrectFeedback'];
	var optionOrder = args['optionOrder'];
	var showCorrect = args['showCorrect'];
	
	var thisElementID = 'qResponse_'+questionID+'_'+uniqueKey;

	var questionDivID = 'ek-question-'+questionID+'-'+uniqueKey;		

	var questionButtonID = 'ekQuizButtonWrap_'+questionID+'_'+uniqueKey;


	var userResponse = "";
	
	
	// Hide the Button or not
	switch(qType)
	{
		case "singleResponse":	
		case "multiResponse":
		case "multiBlanks":
		
		document.getElementById(questionButtonID).innerHTML = "<br/>Submitting...<br/>";
		
		break;


	}		
		
	
	
	

	
	
	
	
	
	// Get the user response
	switch(qType)
	{
		case "singleResponse":
			// Get the value of the checked radio item
			var radioGroupName = "qResponses_"+questionID+"_"+uniqueKey;
			userResponse = jQuery('input[name='+radioGroupName+']:checked').val();	
		
		break;
		
		case "multiResponse":
			// Get the value of the checked radio item
			var radioGroupName = "qResponses_"+questionID+"_"+uniqueKey;
			
			
			// Create an array of the checkboxes that are checked
			userResponse = jQuery('input[name='+radioGroupName+']:checked').map(function () {
				return this.value;
			}).get();
		break;
		
		case "multiBlanks":
			var multiBlankName = "multiBlankInput_"+questionID+"-"+uniqueKey;
				
			//console.log ("multiBlankName = "+multiBlankName);
			userResponse = jQuery("input[name="+multiBlankName+"]").map(function() {
				return this.value;
			}).get();
	
		
		break;
		
		case "reflectiveText":

			var textareaID = "reflection_"+questionID+"-"+uniqueKey;
			
			if(saveResponse==true)
			{		
				// Force TINY MCE editor to return correct value
				tinyMCE.triggerSave();	
				var userResponse = document.getElementById(textareaID).value;
				//console.log("userResponse = "+userResponse);
			}				
			
			
			// Create div ID of the feedback
			var reflectiveFeedbackDiv = 'reflectionFeedback_'+questionID+'-'+uniqueKey;
			var reflectiveSavedFeedbackDiv = 'reflectionSavedFeedback_'+questionID+'-'+uniqueKey;
	
		
		break;		
		
		default:
			userResponse = jQuery("#"+thisElementID).val();
		break;
	}
	
	
	
	//console.log("thisElementID="+thisElementID);
	//console.log("userResponse="+userResponse);	
	//console.log("qType="+qType);
	//console.log("saveResponse="+saveResponse);
	
	//console.log("userResponse = "+userResponse);
	//console.log("saveResponse = "+saveResponse);
	
	jQuery.ajax({
		type: 'POST',
		url: submitQuestionAjax_params.ajaxurl,
		data: {			
			"action"			: "submitResponse",
			"userID"			: userID,
			"questionID"		: questionID,
			"saveResponse"		: saveResponse,
			"showAnswer"		: showAnswer,
			"showCorrect"		: showCorrect,
			"userResponse"		: userResponse,
			"correctFeedback" 	: correctFeedback,
			"incorrectFeedback"	: incorrectFeedback,
			"qType"				: qType,
			"optionOrder"		: optionOrder,
			"security"			: submitQuestionAjax_params.ajax_nonce
		},
		success: function(data){
			
			//console.log("questionDivID ="+questionDivID);
			var tryAgainStr = '<div class="qtl_redo_button" id="redo_button_'+questionID+'_'+uniqueKey+'"><a href="javascript:redrawQuestion('+questionID+', \''+uniqueKey+'\')"><i class="fas fa-redo-alt"></i> Reload Question</a></div>';
			
			// Replace the Question with the Feedback UNLESS its reflectiveText
			switch(qType)
			{
				case "reflectiveText":
				if(saveResponse==true)
				{
					jQuery ( "#"+reflectiveSavedFeedbackDiv ).show("fast");
				}
	
				document.getElementById(reflectiveFeedbackDiv).innerHTML = data;						
				jQuery ( "#"+reflectiveFeedbackDiv ).show("fast");
				
				
				break;
				
				default:
			
					document.getElementById(questionDivID).innerHTML = data + tryAgainStr;
					
				break;
			}
			
			
		}
			
	});
	
	
	
	return false;		
	
}


function redrawQuestion(questionID, uniqueKey)
{
	var questionDivID = 'ek-question-'+questionID+'-'+uniqueKey;	
	var redoButtonDiv = 'redo_button_'+questionID+'_'+uniqueKey;
	
	document.getElementById(redoButtonDiv).innerHTML = "Loading...";
	// get the value of the button text
	var buttonText =   document.getElementById("buttonText-"+questionID+"-"+uniqueKey).value 

// See what 	
	
	jQuery.ajax({
		type: 'POST',
		url: submitQuestionAjax_params.ajaxurl,
		data: {			
			"action"			: "redrawQuestion",
			"questionID"		: questionID,
			"randomKey"			: uniqueKey,
			"buttonText"		: buttonText,
			"security"			: submitQuestionAjax_params.ajax_nonce
		},
		success: function(data){	
			document.getElementById(questionDivID).innerHTML = data;
		
				
		}
			
	});
	
	
	
	

}