
//chart utility functions

//get item time
//needs to be updated to account for timezone overrides
function getItemTime ($itemTime)
{
    var hours = $itemTime.getHours()
    var minutes = $itemTime.getMinutes()
    var ampm = "";

    if (minutes < 10) minutes = "0" + minutes

    if(hours > 12) { hours = hours - 12; ampm = "pm"; }
        else ampm = "am";

    $itemTime = hours + ":" + minutes + " " + ampm;

    //console.log(  $itemTime, "time set");

    return $itemTime;
}


