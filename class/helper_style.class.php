<?php


class hStyle
{
    /*
     * $style // default,error, info, warning
     */
	public static function callout($message='',$style='default' )
	{
	    if($style=='default'){
	        
	    }
	    
	    
	    $ret  =  '<div class="'.$style.' clearboth" >';
	    $ret .=  $message;
	    $ret .=  '</div>';
	}
	
	
}


