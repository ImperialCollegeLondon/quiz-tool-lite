var SKL = {
	
	
	get_next_key: function () {
		var key = SKL.next_key;
		SKL.next_key += 1;
		
		console.log('Next= '+SKL.next_key);
		jQuery('#next_key').val( SKL.next_key );
		return key;
	},
				
	add_listeners_new_item: function () {
		jQuery('#add_new_option').on( 'click', function ( e ) {
			jQuery('#new_option_text').removeClass('inputError');
			SKL.add_option( e );
		});
		jQuery('#new_option_text').on( 'change', function ( e ) {
			jQuery('#add_option_feedback').empty();
		});
	},
	add_listeners_options: function () {
		
		// Listen for any clicks
		jQuery('#options_list_wrap').on( 'click', 'div', function ( e ) {
			SKL.click_option_item( e, this );
		});
		
		
		// Listen for delete clicks
		jQuery('.option-item-remove').on( 'click', function ( e ) {
			var thisOptionID = e.target.id;
			thisOptionID = thisOptionID.split('_');
			thisOptionID = thisOptionID[1];
						
			var parentID = "#response_option_wrap_"+thisOptionID;

			
			
			
			SKL.remove_option( e, parentID );
		});
		
		// Listen for toggles of advanced feedback
		jQuery('.option-feedback-toggle').on( 'click', function ( e ) {
			var thisOptionID = e.target.id;
			thisOptionID = thisOptionID.split('_');
			thisOptionID = thisOptionID[1];
						
			var feedbackDivID = "#option_feedback_"+thisOptionID;
			
			jQuery(feedbackDivID).slideToggle("fast");

		});		
		
		
		
		
		
		
	},
	
	
	
	remove_listeners_options: function () {
		jQuery('#options_list_wrap').off( 'click', 'div' );
		jQuery('.option-item-remove').off( 'click');
		jQuery('.option-feedback-toggle').off( 'click');

	},
	
	
	
	// The main function for clicking an item
	click_option_item: function ( e, element ) {
		        
		
		// If correct answer is checked then look at qType and if its single response then remove other ticks
		var clickedID = e.target.id;
		
		// See if the ID contains _isCorrect meaning its a checkbox
		if(clickedID.indexOf("_isCorrect") !== -1)
		{
			//console.log(clickedID);
			// Get the Option ID by splitting element name before _ and removing 'option' text
			var thisOptionID  = clickedID.split('_');
			thisOptionID =thisOptionID[0].slice(6);
			//console.log(thisOptionID);
			
			// See if the question type is single response and remove all other correct classes if sort
			var qType = jQuery ( "#qType" ).val();
			
			//console.log('this QTtyp = '+qType);
			var isOptionChecked = false;
			// Get the Value of clicked ID - i.e. checked or not
			if(jQuery('#'+clickedID).is(':checked'))
			{
				var isOptionChecked = true;
			}			
			
			if(qType=="singleResponse")
			{
				
			
					
				// Remove isCorrect class from all others
				jQuery("#options_list_wrap>div").removeClass("isCorrect");
				
				// Uncheck all other checkboxes
				jQuery('#options_list_wrap').find('input[type=checkbox]:checked').removeAttr('checked');
				
				// Finally CHECK the one ticked and add correct class
				if(isOptionChecked==true)
				{
					// Add isCorrect css to parent div
					jQuery('#'+clickedID).closest(".option-item").addClass('isCorrect');
					jQuery('#'+clickedID).prop( "checked", true );
				}	
				
			}
			else
			{
				//console.log("called");
				// Get the Value of clicked ID and if its CHECKED then add that 
				if(isOptionChecked==true)
				{
					// Add isCorrect css to parent div and check the checkbox
					jQuery('#'+clickedID).closest(".option-item").addClass('isCorrect');
					jQuery('#'+clickedID).prop( "checked", true );
				}
				else
				{
					// Add isCorrect css to parent div and uncheck the checkbox
					jQuery('#'+clickedID).closest(".option-item").removeClass('isCorrect');
					jQuery('#'+clickedID).prop( "checked", false );
					
				}
			}
			
		}
		
		
	},
	
	add_option: function ( e ) {
		var html;
		var text = jQuery('#new_option_text').val();
		var isCorrect= false; // Set false by default
		
		if(jQuery('#new_is_correct').is(':checked'))
		{
			isCorrect = true;
		}
		
		//console.log("isCorrect="+isCorrect);
		if ( ! text ) {
			jQuery('#add_option_feedback').empty().append('<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Please add some text');
			jQuery('#new_option_text').addClass('inputError');
		} else {
			
			html = SKL.new_list_item( SKL.get_next_key(), text, isCorrect );
	
			
		}
		e.preventDefault();
		
		
	},
	remove_option: function ( e, element ) {
		jQuery( element ).slideUp( 300, function () {
			jQuery( element ).remove();
			SKL.renumber();
		});
		e.preventDefault();
	},
	
	new_list_item: function ( key, text, isCorrect ) {
		
		
		console.log("key = "+key);
		var html = ''; 
		var qType = jQuery ( "#qType" ).val();
		
		// Create a JS var indicating if qType is text based i.e. no correct option
		var textBasedResponse=false;
		var textTypeAnswers = ["shortTextResponse", "multiBlanks"];
		if(jQuery.inArray(qType, textTypeAnswers) !== -1)
		{
			textBasedResponse=true;
		}
		var text = text || '';
		var isCorrect = isCorrect || '';
		//html += '<div class="option-item ';
		if(isCorrect==true)
		{
			//html +='isCorrect';
			
			if(qType=="singleResponse")
			{
			
				// Also remove all other checkbox checks if its single response
				jQuery('#options_list_wrap').find('input[type=checkbox]:checked').removeAttr('checked');
				//console.log("This is a new correct answer");
				
				
				// Remove isCorrect class from all others
				jQuery("#options_list_wrap>div").removeClass("isCorrect");	
			}
			
			
			// Clear the default checked for new items
			jQuery('#options_input_wrap').find('input[type=checkbox]:checked').removeAttr('checked');
			
			
			
		}
		

		
		jQuery.ajax({
			type: 'POST',
			url: quizAdminAjax.ajaxurl,
			data: {			
				"action"		: "drawNewResponseOption",
				"qType"		: qType,
				"isCorrect"	: isCorrect,
				"dataKey"	: key,
				"text"		: text,
				
			},
			success: function(data){
							
				console.log(data);
				jQuery('#options_list_wrap').append( data );
				SKL.renumber();
				
				SKL.remove_listeners_options(); 
				
				SKL.add_listeners_options();
				jQuery('#new_option_text').val('');
				SKL.renumber();						

				
			}
				
		});		
		

			
		
		return;
	},
	
	renumber: function () {
		
		jQuery('.option-display-number').each( function( index ) {
			jQuery( this ).text( index + 1 +'.' );
		});
	},
	
	init: function () {
		
		SKL.add_listeners_options();
		SKL.add_listeners_new_item();
		jQuery('#options_list_wrap').sortable({
			stop: function( event, ui ) {
				SKL.renumber();
			}
		});
	}
};
jQuery( document ).ready( function ()
{
	// Initialise the responses	
	SKL.init();
	
	// Get the INITIAL key from the question post meta
	var initial_key = parseInt(jQuery('#initial_key').val());
	
	//console.log("initial key = "+initial_key);
	
	// Pass this as the start key to the object
	SKL.next_key=initial_key;
	
	
});