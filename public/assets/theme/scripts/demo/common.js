$(function () {
	$('.widget[data-toggle="collapse-widget"] .widget-body')
	.on('show', function(){
		$(this).parents('.widget:first').attr('data-collapse-closed', "false");
	})
	.on('hidden', function(){
		$(this).parents('.widget:first').attr('data-collapse-closed', "true");
	});
	
	$('.widget[data-toggle="collapse-widget"]').each(function()
	{
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