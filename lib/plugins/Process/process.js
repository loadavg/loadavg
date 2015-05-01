

$(function () {

 $( "#accordion" )

     .accordion({
         header: "> div > h3",

         activate: function( event, ui){
         }
     })
     
     /*
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
    });
*/


    $('div.accordion-body').on('shown', function () {

        //console.log( $(this).parents().attr('data-collapse-closed') + ' open' );
        $(this).parents().attr('cookie-closed', true);

        //storeState();
    });

    $('div.accordion-body').on('hidden', function () {

         //console.log( $(this).parents().attr('data-collapse-closed') + ' close' );
         $(this).parents().attr('cookie-closed', false);

        //storeState();
    });


});

