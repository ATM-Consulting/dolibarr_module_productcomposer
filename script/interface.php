<?php

require('../config.php');

dol_include_once( '/productcomposer/lib/productcomposer.lib.php');
dol_include_once('/productcomposer/class/productcomposer.class.php');

$get = GETPOST('get');
$post = GETPOST('post');
$fromelement = GETPOST('fromelement');
$fromelementid = GETPOST('fromelementid','int');
$roadmapid = GETPOST('roadmapid','int');
$stepid = GETPOST('stepid','int');
/*
if( ($post=='roadmapRank' || $post=='roadmapRankDet') && !empty($fromelement) )
{
    _postRoadmapRank($fromelement);
}*/

if($get=='selectRoadmap' && !empty($fromelement) && !empty($fromelementid) )
{
    
    $PComposer = productcomposer::loadbyelement($fromelementid,$fromelement);
    
    if(!empty($PComposer))
    {
        $PComposer->print_roadmapSelection();
    }
}


if($get=='newroadmap' )
{
    if(!empty($fromelement) && !empty($fromelementid) && !empty($roadmapid))
    {
        $PComposer = productcomposer::loadbyelement($fromelementid,$fromelement);
        
        if(!empty($PComposer))
        {
            $res =$PComposer->addRoadmap($roadmapid);
            if($res > 0)
            {
                $PComposer->print_nextstep(0);
            }
            else{
                print hStyle::callout($langs->trans('ErrorLoadingRoadmap').' : '.$res,'error');
            }
        }
        else{
            print hStyle::callout($langs->trans('ErrorLoadingProductcomposer'),'error');
        }
        
    }
    else { echo $langs->trans('paramMissed'); }
}

if($get=='loadnextstep'  && !empty($fromelement) && !empty($fromelementid) && !empty($roadmapid) )
{
    if(!empty($fromelement) && !empty($fromelementid) && !empty($roadmapid))
    {
    
        $PComposer = productcomposer::loadbyelement($fromelementid,$fromelement);
        if(!empty($PComposer))
        {
            echo 'la';
            //addRoadmap($roadmapid,$setcurent=true)
            
            //$PComposer->loadCurentRoadMap($roadmapid);
            //$PComposer->print_roadmapSelection();
        }
    
    }
    else { echo $langs->trans('paramMissed'); }
}

if($get=='delete'  && !empty($fromelement) && !empty($fromelementid) && !empty($roadmapid) )
{
    if(!empty($fromelement) && !empty($fromelementid) && !empty($roadmapid))
    {
        
        $PComposer = productcomposer::loadbyelement($fromelementid,$fromelement);
        if(!empty($PComposer))
        {
            if($PComposer->delete())
            {
                echo 'deleted';
            }
        }
        
    }
    else { echo $langs->trans('paramMissed'); }
}

    
/*
function _postRoadmapRank($objectName)
{
    global $db,$user;
	$TRowOrder= GETPOST('TRowOrder');
	$contractId= GETPOST('objet_id');
	
	$objectHaveRank =  false;
	if(class_exists($objectName) )
	{
	    $object = new $objectName($db);
	    if(method_exists($object, 'updateRankOfLine'))
	    {
	        $objectHaveRank = true;
	    }
	}
	
	if(!$objectHaveRank){ exit; }
	
	if(is_array($TRowOrder) && !empty($TRowOrder) && !empty($contractId))
	{
		foreach($TRowOrder as $rang => $value)
		{
			$rowid= intval($value);
			$rang = intval($rang);
			
			if($rowid>0)
			{
			    $objectName::updateRankOfLine($rowid,$rang);
			}
			
		}
	}
	
	exit();
}*/


var_dump($_REQUEST);
