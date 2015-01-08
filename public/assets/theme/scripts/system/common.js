

//used to get status of accordions - collapsed or visable
//from the loadUI cookie
//using code to manage accordion state is in common.js
//http://www.ridgesolutions.ie/index.php/2013/02/19/twitter-bootstrap-programmatically-open-or-close-an-accordion-with-javascript/

//fix icons with this if we want them
//http://stackoverflow.com/questions/18325779/bootstrap-3-collapse-show-state-with-chevron-icon


$(function () {

	$('div.accordion-body').on('shown', function () {

		//console.log( $(this).parents('.accordion:first').attr('data-collapse-closed') , 'open' );

		$(this).parents('.accordion:first').attr('cookie-closed', true);

	    //$(this).parent("div").find(".icon-chevron-down")
	    //       .removeClass("icon-chevron-down").addClass("icon-chevron-up");
	
		storeState();

	});

	$('div.accordion-body').on('hidden', function () {

		//console.log( $(this).parents('.accordion:first').attr('data-collapse-closed') , 'close' );

		$(this).parents('.accordion:first').attr('cookie-closed', false);
	    
	    //$(this).parent("div").find(".icon-chevron-up")
	    //       .removeClass("icon-chevron-up").addClass("icon-chevron-down");
	
		storeState();

	});
});


function storeState() {

	var loadCookie = "loadUIcookie";

    var check_open_divs = [];

    //mine
    var toggled_div = $('.accordion');

    $(toggled_div).each(function() {

    	var moduleName = $(this).attr('data-collapse-closed');

    	var status = $(this).attr('cookie-closed');
    	if (status == null)
    		status = "false";

    	check_open_divs.push(moduleName + "=" + status);

    });

    // stringify array object
    check_open_divs = JSON.stringify(check_open_divs);
    
    //console.log(check_open_divs);

    $.cookie(loadCookie, check_open_divs, {expires:365, path: '/'});
}
