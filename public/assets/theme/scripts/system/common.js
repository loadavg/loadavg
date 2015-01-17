

//used to get status of accordions - collapsed or visable
//from the loadUI cookie
//using code to manage accordion state is in common.js
//http://www.ridgesolutions.ie/index.php/2013/02/19/twitter-bootstrap-programmatically-open-or-close-an-accordion-with-javascript/

//fix icons with this if we want them
//http://stackoverflow.com/questions/18325779/bootstrap-3-collapse-show-state-with-chevron-icon


$(function () {


 var active = false,
 sorting = false;

 $( "#accordion" )

     .accordion({
         header: "> div > h3",
         //collapsible: true,

         activate: function( event, ui){
             //this fixes any problems with sorting if panel was open 
             //remove to see what I am talking about

             if(sorting) {
                 $(this).sortable("refresh"); 
                 current_order($(this));                                         
             };
         }
     })
     
     .sortable({
        connectWith: ".accordion",
     //handle: "h3",
     //placeholder: "ui-state-highlight",

     start: function( event, ui ){
     //change bool to true
     sorting=true;

     },

     stop: function( event, ui ) {
     
     //ui.item.children( "h3" ).triggerHandler( "focusout" );

    $(this).sortable("refresh"); 
    
    //current_order($(this));    

     //change bool to false
     sorting=false;
     
      storeState();

     }
 });



    $('div.accordion-body').on('shown', function () {

        console.log( $(this).parents().attr('data-collapse-closed') + ' open' );

        //$(this).('.accordion:first').attr('cookie-closed', true);
        //$(this).parents('.accordion:first').attr('cookie-closed', true);
        $(this).parents().attr('cookie-closed', true);

        storeState();

    });

    $('div.accordion-body').on('hidden', function () {


         console.log( $(this).parents().attr('data-collapse-closed') + ' close' );

       // $(this).('.accordion:first').attr('cookie-closed', false);
        //$(this).parents('.accordion:first').attr('cookie-closed', false);
         $(this).parents().attr('cookie-closed', false);

        storeState();

    });


});

function current_order(el){
    var order=[];
    el.children().each( function(i){      
              order[i]=this.id;
    });
    // silly test      
    for(var i=0; i<order.length; i++){
       //console.log("got " + order[i]);
   }
}

function storeState() {


    var loadCookie = "loadUIcookie";
//    var testCookie = "testJSUIcookie";

  //  var check_open_divs = [];
    var myCookie = [];
   var jsonObj = {}; 

    //mine
    var toggled_div = $('#accordion');

    var position = 0;

    $(toggled_div).children().each(function() {


        var id = $(this).attr('id');
       if (id != 'separator' )
       {
            var moduleName = $(this).attr('data-collapse-closed');

            console.log("moduleName " + moduleName);

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

    myCookie.push( jsonObj   );

    // then to get the JSON string
    myCookie = JSON.stringify(myCookie);

    //get rid of extra brackets on string
    var newStr = myCookie.substring(1, myCookie .length-1);

    $.cookie(loadCookie, newStr, {expires:365, path: '/'});


   // console.log(check_open_divs);

}


