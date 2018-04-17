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

if($get=='loadnextstep'  && !empty($fromelement) && !empty($fromelementid) && !empty($roadmapid) )
{
    
    $PComposer = productcomposer::loadbyelement($fromelementid,$fromelement);
    if(!empty($PComposer))
    {
        $PComposer->loadCurentRoadMap($roadmapid);
        //$PComposer->print_roadmapSelection();
    }
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

