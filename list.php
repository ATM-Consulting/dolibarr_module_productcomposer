<?php

require 'config.php';
// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
dol_include_once( '/productcomposer/lib/productcomposer.lib.php');
dol_include_once('/productcomposer/class/roadmap.class.php');

if( empty($user->rights->productcomposer->read) && !$user->admin) accessforbidden();

$langs->load('abricot@abricot');
$langs->load('productcomposer@productcomposer');

$PDOdb = new TPDOdb;
$object = new PCRoadMap($db);

$hookmanager->initHooks(array('productcomposerlist'));

/*
 * Actions
 */

$parameters=array();
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	// do action from GETPOST ... 
}


/*
 * View
 */

llxHeader('',$langs->trans('RoadmapList'),'','');

print_fiche_titre($langs->trans('RoadmapList'));

// Configuration header
$head = productcomposerAdminPrepareHead();
dol_fiche_head(
    $head,
    'roadmaps',
    $langs->trans("Module103998Name"),
    0,
    "productcomposer@productcomposer"
    );



//$type = GETPOST('type');
//if (empty($user->rights->productcomposer->all->read)) $type = 'mine';

// TODO ajouter les champs de son objet que l'on souhaite afficher
$sql = 'SELECT r.rowid id, r.label, r.date_creation, \'\' AS action';
$sql.= ' FROM '.MAIN_DB_PREFIX.$object->table_element.' r ';
$sql.= ' WHERE 1=1';
$sql.= ' ORDER BY r.rank ASC ';




$dbtool = new PCDbTool($db);
$Tlist = $dbtool->executeS($sql);


    ?>
<table width="100%" border="0" class="notopnoleftnoright" style="margin-bottom: 6px;">
    <tbody>
        <tr>
            <td class="nobordernopadding valignmiddle">
                <img src="<?php echo dol_buildpath('theme/eldy/img/title_generic.png',2); ?>" alt="" class="hideonsmartphone valignmiddle" id="pictotitle">
                <div class="titre inline-block"><?php  print $langs->trans('RoadmapStep'); ?></div>
            </td>
            <td style="text-align:right;" >
            	<a class="butAction" href="<?php print dol_buildpath('/productcomposer/card.php',2).'?action=create' ?>" ><i class="fa fa-plus"></i> <?php print $langs->trans('AddNewRoadMap'); ?></a>
            </td>
       </tr>
   </tbody>
</table>
 

<div class="div-table-responsive">
<table id="productcomposer" class="liste ui-sortable" width="100%">
    <thead>
        <tr class="liste_titre">
            <th class="liste_titre" ><?php print $langs->trans('Label'); ?></th>
            <th class="liste_titre" ><?php print $langs->trans('Category'); ?></th>
            
            <th class="liste_titre" ></th>
            <th class="liste_titre" ></th>
        </tr>
    </thead>
    <tbody>
<?php if(!empty($Tlist)){ ?>
    	<?php foreach($Tlist as $roadmap){ ?>
        <tr class="oddeven" data-lineid="<?php print $roadmap->id; ?>" >
            <td  ><a href="<?php print dol_buildpath('/productcomposer/card.php',2).'?id='.$roadmap->id; ?>" ><?php print $roadmap->label; ?></a></td>
            <td  ><a href="<?php print dol_buildpath('/categories/viewcat.php?type=product',2).'&amp;id='.$roadmap->fk_categorie; ?>" ><?php print $roadmap->category_label; ?></a></td>
            <td class="productcomposer_linecolmove" ></td>
            <td ><a href="<?php print dol_buildpath('/productcomposer/card.php',2).'?action=edit&id='.$roadmap->id; ?>" ><?php print img_edit(); ?></a></td>
            
        </tr>
        <?php } ?>
<?php } ?>   
    </tbody>
</table>
</div>

<?php if(!empty($Tlist)){ ?>
<script type="text/javascript">
$(document).ready(function(){
    
    // target some elements
    var moveBlockCol= $('td.productcomposer_linecolmove');
    
    
    moveBlockCol.disableSelection(); // prevent selection
    
    // apply some graphical stuff
    moveBlockCol.css("background-image",'url(<?php echo dol_buildpath('theme/eldy/img/grip.png',2);  ?>)');
    moveBlockCol.css("background-repeat","no-repeat");
    moveBlockCol.css("background-position","center center");
    moveBlockCol.css("cursor","move");
    moveBlockCol.attr('title', '<?php echo html_entity_decode($langs->trans('MoveTitleBlock')); ?>');
    
    
    $( "#productcomposer" ).sortable({
        cursor: "move",
        handle: ".productcomposer_linecolmove",
        items: 'tr:not(.liste_titre)',
        delay: 150, //Needed to prevent accidental drag when trying to select
        opacity: 0.8,
        axis: "y", // limit y axis
        placeholder: "ui-state-highlight",
        start: function( event, ui ) {
            //console.log('X:' + e.screenX, 'Y:' + e.screenY);
            //console.log(ui.item);
            var colCount = ui.item.children().length;
            ui.placeholder.html('<td colspan="'+colCount+'">&nbsp;</td>');
            
        },
        update: function (event, ui) {

        	var TRowOrder = $(this).sortable('toArray', { attribute: 'data-lineid' });
        				    	        
	        // POST to server using $.post or $.ajax
	        $.ajax({
	            data: {
    	            post: 'roadmapRank',
					TRowOrder: TRowOrder
				},
	            type: 'POST',
	            url: '<?php echo dol_buildpath('/productcomposer/script/interface.php', 1) ; ?>',
	            success: function(data) {
	                console.log(data);
	            },
	        });
	    }
    });
		
});
</script>
<style type="text/css" >

tr.ui-state-highlight td{
	border: 1px solid #dad55e;
	background: #fffa90;
	color: #777620;
}
</style>
<?php 
}








llxFooter('');

/**
 * TODO remove if unused
 */
function _getUserNomUrl($fk_user)
{
	global $db;
	
	$u = new User($db);
	if ($u->fetch($fk_user) > 0)
	{
		return $u->getNomUrl(1);
	}
	
	return '';
}