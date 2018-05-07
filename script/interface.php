<?php

require('../config.php');

dol_include_once( '/productcomposer/lib/productcomposer.lib.php');
dol_include_once('/productcomposer/class/productcomposer.class.php');


// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('productcomposerinterface'));

// Translations
$langs->load("productcomposer@productcomposer");

//var_dump((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

$get = GETPOST('get');
$post = GETPOST('post');
$fromelement = GETPOST('fromelement');
$fromelementid = GETPOST('fromelementid','int');
$roadmapid = GETPOST('roadmapid','int');
$stepid = GETPOST('stepid','int');
$nextstepid = GETPOST('nextstepid','int');
$param=array();

if( ($post=='roadmapRank' || $post=='roadmapRankDet') )
{
    _postRoadmapRank($post=='roadmapRank'?'PCRoadMap':'PCRoadMapDet');
    exit;
}

if(!empty($fromelement) && !empty($fromelementid) )
{
    // load product composer
    $PComposer = productcomposer::loadbyelement($fromelementid,$fromelement);
    if($PComposer < 1)
    {
        print hStyle::callout($langs->trans('ErrorLoadingProductcomposer'),'error');
        exit();
    }
}
else{
    print hStyle::callout($langs->trans('ErrorLoadingProductcomposer'),'error');
    exit();
}


if($get=='selectRoadmap')
{
    $PComposer->print_roadmapSelection();
}



if($get=='newroadmap' )
{
    if(!empty($fromelement) && !empty($fromelementid) && !empty($roadmapid))
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
    else { echo $langs->trans('paramMissed'); }
}

if( $get == 'addproductandnextstep' )
{
    $productid = GETPOST('productid');
    if(!empty($stepid) && !empty($productid) )
    {
        $PComposer->addProduct($productid,$stepid);
        
        
        // go to loadnextstep action
        $PComposer->print_nextstep($stepid);
        
        print '<div id="composer-cart" class="composer-cart">';
        $PComposer->printCart();
        print '</div>';
    }
    else { echo $langs->trans('paramMissed'); }
}



if( $get == 'selectroadmapcategorie' )
{
    
    if(!empty($PComposer->roadmap) )
    {
        $PComposer->print_nextstep(0, array('fk_categorie'=>GETPOST('fk_categorie','int')));
        
        print '<div id="composer-cart" class="composer-cart">';
        $PComposer->printCart();
        print '</div>';
    }
    else { print hStyle::callout($langs->trans('ErrorRoadMapNotLoaded'),'error'); }
}

if( $get == 'loadstep' )
{
    if(!empty($stepid))
    {
        
        
        $goTo = GETPOST('goto','int');
        if($goTo){
            $PComposer->TcurentComposer['currentCycle']++;
            $PComposer->save();
        }
        
        
        $PComposer->print_step($stepid, array('fk_categorie'=>GETPOST('fk_categorie','int')) );
        
        print '<div id="composer-cart" class="composer-cart">';
        $PComposer->printCart();
        print '</div>';
    }
    else { echo $langs->trans('paramMissed'); }
}



if( $get == 'loadnextstep' )
{
    if(!empty($stepid))
    {
        $PComposer->print_nextstep($stepid);
        
        print '<div id="composer-cart" class="composer-cart">';
        $PComposer->printCart();
        print '</div>';
    }
    else { echo $langs->trans('paramMissed'); }
}

if($get=='delete'  && !empty($fromelement) && !empty($fromelementid))
{
    if($PComposer->delete())
    {
        echo 'deleted';
    }
}


if($get=='import')
{
    if($PComposer->import())
    {
        
    }
}

if($get=='annuleCurent')
{
    $PComposer->annuleCurent();
}

if($get=='loadcart')
{
    
    print '<div id="composer-cart" class="composer-cart">';
    $PComposer->printCart();
    print '</div>';
}



if($get=='delete-product')
{
    $cycle   = GETPOST('cycle','int');
    $step    = GETPOST('step','int');
    $product = GETPOST('product','int');
    
    $allAfter = 0;
    if(!empty($conf->global->PC_FORCE_DEL_FOLLOWING_PRODUCT))
    {
        $allAfter = 1;
    }
    
    $PComposer->deleteProduct($cycle,$step,$product,$allAfter);
    $PComposer->printCart();
}
    

function _postRoadmapRank($objectName)
{
    global $db,$user;
	$TRowOrder= GETPOST('TRowOrder');
	$objId= GETPOST('objet_id');
	
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
	
	if(is_array($TRowOrder) && !empty($TRowOrder) && ( !empty($objId) || $objectName == 'PCRoadMap' )  )
	{
		foreach($TRowOrder as $rang => $value)
		{
			$rowid= intval($value);
			$rang = intval($rang);
			
			if($rowid>0)
			{
			    $objectName::updateRankOfLine($rowid,$rang);
			    echo $rowid.' '.$objectName;
			}
			
		}
	}
	
	exit();
}

if(!empty($PComposer->TcurentComposer['products']))
{
    echo '<div id="productComposerIsReadyToImport" ></div>';
}

print '<div style="clear:both;" ></div>';
//var_dump($_REQUEST);
//var_dump($PComposer->TcurentComposer);
