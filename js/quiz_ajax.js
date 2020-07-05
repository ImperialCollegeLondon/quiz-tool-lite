// Set global va for timer - this is set to false if the quiz is already submitted
var quizHasFinished = false;



// Start listening for click events in the quiz
function start_quiz_listen()
{

	//Listener for quiz start button pushed
	jQuery('.quizStartButton').on( 'click', function (  ) {
		// Get the ID of the button, turn to array with _ as split and second element is the quiz IDa
		var quizID = this.id.split("_")[1];
		//console.log("Start the quiz "+quizID);
		//var quizID = quizID[1];
		quizStart(quizID);
	});


	//Listener for page submit
	jQuery('#ek-nextpage-button').on( 'click', function (  ) {
		quizPageSubmit('forward')
	});

	//Listener for previous page submit
	jQuery('#ek-prevpage-button').on( 'click', function (  ) {
		quizPageSubmit('back')
	});

	//Listener for end quiz button
	jQuery('#ek-endquiz-button').on( 'click', function (  ) {
		jQuery('#quiz-finish-modal').show();
	});

	// LIstener for the close modal box
	jQuery('.close-modal').on( 'click', function (  ) {
		jQuery('#quiz-finish-modal').hide();
	});

	// Listener for confirming the quiz finish
	jQuery('#quizFinishConfirm, #quizTimerFinishConfirm').on( 'click', function (  ) {
        var quizID = jQuery(this).data("id");
		quizPageSubmit('finish', quizID);

		/* Hide the Modal Popups */
		jQuery('#quiz-finish-modal').hide();

		/* HIde the timer */
		jQuery('#quizTimeLimit').hide();

		/* HIde the timer modal */
		jQuery('#quiz-timer-finish-modal').hide();


	});


}
// Start listening on page load
jQuery(document).ready(function() {
	start_quiz_listen();
});
// And also listen after ajax calls
jQuery( document ).ajaxStop(function() {
	start_quiz_listen();
});

function quizStart(quizID)
{


    document.getElementById('ek-quizWrapper').innerHTML = "Please wait, loading...";

    // Create a quiz layout and Get the attempt ID
	jQuery.ajax({
		type: 'POST',
		url: quizPageAjax_params.ajaxurl,
		data: {
			"action"		: "initialise_quiz",
            "quizID"		: quizID,
			"security"		: quizPageAjax_params.ajax_nonce
		},
		success: function(attemptID){


            // Set the JS cookie

            qtl_cookie.set_cookie( "qtl_attemptID", attemptID, 1 );

            jQuery.ajax({
                type: 'POST',
                url: quizPageAjax_params.ajaxurl,
                data: {
                    "action"		: "startQuiz",
                    "quizID"		: quizID,
                    "attemptID"     : attemptID,
                    "security"		: quizPageAjax_params.ajax_nonce
                },
                success: function(data){


                    // Page Processed
                    document.getElementById('ek-quizWrapper').innerHTML = data;

                    // Also jump to top of page
                    //jQuery('html, body').animate({ scrollTop: 0 }, 'fast');

                    // Start the time but only if the quizTimeLimit div exists
                    if(jQuery("#quizTimeLimit" ).length != 0)
                    {
                        // Start the timer
                        startTimer(quizID);
                    }
                }
            });
        }

    });






}


