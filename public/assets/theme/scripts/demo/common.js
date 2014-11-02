$(function () {
	$('.widget[data-toggle="collapse-widget"] .widget-body')
	.on('show', function(){
		$(this).parents('.widget:first').attr('data-collapse-closed', "false");
		    //console.log('open');
			storeState();
	})
	.on('hidden', function(){
		$(this).parents('.widget:first').attr('data-collapse-closed', "true");
		    //console.log('close');
			storeState();
	});
	
	$('.widget[data-toggle="collapse-widget"]').each(function()
	{
		//console.log('create');
		//console.log($(this).attr('data-collapse-closed'));

		// append toggle button
		$(this).find('.widget-head').append('<span class="collapse-toggle"></span>');
		
		// make the widget body collapsible
		$(this).find('.widget-body').addClass('collapse');
		
		// verify if the widget should be opened
		if ($(this).attr('data-collapse-closed') !== "true")
			$(this).find('.widget-body').addClass('in');
		
		// bind the toggle button
		$(this).find('.collapse-toggle').on('click', function(){
			$(this).parents('.widget:first').find('.widget-body').collapse('toggle');
		});
	});

});

//http://stackoverflow.com/questions/22811549/jquery-cookie-from-data-attributes-using-json-string

function storeState() {

    var check_open_divs = [];

    //mine
    var toggled_div = $('.widget[data-toggle="collapse-widget"]:not(.data-collapse-closed)');

    $(toggled_div).each(function() {
      check_open_divs.push($(this).attr('data-target'));
    });

    // stringify array object
    check_open_divs = JSON.stringify(check_open_divs);
    //console.log(check_open_divs);

    $.cookie('bg-noise-div-state', check_open_divs, {expires:365, path: '/'});
}



