<?php

    define('INC_FROM_CRON_SCRIPT',true);
    require('../config.php');
    header('Content-Type: application/javascript');
    
    // Translations
    $langs->load("productcomposer@productcomposer");
?>

$( document ).ready(function() {


	$("#pc-product-generator-btn").click(function (e) {
		
        e.preventDefault();
        var page = 'sddd' ;//$(this).attr("href");
        var fromelement = $(this).attr("data-element");
        var fromelementid = $(this).attr("data-id");
        
		if( page != undefined && fromelement != undefined && fromelementid != undefined)
		{
	    	var windowWidth = $(window).width()*0.8; //retrieve current window width
	    	var windowHeight = $(window).height()*0.8; //retrieve current window height
			
			
	        
	        
	        var $composerDialog = $('<div></div>').dialog({
	            autoOpen: false,
	            modal: true,
	            height: windowHeight,
	            width: windowWidth,
	            title: "<?php echo $langs->trans('PopUpTitle_ProductComposer'); ?>",
                buttons: {
                  "<?php echo $langs->trans('Cancel')?>": function() {
                    $( this ).dialog( "close" );
                  }
                }
	        });
	        
	        $composerDialog.dialog('open');
	        
	        // add 
	        var buttons = $composerDialog.dialog("option", "buttons"); // getter
			$.extend(buttons, { "<?php echo $langs->trans('ImportInDocument')?>": function () { 
			
			alert('foo'); 
			
			} });
			$composerDialog.dialog("option", "buttons", buttons); // setter
		}
		else
		{
			$.jnotify("<?php echo $langs->trans('ErrorNoUrl'); ?>", "error", true);
		}
		
	
	});
});
