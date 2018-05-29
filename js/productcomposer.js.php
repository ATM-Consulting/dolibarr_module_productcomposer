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
        //console.log($composerDialog.data());
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
                buttons:{
                        
                        'cancel' : {
                            text: "<?php echo $langs->trans('Cancel')?>",
                            "class": 'cancelButtonClass',
                            click: function() {
                              	if($("#" + popinId).data('fk_pcroadmap') != undefined)
                              	{
                              		loadInPopin(interfaceurl + "?get=annuleCurent") ;
                              		$("#" + popinId).removeData('fk_pcroadmap');
                              	}
                                $( this ).dialog( "close" ).html(''); //.dialog('destroy');
                            }
                        }/*,
                        'DeleteAllwork' : {
                        // For testing
                            text: "<?php echo $langs->trans('DeleteAllwork')?>",
                            "class": 'saveButtonClass',
                            click: function() {
                      
                              	loadInPopin(interfaceurl + "?get=delete") ;
                              	if($("#" + popinId).data('fk_pcroadmap') != undefined)
                              	{
                              		$("#" + popinId).removeData('fk_pcroadmap');
                              	}
                                $( this ).dialog( "close" ).html('');
                            }
                        }*/
                    },
               
              
	        });
	        
	        $composerDialog.dialog('open').parent().css('z-index', 3000);
	        
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
		
		if(targetAction == "import")
		{
			loadInPopin(page,true);
			//readyToImport();
		}
		
		if(targetAction == "newroadmap")
		{
			var fk_pcroadmap = $( this ).data('fk_pcroadmap');
			
			// store choice
			$("#" + popinId).data('fk_pcroadmap',fk_pcroadmap);
			
			page =  page + "&roadmapid=" + fk_pcroadmap;
			loadInPopin(page);
		}
		
		
		if(targetAction == "addproductandnextstep" || targetAction == "showProductForm" || targetAction == "validproductformandnextstep")
		{
		 	event.preventDefault();
		 	
			//console.log($( this ).data());
		
			var fk_pcroadmap = $("#" + popinId).data('fk_pcroadmap');
			page =  page + "&roadmapid=" + fk_pcroadmap;
			
			var productid = $( this ).data('id');
			page =  page + "&productid=" + productid;
			
			var nextstepid = $( this ).data('fk_nextstep');
			page =  page + "&nextstepid=" + nextstepid;
			
			var stepid = $( this ).data('fk_step');
			page =  page + "&stepid=" + stepid;
			
			var postfields;
			if(targetAction == "validproductformandnextstep")
			{
				postfields = $( '#pc-product-form' ).serialize();
			}
			
			loadInPopin(page, 0, false, postfields);
		}
		
		
		
		
		
		if(targetAction == "selectroadmapcategorie")
		{		
			var parametters = { 
					roadmapid: $("#" + popinId).data('fk_pcroadmap'), 
					nextstepid: $( this ).data('fk_nextstep'), 
					stepid: $( this ).data('fk_step'), 
					fk_categorie: $( this ).data('id'), 
				};
		
			//console.log( dataTransmitToUrl(parametters , page));
			loadInPopin(  dataTransmitToUrl(parametters , page)   );
		}
		
		
		if(targetAction == "delete-product")
		{		
			var parametters = $( this ).data();
			//console.log( dataTransmitToUrl(parametters , page));
			loadInPopin(dataTransmitToUrl(parametters , page) ,0, $( this ).data('load-in'));
		}
		
		
		if(targetAction == "loadstep")
		{		
			var parametters = { 
					roadmapid: $("#" + popinId).data('fk_pcroadmap'),
					stepid: $( this ).data('fk_step'), 
					fk_categorie: $( this ).data('fk_categorie'), 
					goto : $( this ).data('goto')
				};
		
			//console.log( dataTransmitToUrl(parametters , page));
			loadInPopin(  dataTransmitToUrl(parametters , page)   );
		}
		
		
		if(targetAction == "selectcatandnextstep")
		{		
			var parametters = { 
					roadmapid: $("#" + popinId).data('fk_pcroadmap'),
					stepid: $( this ).data('fk_step'), 
					fk_categorie: $( this ).data('id')
				};
		
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
	
	
	
	function loadInPopin(target,reloadAfter = 0, htmltarget=false, postfields=false){
		
		
		var dialogWrap =  $("#" + popinId);
		
		if(htmltarget && $(htmltarget) != undefined)
		{
			var dialogContent =  $(htmltarget);
		}
		else{
			var dialogContent =  dialogWrap;
		}
		
		
		
        dialogContent.fadeTo('fast',0,function() {
    		// Animation complete.
            var fromelement = dialogWrap.data("element");
            var fromelementid = dialogWrap.data("id");
            
    		if( fromelement != undefined && fromelementid != undefined)
    		{
    			var appendUrl = "fromelement=" + fromelement + "&fromelementid=" + fromelementid;
    			if (target.indexOf("?") >= 0){
    				target = target + '&' + appendUrl;
    			}else{
    				target = target + '?' + appendUrl;
    			}
    		}
    		
    		$.post( target , postfields,function(data) {
    			dialogContent.html( data );
    			
    			// RELOAD PAGE
    			if(reloadAfter){
            		//location.reload();
            	}
            	
            	// DETECT IMPORT READY
            	if ( dialogContent.find( "#productComposerIsReadyToImport" ).length ) {
					
            		var buttons = $composerDialog.dialog("option", "buttons"); // getter
            		
            		var importBtn = {'importbtn':{ 
            		
            				text: "<?php echo $langs->trans('ImportInDocument')?>",
                            "class": 'butAction',
                            click: function () { 
                    			var page = interfaceurl + "?get=import";
                    			loadInPopin(page,true);
                    		}
            		}};
            		
            		$.extend(buttons, importBtn);
                			
                	$composerDialog.dialog("option", "buttons", buttons); // setter
				}
              	
              	// SHOW RESULT
              	dialogContent.fadeTo('fast',100).find("#item-filter").focus();
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
