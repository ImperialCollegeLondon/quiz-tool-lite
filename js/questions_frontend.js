// Start listening for click events in the quiz
function start_page_listen()
{
	//Listener for clicking an option
	jQuery('.responseOptionItem').on( 'click', function ( e ) {



		//console.log(e.target.tagName);
		if( e.target.tagName === "LI") {
			// Remove Selected from other children
			var parentUL = "#"+jQuery(this).closest('ul').attr('id');
			var qType =  jQuery(parentUL).attr("data-qtype");

			//console.log("qType = "+qType);

			if(qType=="singleResponse")
			{
				// Now remove all selected classa from the children
				jQuery(parentUL+" li").removeClass("selected");


				// Add Selected Class to this radio button
				jQuery( this ).children().addClass( "selected" );
			}

			if(qType=="multiResponse")
			{
				//console.log("called");
				// Now remove all selected classa from the children
				jQuery( this ).children().toggleClass( "selected" );
			}
		}




	});

}
// Start listening on page load
jQuery(document).ready(function() {
	start_page_listen();
});
// And also listen after ajax calls
jQuery( document ).ajaxStop(function() {
	start_page_listen();
});
