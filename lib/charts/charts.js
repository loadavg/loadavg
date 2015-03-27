//used to get status of accordions - collapsed or visable as well as postion
//from the loadUI cookie


$(function () {


//this hack is used to disable drag and drop of charts on mobile devices
var weAreMobile = false;
if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
 weAreMobile = true;
 //console.log ("we are mobile in charts");
} 


 $( "#accordion" )

     .accordion({
         header: "> div > h3",

         activate: function( event, ui){
         }
     })
     
    
     .sortable({


        connectWith: ".accordion",
        //items: ":not(.separator)",
        cancel: ".separator",

        start: function( event, ui ){
        },

        stop: function( event, ui ) {
             //ui.item.children( "h3" ).triggerHandler( "focusout" );
            $(this).sortable("refresh"); 
            storeState();
        
    }

    //without this touch devices cant drag the UI !!!
    .draggable();
    
    });

     //disable sorting on mobile devices
     //messy hack but beetter than before
     if (weAreMobile == true) {
        $('#accordion').sortable('disable');
     };


    $('div.accordion-body').on('shown', function () {

        //console.log( $(this).parents().attr('data-collapse-closed') + ' open' );
        $(this).parents().attr('cookie-closed', true);

        storeState();
    });

    $('div.accordion-body').on('hidden', function () {

         //console.log( $(this).parents().attr('data-collapse-closed') + ' close' );
         $(this).parents().attr('cookie-closed', false);

        storeState();
    });


});



/*
 * used to save status of accordions for the charting module
 * data is saved in a cookie
 */ 

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

