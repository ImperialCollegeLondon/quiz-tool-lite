jQuery(document).ready(function($) {
	
	
	//Listener for time limit click
		$('#timeLimit').on( 'click', function (  ) {
		$( "#timeLimitOptions" ).toggle( "fast");
	});
	
	// Listen for partipant must be logged in click
		$('#loginRequired').on( 'click', function (  ) {
		$( "#loginRequiredOptions" ).toggle( "fast");
	});
	
	// LIstener for Use Question pots toggleuseQuestionPots
	$('#useQuestionPots').on( 'click', function (  ) {
		$( "#quizQuestionsList" ).show( "fast");
		$( "#questionsFromIDsDiv" ).hide( "fast");
	});
	
	// LIstener for manually adding questions
		$('#addQuestionsFromList').on( 'click', function (  ) {
		$( "#questionsFromIDsDiv" ).show( "fast");
		$( "#quizQuestionsList" ).hide( "fast");
	});
	
	// Listener for all questions per page
		$('#allQuestionsPerPage').on( 'click', function (  ) {
		$( "#questionsPerPageDiv" ).toggle( "fast");
	});
	
	
	
});