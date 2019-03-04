function ek_question_duplicate(targetQuestionID, targetPotID, currentPotID)	
{
	
	
	
	document.getElementById('duplicateOptions'+targetQuestionID).innerHTML = "Duplicating....";

	
	jQuery.ajax({
		type: 'POST',
		url: quizAdminAjax.ajaxurl,
		data: {			
			"action"			: "duplicateQuestion",
			"targetQuestionID" : targetQuestionID,
			"targetPotID"		: targetPotID,
			"security"			: quizAdminAjax.ajax_nonce
		},
		success: function(data){
			
		window.location.href = "edit.php?feedback=questionduplicated&post_type=ek_question&potID="+currentPotID+"&orderby=title&order=asc";

					
			
		}
			
	});
}