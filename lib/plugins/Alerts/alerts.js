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




var dynamicTable = (function() {
    
    var _tableId, _table, 
        _fields, _headers, 
        _defaultText,
        _warningThreshold;
    
    /** Builds the row with columns from the specified names. 
     *  If the item parameter is specified, the memebers of the names array will be used as property names of the item; otherwise they will be directly parsed as text.
     */
    function _buildRowColumns(names, item, itemnumber) {
        var row = '<tr class="selectable">';
        if (names && names.length > 0)
        {
            
            //in case there is no tiestamp data for some reason
			var timeStamp = 0;
            if (typeof chartArray[itemnumber+1].timeStamp != 'undefined') {
				timeStamp = chartArray[itemnumber+1].timeStamp;
            }

        	loop = 0;

            $.each(names, function(index, name) {
                var c = item ? item[name+''] : name;


                //first column here - time needs its own look                
                if (loop==0) {
                	row += '<td class="center" >';
                	row += '<span class="label label-info">' + c + '</span>';
                }
                //last column here - total needs its own look
                else if (loop == modules.length+1)
                {
                	row += '<td class="center" >';

					if ( item[name] == 0 ) {
                		row += '<span class="label label-warning"></span>';
                	}
                	else
                	row += '<span class="label label-info">' + c + '</span>';
                }
                //data columns here need to be tagged and get timestamp
            	else {

            		if ( item[name] == 0 ) {

	                	row += '<td>' 
                		row += '<span class="label label-warning"></span>';
                	
                	}
            		else  {

	                	row += '<td class="center" data-toggle="modal" data-target="#myModal" data-module="' 
	                		+   modules[loop-1] + '" data-time="' + timeStamp + '" >';

	                	if ( item[name] > _warningThreshold )
	                		row += '<span class="label label-warning">' + c + '</span>';
                		else
                			row += '<span class="label label-success">' + c + '</span>';                		

                	}


            	}

            	//close out row here
            	row += '</td>';

            	loop++;
            });
        }
        row += '<tr>';
        return row;
    }

    /** Builds the row with columns from the specified names. 
     *  If the item parameter is specified, the memebers of the names array will be used as property names of the item; 
     *  otherwise they will be directly parsed as text.
     */
    function _buildHeaderRowColumns(names, item) {
        var row = '<tr>';


    modules = getModules(chartModules);
    var totalModules = modules.length;

    //calculate column widths - need to add time and totals column
    //and we subtract width of time column from 100% first (gives 91)
    var columnWidth = 82 / (totalModules+1);


        if (names && names.length > 0)
        {
            $.each(names, function(index, name) {
                var c = item ? item[name+''] : name;

                //why dos this com up 2x ?
                //console.log (c);

                if (c=='time' || c=='Total')
                    row += '<th width=9%>' + c + '</th>';
                else
                    row += '<th width=' + columnWidth  +  '%>' + c + '</th>';

            });
        }
        row += '<tr>';
        return row;
    }


    /** Builds and sets the headers of the table. */
    function _setHeaders() {
        // if no headers specified, we will use the fields as headers.
        _headers = (_headers == null || _headers.length < 1) ? _fields : _headers; 
        
        var h = _buildHeaderRowColumns(_headers);

        if (_table.children('thead').length < 1) _table.prepend('<thead></thead>');
        _table.children('thead').html(h);

        //console.log(h, "_setHeaders");

    }
    
    function _setNoItemsInfo() {
    
        if (_table.length < 1) 
        	return; //not configured.
        
        var colspan = _headers != null && _headers.length > 0 ? 
            'colspan="' + _headers.length + '"' : '';
        
        var content = '<tr class="no-items"><td ' + colspan + ' style="text-align:center">' + 
            _defaultText + '</td></tr>';
        
        //var headercontent = '<tr class="no-items"><th ' + colspan + ' style="text-align:center">' + 
        //    _defaultText + '</th></tr>';

        if (_table.children('tbody').length > 0)
            _table.children('tbody').html(content);
        else 
        	_table.append('<tbody>' + content + '</tbody>');
    }
    
    function _removeNoItemsInfo() {
        var c = _table.children('tbody').children('tr');
        if (c.length == 1 && c.hasClass('no-items')) _table.children('tbody').empty();
    }
    
    return {
        /** Configres the dynamic table. */
        config: function(tableId, fields, headers, defaultText) {
            _tableId = tableId;
            _table = $('#' + tableId);
            _fields = fields || null;
            _headers = headers || null;
            _warningThreshold = 4;
            _defaultText = defaultText || 'No items to list...';
            _setHeaders();
            _setNoItemsInfo();
            return this;
        },
        /** Loads the specified data to the table body. */
        load: function(data, append) {
            if (_table.length < 1) 
            	return; //not configured.
            
            _setHeaders();

            _removeNoItemsInfo();

            if (data && data.length > 0) {
                var rows = '';
                
                	//console.log("data : " , data );

                var itemnumber = 0;

                $.each(data, function(index, item) {

                	//console.log("fields : " , _fields );
                	//console.log("item   : ", item);

                    rows += _buildRowColumns(_fields, item, itemnumber);

					itemnumber += 1;

                });

                var mthd = append ? 'append' : 'html';
                _table.children('tbody')[mthd](rows);
            }
            else {
                _setNoItemsInfo();
            }
            return this;
        },
        /** Clears the table body. */
        clear: function() {
            _setNoItemsInfo();
            return this;
        }
    };
}());


