/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Alert module javascript code
* 
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/





$(function(){

	$('#myModal').on('show', function(){ //subscribe to show method

		//get module and time period from html table
	    var module = $(event.target).closest('td').data('module');
	    var time = $(event.target).closest('td').data('time');

		//get the data for 1 hour starting at calling time
		thedata = getTimeSlotAlert(module,time,1);

		//parse the data
		var data = "";
		var htmldata = "";

		for (index = 1; index < thedata.length; ++index) {
			
			htmldata = thedata[index];
			data = data + htmldata;
		}

		//write to modal
		$(this).find('.modal-title').html($(thedata[0]));
		$(this).find('.modal-body').html($(data));

	});
});



/* 
 * note that when returning html strings need to 
 * start with < or they are not recognized
 */

function getTimeSlotAlert(module,time,span) {

	//adjust php time to javascript time
	time = time * 1000;

	startTime = time;
	//console.log('Start time: ' ,getHumanDate(startTime));

	var endTime = startTime;
	var h = span;

	endTime = startTime + (h*60*60*1000); 
	//console.log('End time: ' ,getHumanDate(endTime));

	var thedata = [];
	thedata[0] = '<strong>' + module + ' alerts</strong> <strong>from</strong> ' 
			   + getHumanDate(startTime) + ' <strong>to</strong> ' 
			   + getHumanDate(endTime) + '<br>';

	//console.log(thedata);

	var loop = 1;
	for (var i = 0; i < alertData[module].length; ++i) {

		//get time for dataset
		getTime = alertData[module][i][0] * 1000;

		dateTime = new Date(getTime);

		if ( (getTime >= startTime) && (getTime < endTime)  ) {

			var theAlert = jQuery.parseJSON(alertData[module][i][2]);
			
			theAlertTime = getHumanDate(getTime);

			thedata[loop] =  '<span style="color:#4F2817;"><strong>' + theAlertTime + '</strong>  '  

							+ '<strong>Alert</strong> ' + theAlert[0][0] + ' '
							+ '<strong>Trigger set at</strong> ' + theAlert[0][1] + ' '
							+ '<strong>Value recorded</strong> ' + theAlert[0][2] + ' ';

			if (  theAlert.length > 1 ) {
				thedata[loop] = thedata[loop] + '<br><span style="color:#4F2817;"><strong>' + theAlertTime + '</strong>  ' 
											  + '<strong>Alert</strong> ' + theAlert[1][0] + ' '
											  + '<strong>Trigger set at</strong> ' + theAlert[1][1] + ' '
											  + '<strong>Value recorded</strong> ' + theAlert[1][2] + ' ';
			}

			thedata[loop] = thedata[loop] + '<span><br>';

			
			loop++;
		}

	}

	return thedata;


}
 
function getHumanDate(time) {

    var currentTime = new Date(time);

    //var tz_offset = currentTime.getTimezoneOffset()/60;

    var hours = currentTime.getHours();
    var minutes = currentTime.getMinutes();
    var ampm = "";

    if (minutes < 10) minutes = "0" + minutes;

    if(hours > 12) { hours = hours - 12; ampm = "pm"; }
        else ampm = "am";

    var browserTime = hours + ":" + minutes + " " + ampm;

    return browserTime;

}

	