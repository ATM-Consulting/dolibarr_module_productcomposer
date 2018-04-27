<?php 


if( empty($user->rights->productcomposer->read) && !$user->admin) accessforbidden();

$object->fetchObjectLinked();

$sql = 'SELECT r.rowid id, r.rank , r.label, c.label category_label, c.color, r.fk_categorie, r.type';
$sql.= ' FROM '.MAIN_DB_PREFIX.'pcroadmapdet r ';
$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'categorie c ON (c.rowid = r.fk_categorie) ';
$sql.= ' WHERE fk_pcroadmap = '.$object->id;
$sql.= ' ORDER BY r.rank ASC ';

$dbtool = new PCDbTool($db);
$TchildrenList = $dbtool->executeS($sql);


?>
<table width="100%" border="0" class="notopnoleftnoright" style="margin-bottom: 6px;">
    <tbody>
        <tr>
            <td class="nobordernopadding valignmiddle">
                <img src="<?php echo dol_buildpath('theme/eldy/img/title_generic.png',2); ?>" alt="" class="hideonsmartphone valignmiddle" id="pictotitle">
                <div class="titre inline-block"><?php  print $langs->trans('RoadmapStep'); ?></div>
            </td>
            <td style="text-align:right;" >
            	<a class="butAction" href="<?php print dol_buildpath('/productcomposer/card_roadmapdet.php',2).'?action=create&amp;fk_pcroadmap='.$object->id ?>" ><i class="fa fa-plus"></i> <?php print $langs->trans('AddNewRoadMapStep'); ?></a>
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
            <th class="liste_titre" ><?php print $langs->trans('Type'); ?></th>
            
            <th class="liste_titre" ></th>
            <th class="liste_titre" ></th>
        </tr>
    </thead>
<?php if(!empty($TchildrenList)){ ?>
    <tbody>
    	<?php foreach($TchildrenList as $roadmapStep){ ?>
        <tr class="oddeven" data-lineid="<?php print $roadmapStep->id; ?>" >
            <td  ><a href="<?php print dol_buildpath('/productcomposer/card_roadmapdet.php',2).'?id='.$roadmapStep->id; ?>" ><?php print $roadmapStep->label; ?></a></td>
            <td  ><a href="<?php print dol_buildpath('/categories/viewcat.php?type=product',2).'&amp;id='.$roadmapStep->fk_categorie; ?>" ><?php print $roadmapStep->category_label; ?></a></td>
            <td  ><?php print PCRoadMapDet::translateTypeConst($roadmapStep->type); ?></td>
            <td class="productcomposer_linecolmove" ></td>
            <td ><a href="<?php print dol_buildpath('/productcomposer/card_roadmapdet.php',2).'?action=edit&id='.$roadmapStep->id; ?>" ><?php print img_edit(); ?></a></td>
            
        </tr>
        <?php } ?>
<?php } ?>
    </tbody>
</table>
<?php if(!empty($TchildrenList)){ ?>

<?php } ?>

</div>

<?php if(!empty($TchildrenList)){ ?>
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
    	            post: 'roadmapRankDet',
					objet_id: <?php print $object->id; ?>,
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




