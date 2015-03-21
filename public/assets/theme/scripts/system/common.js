




function storeState() {


    var loadCookie = "loadUIcookie";

    var myCookie = [];
    var jsonObj = {}; 

    //mine
    var toggled_div = $('#accordion');

    $(toggled_div).children().each(function() {

        var id = $(this).attr('id');
       if (id != 'separator' )
       {
            var moduleName = $(this).attr('data-collapse-closed');

            //console.log("moduleName " + moduleName);

            //if (moduleName != 'undefined' && (moduleName) )
            if ( (moduleName) )
            {
                var status = $(this).attr('cookie-closed');

                if ( status == null || !status )
                    status = "open";

                //for when nothinbg has been set its open
                if ( status == "true" || status == "open" )
                    status = "open";
                else
                    status = "closed";

                jsonObj[moduleName] = status;

            }
        }

    });

    myCookie.push( jsonObj );

    // then to get the JSON string
    myCookie = JSON.stringify(myCookie);

    //get rid of extra brackets on string
    var newStr = myCookie.substring(1, myCookie .length-1);

    $.cookie(loadCookie, newStr, {expires:365, path: '/'});

   // console.log(check_open_divs);

}


    /////////////////////////////////////////////////////
    // functions
    ////////////////////////////////////////////////////

    function timeConverter ( ts ){

        //console.log ("data type : ", toType(ts));
        //console.log ("data      : ", ts );

        //var timestamp = parseInt(ts);
        var timestamp = ts;


        var dt = new Date( timestamp );
        data = (dt.getHours() + ':' + dt.getMinutes() + ':' + dt.getSeconds() + ' -- ' + dt );
        return data;
    };

    function toType (obj) {
        return ({}).toString.call(obj).match(/\s([a-zA-Z]+)/)[1].toLowerCase()
    };


//chart utility functions - but can be called from charts embedded in plugins

//get item time in hh : mm : am/pm 
//needs to be updated to account for timezone overrides
function getItemTime ($itemTime)
{
    var hours = $itemTime.getHours()
    var minutes = $itemTime.getMinutes()
    var ampm = "";

    if (minutes < 10) 
        minutes = "0" + minutes

    if(hours > 12) { hours = hours - 12; ampm = "pm"; }
        else ampm = "am";

    $itemTime = hours + ":" + minutes + " " + ampm;

    //console.log(  $itemTime, "time set");

    return $itemTime;
}


    