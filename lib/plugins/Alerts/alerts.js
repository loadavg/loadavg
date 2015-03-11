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
    function _buildRowColumns(names, item) {
        var row = '<tr class="selectable">';
        if (names && names.length > 0)
        {
        	loop = 0;

            $.each(names, function(index, name) {
                var c = item ? item[name+''] : name;
                //row += '<td>' + c + '</td>';

                //first column here - time
                //need to make last one the total column as well and add them up ?
                if (loop==0)
                	row += '<td><span class="label label-info">' + c + '</span></td>';
            	else {

            		//console.log(item[name]);
            		//console.log(_warningThreshold);

            		if ( item[name] == 0 ) {
                		row += '<td><span class="label label-warning"></span></td>';
                	}
            		else if ( item[name] > _warningThreshold ) {
                		row += '<td><span class="label label-warning">' + c + '</span></td>';
                	}
                	else
                	{
                		row += '<td><span class="label label-success">' + c + '</span></td>';                		
                	}


            	}
            	loop++;
            });
        }
        row += '<tr>';
        return row;
    }

    /** Builds the row with columns from the specified names. 
     *  If the item parameter is specified, the memebers of the names array will be used as property names of the item; otherwise they will be directly parsed as text.
     */
    function _buildHeaderRowColumns(names, item) {
        var row = '<tr>';
        if (names && names.length > 0)
        {
            $.each(names, function(index, name) {
                var c = item ? item[name+''] : name;
                row += '<th>' + c + '</th>';
            });
        }
        row += '<tr>';
        return row;
    }


    /** Builds and sets the headers of the table. */
    function _setHeaders() {
        // if no headers specified, we will use the fields as headers.
        _headers = (_headers == null || _headers.length < 1) ? _fields : _headers; 
        
        //var h = _buildRowColumns(_headers);
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
            _warningThreshold = 3;
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
                
                $.each(data, function(index, item) {

                    rows += _buildRowColumns(_fields, item);

                });

                //console.log(rows, "_buildRowColumns");

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

$(document).ready(function(e) {
    
    //need to build alerts array here so all is in order...

    //need time array col 1
    //need alerts array
    //need totals array last col
    console.log(alertData);
    console.log(chartData);

    //need flag for over threshold for alerts array ?

    var data1 = [
        { time: '12:00' , field1: 1, field2: 0, field3: 3, field4: 7, total: 24 },
        { time: '01:00' , field1: 5, field2: 2, field3: 0, field4: 12, total: 24 },
        { time: '02:00' , field1: 8, field2: 6, field3: 12, field4: 12, total: 24 }
        ];

    
    var dt = dynamicTable.config('data-table', 
                                 ['time', 'field1', 'field2', 'field3', 'field4', 'total'], 
                                 ['time', 'header 1', 'header 2', 'header 3', 'header 4', 'Total'], //set to null for field names instead of custom header names
                                 'There are no items to list...');

	dt.load(data1);    


});

