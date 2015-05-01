//used to get status of accordions - collapsed or visable as well as postion
//from the loadUI cookie



$(function () { 

    $("[checkbox-type='my-checkbox']").bootstrapSwitch();  

    $('input[checkbox-type="my-checkbox"]').on('switchChange.bootstrapSwitch', function(event, state) {

            if($(this).is(':checked')){
                //console.log("show basic"); // DOM element
            } else {
                //console.log("hide basic"); // DOM element
            }

    });

    $("[checkbox-type='my-checkbox-databox']").bootstrapSwitch();  
    //$("[name='formsettings[modules][<?php echo $module; ?>]']").bootstrapSwitch();  

    $('input[checkbox-type="my-checkbox-databox"]').on('switchChange.bootstrapSwitch', function(event, state) {
    //$('input[name="formsettings[modules][<?php echo $module; ?>]"]').on('switchChange.bootstrapSwitch', function(event, state) {


            var thename =   $(this).attr('data-target');
            var thediv =    '.viewdetails_' + thename;

            if($(this).is(':checked')){
                //console.log("show"); // DOM element
                $(thediv).show();
            } else {
                //console.log("hide"); // DOM element
                $(thediv).hide();
            }

    });
})
