console.log("pot JS called");


// Start listening on page load
jQuery(document).ready(function() {

    var submitForm = function(method){
        //set form action based on the method passed in the click handler, update/delete
        var formAction = 'options.php?page=ek-pot-search';

        //set form action
        jQuery('#posts-filter').attr('action', formAction); // Change the form action
        jQuery('#posts-filter').attr('method', 'post'); // CHange to POST instead of GET so it doesn't go to the options general page

        //submit form
        console.log(formAction);
        jQuery('#posts-filter').submit();
    };

    jQuery(document)
    .on('click', '#search-submit', function(){
        //set your method
        submitForm('search');

    })


    // Get the URL of the page
    var url = window.location.href;
    console.log(url);



});
