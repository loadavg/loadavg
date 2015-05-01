$(function () {
	$("#minDate, #maxDate").datepicker({
		showOtherMonths:true, 
		showWeek: true, 
		minDate: new Date( $( '#minDateValue' ).val( ) ), 
		maxDate: new Date( $( '#maxDateValue' ).val( ) )
	});
});