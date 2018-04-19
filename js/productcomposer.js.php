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
		
       // e.preventDefault();
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
                  
                  	loadInPopin(interfaceurl + "?get=delete") ;
                  
                    $( this ).dialog( "close" ).dialog('destroy').remove();
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
		var page = interfaceurl + "?get=" + targetAction;
		
		if(targetAction == "loadnextstep")
		{
			loadInPopin(page);
		}
		
		if(targetAction == "newroadmap")
		{
			var fk_pcroadmap = $( this ).data('fk_pcroadmap');
			
			// store choice
			$("#" + popinId).data('fk_pcroadmap',fk_pcroadmap);
			
			page =  page + "&roadmapid=" + fk_pcroadmap;
			loadInPopin(page);
		}
		
		
		if(targetAction == "addproductandnextstep")
		{
			var fk_pcroadmap = $("#" + popinId).data('fk_pcroadmap');
			page =  page + "&roadmapid=" + fk_pcroadmap;
			
			var productid = $( this ).data('id');
			page =  page + "&productid=" + productid;
			
			var nextstepid = $( this ).data('fk_nextstep');
			page =  page + "&nextstepid=" + nextstepid;
			
			var stepid = $( this ).data('fk_step');
			page =  page + "&stepid=" + stepid;
			
			
			loadInPopin(page);
		}
		
		
		
	
	});
	
	
	
	
	function addProduct(id){
		var dialogContent =  $("#" + popinId);
		var fk_pcroadmap = $("#" + popinId).data('fk_pcroadmap');
	}
	
	
	
	function loadInPopin(target){
		
		var dialogContent =  $("#" + popinId);
        dialogContent.fadeTo('fast',0,function() {
    		// Animation complete.
            var fromelement = dialogContent.data("element");
            var fromelementid = dialogContent.data("id");
            
    		if( fromelement != undefined && fromelementid != undefined)
    		{
    			var appendUrl = "fromelement=" + fromelement + "&fromelementid=" + fromelementid;
    			if (target.indexOf("?") >= 0){
    				target = target + '&' + appendUrl;
    			}else{
    				target = target + '?' + appendUrl;
    			}
    		}
    		dialogContent.load( target , function() {
              dialogContent.fadeTo('fast',100);
            });
        
  		});
        
		
			
	}
	
	
	$( document ).on("keyup", "#item-filter", function () {

        var filter = $(this).val(), count = 0;
        var target = $(this).data("target");
        $(target + " .searchitem").each(function () {
       
            if ($(this).text().search(new RegExp(filter, "i")) < 0) {
                $(this).addClass("hidden");
            } else {
                $(this).removeClass("hidden");
                count++;
            }
        });
        $("#filter-count").text(count);
    });
	
	
});
