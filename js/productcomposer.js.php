<?php

    define('INC_FROM_CRON_SCRIPT',true);
    require('../config.php');
    header('Content-Type: application/javascript');
    
    // Translations
    $langs->load("productcomposer@productcomposer");
?>

var $composerDialog;
	
$( document ).ready(function() {
	var readyToImport = false;
	var interfaceurl = "<?php print dol_buildpath('/productcomposer/script/interface.php',2); ?>";
	var popinId = "jquery-product-composer-dialog-box";
	$("#pc-product-generator-btn").click(function (e) {
		
       // e.preventDefault();
       
	    $composerDialog = $("#" + popinId);
        var page = interfaceurl + "?get=selectRoadmap";
        console.log($composerDialog.data());
        var fromelement = $composerDialog.data("element");
        var fromelementid = $composerDialog.data("id");
        
		if( page != undefined && fromelement != undefined && fromelementid != undefined)
		{
	    	var windowWidth = $(window).width()*0.95; //retrieve current window width
	    	var windowHeight = $(window).height()*0.95; //retrieve current window height
			
			page = page + "&fromelement=" + fromelement + "&fromelementid=" + fromelementid
			
	       /* $.get(page, function (data) {
	        	htmlLines = $(data) ;
	        });*/
	        
	        if($("#" + popinId).data('fk_pcroadmap') == undefined)
          	{
          		 $composerDialog.load( page);
          	}
	        
	        
	        $composerDialog.dialog({
	            autoOpen: false,
	            modal: true,
	            height: windowHeight,
	            width: windowWidth,
	            title: "<?php echo $langs->trans('PopUpTitle_ProductComposer'); ?>",
                buttons: {
                      "<?php echo $langs->trans('Cancel')?>": function() {
                          	if($("#" + popinId).data('fk_pcroadmap') != undefined)
                          	{
                          		loadInPopin(interfaceurl + "?get=annuleCurent") ;
                          		$("#" + popinId).removeData('fk_pcroadmap');
                          	}
                            $( this ).dialog( "close" ).html(''); //.dialog('destroy');
                      },
                      
                      "<?php echo $langs->trans('DeleteAllwork')?>": function() {
                      
                          	loadInPopin(interfaceurl + "?get=delete") ;
                          	if($("#" + popinId).data('fk_pcroadmap') != undefined)
                          	{
                          		$("#" + popinId).removeData('fk_pcroadmap');
                          	}
                            $( this ).dialog( "close" ).html('');
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
		
		var data = $( this ).data();
		
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
			console.log($( this ).data());
		
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
		
		if(targetAction == "selectroadmapcategorie")
		{		
			var parametters = { 
					roadmapid: $("#" + popinId).data('fk_pcroadmap'), 
					nextstepid: $( this ).data('fk_nextstep'), 
					stepid: $( this ).data('fk_step'), 
					fk_categorie: $( this ).data('id'), 
				};
		
			console.log( dataTransmitToUrl(parametters , page));
			loadInPopin(  dataTransmitToUrl(parametters , page)   );
		}
		
		if(targetAction == "loadstep")
		{		
			var parametters = { 
					roadmapid: $("#" + popinId).data('fk_pcroadmap'),
					stepid: $( this ).data('fk_step'), 
					fk_categorie: $( this ).data('fk_categorie'), 
				};
		
			console.log( dataTransmitToUrl(parametters , page));
			loadInPopin(  dataTransmitToUrl(parametters , page)   );
		}
		
		
	
	});
	
	
	function dataTransmitToUrl(data, target=''){
		
		if(data != undefined)
		{
			var appendUrl = jQuery.param( data );
			
    		if (target.indexOf("?") >= 0){
    			target = target + '&' + appendUrl;
    		}
    		else
    		{
    			target = target + '?' + appendUrl;
    		}
    		
    		return target;
		}
		else
		{
			return '';
		}
		
	
	}
	
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