function quizPageSubmit(direction="forward", quizID="")
{

    if(quizID=="")
    {
    	// Get the Quiz ID on the page
    	var quizID = document.getElementById('quizID').value;
    }




    // If it's forward or back get the current page
    switch(direction) {
        case "forward":
        case "back":
        // Get the current Page
    	   var currentPage = parseInt(document.getElementById('currentPage').value);
    	      pageToShow = "";
        break;

    }


	switch(direction) {
		case "forward":
			pageToShow = currentPage+1;
		break;

		case "back":
			pageToShow = currentPage-1;
		break;

		case "finish":
			pageToShow = "answers";

			// Stop the timer!
			quizHasFinished = true;

		break;
	}

	// Create an array of user responses to submit via AJAX adn save in database
	var userResponses = {}; // Create JS Object

	// Get the quiz question elements on this page and get the values
	var questionIDs = jQuery('#ek-quizWrapper .ek-question').map(function(){
	return jQuery(this).attr('id');
	})
	.get();


	var questionCount = questionIDs.length;
	for (var i = 0; i < questionCount; i++) {



		var thisIDStr = questionIDs[i];
		// Get the question ID and the qType
		var tempArray = thisIDStr.split("_");
		var thisQuestionID = tempArray[1];
		var thisQType = tempArray [2];

		//console.log("QID = "+thisQuestionID+" and qType = "+thisQType);

		var thisUserResponse = "";
		switch (thisQType)
		{
			case "shortTextResponse":

				// Get the value of the input containing ID qResponse_THIS-QID
				var thisInputIDsearch = 'qResponse_'+thisQuestionID;
				//console.log("Search for "+thisInputIDsearch);
				jQuery('*[id*='+thisInputIDsearch+']:visible').each(function() {

					var thisUserResponse = this.value;
					//console.log("thisUserResponse="+thisUserResponse);

					// Add the the JS object
					userResponses[thisQuestionID] = thisUserResponse;

				});

			break;


			case "singleResponse":

				// Get the value of the input containing ID qResponse_THIS-QID
				var thisInputIDsearch = 'response_'+thisQuestionID;
				//console.log("Search for "+thisInputIDsearch);
				jQuery('*[id*='+thisInputIDsearch+']:visible').each(function() {


					if(jQuery(this).is(':checked'))
					{
						var thisUserResponse = this.value;

						// Add the the JS object
						userResponses[thisQuestionID] = thisUserResponse;
					}

				});

			break;

			case "multiResponse":

				// Get the value of the input containing ID qResponse_THIS-QID
				var thisInputIDsearch = 'response_'+thisQuestionID;
				//console.log("Search for "+thisInputIDsearch);
				var tempList = "";
				jQuery('*[id*='+thisInputIDsearch+']:visible').each(function() {


					if(jQuery(this).is(':checked'))
					{
						var thisUserResponse = this.value;
						//console.log("thisUserResponse="+thisUserResponse);
						// Add the the JS object
						tempList =  thisUserResponse + "," + tempList;
					}

				});

				// Add ALL check answers to the object
				userResponses[thisQuestionID] = tempList;
				//console.log("tempList="+tempList);

			break;


			case "multiBlanks":

				var thisInputIDsearch = 'blank_'+thisQuestionID;
				//console.log("Search for "+thisInputIDsearch);
				var tempList = "";
				//var tempArray = {};
				jQuery('*[id*='+thisInputIDsearch+']:visible').each(function() {


					var thisUserResponse = this.value;

					//console.log("thisUserResponse="+thisUserResponse);
					// Add the the JS object
					tempList +=   thisUserResponse + ",";


				});

				// Remove the last cahracter from the string i.e. comma
				 tempList = tempList.substring(0, tempList.length - 1);
				//console.log(tempArray);

				//console.log("tempList="+tempList);
				// Add ALL check answers to the object
				// COnvert to array and reverse - for some reason it reads in reverse order



				userResponses[thisQuestionID] = tempList;

			break;

		}


	}


    // Display the wait text whlie the ajax call is made
    document.getElementById('ek-quizWrapper').innerHTML = "Please wait, loading...";


    // Get the atmpet ID as well
    var attemptID = qtl_cookie.get_cookie( "qtl_attemptID");


	jQuery.ajax({
		type: 'POST',
		url: quizPageAjax_params.ajaxurl,
		data: {
			"action"		: "quizPageSubmit",
			"quizID"		: quizID,
			"userResponses"	: userResponses,
			"pageToShow"	: pageToShow,
            "attemptID"     : attemptID,
			"security"		: quizPageAjax_params.ajax_nonce
		},
		success: function(data){

			//console.log("DATA = "+data);


			var dataObj = JSON.parse(data);

			var pageStr = dataObj.pageStr;
			// If its the answers page AND they have a redirection URL then redirect
			if(pageToShow=="answers")
			{
				var completionRedirectURL = dataObj.completionRedirectURL;
				if(completionRedirectURL)
				{
					window.location.href = completionRedirectURL;
					return;
				}
			}

			document.getElementById('ek-quizWrapper').innerHTML = dataObj.pageStr;

			jQuery('html, body').animate({ scrollTop: 0 }, 'fast');// Also jump to top of page
		}

	});



	return false;

}



function startTimer(quizID)
{

	//console.log("called"+quizID);

	jQuery.ajax({
		type: 'POST',
		url: quizPageAjax_params.ajaxurl,
		data: {
			"action"		: "getTimer",
			"quizID"		: quizID,
			"security"		: quizPageAjax_params.ajax_nonce
		},
		success: function(data){

			var quizMinutes = parseInt(data); // Get the quiz timer minutes from ajax call (
			//console.log("quizMinutes='"+data+"'");

			// UNHIDE the timer div if it exists and start the timer
			jQuery('#quizTimeLimit').show();


			/* Finally Start the time and show it */
			// Set the date we're counting down to

			//quizMinutes=1;


			var countDownDate = new Date();
			countDownDate.setMinutes(countDownDate.getMinutes() + quizMinutes);

			// Update the count down every 1 second
			var x = setInterval(function() {
				// Get todays date and time
				var now = new Date().getTime();
				// Find the distance between now an the count down date
				var distance = countDownDate - now;
				// Time calculations for days, hours, minutes and seconds
				var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
				var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
				var seconds = Math.floor((distance % (1000 * 60)) / 1000);
				// Display the result in the element with id="quizTimeLimitRemaining"
				var timer_str="";
				if(hours>=1)
				{
				  timer_str =  hours + "h " + minutes + "m " + seconds + "s ";
				}
				else
				{
				  timer_str =   minutes + "m " + seconds + "s ";
				}
				document.getElementById("quizTimeLimitRemaining").innerHTML = timer_str;


				if(quizHasFinished==true)
				{
					clearInterval(x);

				}

				// If the count down is finished, write some text
				if (distance <= 0) {
				clearInterval(x);


				// Only show the popup if ths quiz has yet to be submitted

				if(quizHasFinished==false)
				{

					document.getElementById("quizTimeLimitRemaining").innerHTML = "Finished";
					jQuery('#quiz-timer-finish-modal').show();
				}




				}
			}, 1000);
		}

	});





}




// Set and get cookies
var qtl_cookie = {
    set_cookie: function ( name, value, days ) {
        var date, expires = '';
        if ( days ) {
            date = new Date();
            date.setTime( date.getTime() + ( days*24*60*60*1000 ) );
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + value + expires + "; path=/";
        return this.get_cookie( name );
    },

    get_cookie: function ( name ) {
        var i, cookie, value = false, cookies = document.cookie.split('; '), l = cookies.length;
        for ( i = 0; i < l; i += 1 ) {
            cookie = cookies[ i ].split('=');
            if ( name === cookie[0] ) {
                value = cookie[1];
            }
        }
        return value;
    }
}
