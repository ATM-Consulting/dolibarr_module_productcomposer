<?php

    define('INC_FROM_CRON_SCRIPT',true);
    require('../config.php');
    header('Content-Type: application/javascript');
    
    // Translations
    $langs->load("productcomposer@productcomposer");
?>

$( document ).ready(function() {

	var readyToImport = false;
	var interfaceurl = "<?php print dol_buildpath('/productcomposer/script/interface.php',2); ?>";
	var popinId = "product-composer-popin";
	$("#pc-product-generator-btn").click(function (e) {
		
        e.preventDefault();
        var page = interfaceurl + "?get=selectRoadmap";
        var fromelement = $(this).data("element");
        var fromelementid = $(this).data("id");
        
		if( page != undefined && fromelement != undefined && fromelementid != undefined)
		{
	    	var windowWidth = $(window).width()*0.8; //retrieve current window width
	    	var windowHeight = $(window).height()*0.8; //retrieve current window height
			
			page = page + "&fromelement=" + fromelement + "&fromelementid=" + fromelementid
			
	       /* $.get(page, function (data) {
	        	htmlLines = $(data) ;
	        });*/
	        
	        var $composerDialog = $('<div id="' + popinId +'" data-element="' + fromelement + '" data-id="' + fromelementid + '" ></div>');
	        $composerDialog.load( page , function() {
	        
	        })
	        .dialog({
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
	        if(readyToImport)
	        {
	        
    	        var buttons = $composerDialog.dialog("option", "buttons"); // getter
    			$.extend(buttons, { "<?php echo $langs->trans('ImportInDocument')?>": function () { 
    			
    			alert('foo'); 
    			
    			} });
    			$composerDialog.dialog("option", "buttons", buttons); // setter
    			
	        }
	        
		}
		else
		{
			$.jnotify("<?php echo $langs->trans('ErrorNoUrl'); ?>", "error", true);
		}
		
	
	});
	
	
	$( document ).on("click", "[data-target-action]", function(){
		// store curent step
		var targetAction = $( this ).data('target-action');
		
		
		if(targetAction == "loadnextstep")
		{
			var targetAction = $( this ).data('target-action');
			var page = interfaceurl + "?get=" + targetAction;
			
			loadInPopin(page);
			
		}
	
	});
	
	
	
	
	
	
	
	
	function loadInPopin(target){
		
		var dialogContent =  $("#" + popinId);
		
		dialogContent.load( target );
			
	}
	
	
	
	
	
	
});