/*
 * function to parse modules data sent over from php
 * and filter out disabled modules (false)
 */

function getModules(data) {

    //console.log(data);

    var theModules = [];

    $.each(data, function(k, v) {

        if ( v == "true" ) {
            theModules.push(k);
        }
    });

    return theModules;

}


/*
 * function to build the chart headings
 */

function buildChartHeadings(data) {

    var headingFields = [];

    //add time headers
    headingFields.push( 'time' );

    //build centeral array of fields and titles
	var elements = 1;
	for ( key in data) {

		headingFields.push( elements );
		//console.log( elements );

		elements += 1;
	}

	//add last column
    headingFields.push( 'Total' );

    return headingFields;

}

/*
 * function to build the chart titles
 */

function buildChartTitles(data) {

    var headingTitles = [];

    //add time headers
    headingTitles.push( 'time' );

    //build centeral array of fields and titles
	var elements = 1;
	for ( key in data) {

		headingTitles.push( modules[key] );
		//console.log(modules[key] );

		elements += 1;
	}

	//add last column
    headingTitles.push( 'Total' );

    return headingTitles;

}

/*
 * function to build the chart core data
 */

function buildChartCore(thechartarray, themodules) {


    var returnChartArray = [];

    var totalModules = themodules.length;

	//loop thorugh thechartarray
    $.each(thechartarray, function(k, v) {
        //display the key and value pair

        //row data to be added to chart
        var lineData = {};

        //set time  columnfrom initial source
        lineData["time"] = v['time'];

        //totals per line for total column
        var keyTotal = 0;

        //loop through all themodules in set and set values if they exist
        for ( x=0; x<totalModules; x++)
        {
            var checkData = 0;
            
            //zero out data here / lineData starts at 1 for data sets!
            lineData[x+1] = 0;

            //if module data is set parse module data
            //check into hasOwnProperty vs undefined test
            if (typeof v['module'] != 'undefined') {

                //if data is in v['module'] for module it has been recorded so use it
                if (typeof v['module'][themodules[x]] != 'undefined') {

                    checkData = v['module'][themodules[x]];
                    //console.log("check: ", themodules[x], checkData);
                    lineData[x+1] = checkData;

                    keyTotal += checkData;
                }
            }
        }

        lineData["Total"] = keyTotal;
        //console.log("gotData : ", lineData );

        returnChartArray.push(lineData);

    });

    //console.log("returnChartArray : ", returnChartArray );

	return returnChartArray;

}

/*
 * main function to generate chart and to render chart
 */

$(document).ready(function(e) {
    
    //need flag for over threshold for alerts array ?

    //need to build chartArray here instead of in php
    //as its built from alertData on the php side
    //and alertData is raw data needed for alerts
    //console.log(alertData);
    //console.log(chartArray);

    //chartModules is sent over by the calling php code as a global
    //clean it up and get modules to be charted here 
    modules = getModules(chartModules);
    

    /*
     * build heading and title arrays based on modules
     * samples are
     * var headingFields = ['time', '1', '2', '3', '4', 'Total'];
     * var headingTitles = ['time', 'a', 'b', 'c', 'd', 'Total'];
	 */

    var headingFields = [];
    headingFields = buildChartHeadings(modules);

    var headingTitles = [];
    headingTitles = buildChartTitles(modules);


    //create chart object 
    var loadAlerts = dynamicTable.config('data-table', 
                                 headingFields, 
                                 headingTitles, //set to null for field names instead of custom header names
                                 'There are no items to list...');

    /*
     * now create core of chart using chartArray (sent from php side)
     */

    var chartDataArray = [];
    chartDataArray = buildChartCore(chartArray, modules);


    /*
     * render chart object with data from chartArray
	 */

	loadAlerts.load(chartDataArray);    


});

