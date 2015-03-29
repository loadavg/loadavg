
//$('#content').draggable();




 /*
  * time converter function - converts timestamps into hours and minutes
  */

function timeConverter ( ts ){

    //console.log ("data type : ", toType(ts));
    //console.log ("data      : ", ts );

    //var timestamp = parseInt(ts);
    var timestamp = ts;


    var dt = new Date( timestamp );
    data = (dt.getHours() + ':' + dt.getMinutes() + ':' + dt.getSeconds() + ' -- ' + dt );
    return data;
};

/*
  * type converter function - where is this used ???
  */

function toType (obj) {
    return ({}).toString.call(obj).match(/\s([a-zA-Z]+)/)[1].toLowerCase()
};


/*
 * get the time in hh:mm am/pm format for a date/time object sent over 
 *
 */

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


    