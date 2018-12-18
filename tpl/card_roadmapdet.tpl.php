<!-- Un début de <div> existe de par la fonction dol_fiche_head() -->
	<input type="hidden" name="action" value="[view.action]" />
	<input type="hidden" name="fk_pcroadmap" value="[view.fk_pcroadmap]" />
	
	<table width="100%" class="border" id="form-detroadmap">
		<tbody>

			<tr class="label" >
				<td width="25%">[langs.transnoentities(Label)]</td>
				<td>[view.showLabel;strconv=no]</td>
			</tr>
			
			<tr class="label"  id="type_line" data-value="[object.type;strconv=no]" >
				<td width="25%">[langs.transnoentities(Type)]</td>
				<td>[view.showType;strconv=no]</td>
			</tr>
			
			<tr class="label showOnType3 " id="goto_line" >
				<td width="25%">[help.help_Goto;strconv=no]</td>
				<td>[view.showGoto;strconv=no]</td>
			</tr>
			
			<tr class="label  showOnType2 showOnType1" id="category_line" >
				<td width="25%">[langs.transnoentities(Category)]</td>
				<td>[view.showCat;strconv=no]</td>
			</tr>
			
			<tr class="label showOnType2" id="linktoroadmap_line" >
				<td width="25%">[help.help_LinkToRoadmapCat;strconv=no]</td>
				<td>[view.showLinkToRoadmapCat;strconv=no]</td>
			</tr>
			
			<tr class="label  showOnType2" id="linkToPrevCat_line" >
				<td width="25%">[help.help_LinkToPrevCat;strconv=no]</td>
				<td>[view.showLinkToPrevCat;strconv=no]</td>
			</tr>
			
			<tr class="label  showOnType2" id="forcePriceToZero_line" >
				<td width="25%">[help.help_forcePriceToZero;strconv=no]</td>
				<td>[view.showNoPrice;strconv=no]</td>
			</tr>
			
			<tr class="label  showOnType2" id="addDesc_line" >
				<td width="25%">[help.help_AddDesc;strconv=no]</td>
				<td>[view.showFlagDesc;strconv=no]</td>
			</tr>
			
			<tr class="label  showOnType2"  id="optional_line" >
				<td width="25%">[help.help_optional;strconv=no]</td>
				<td>[view.showOptional;strconv=no]</td>
			</tr>
			
		</tbody>
	</table>
<script type="text/javascript" language="javascript">
$(document).ready(function() {


    $('#form-detroadmap [class*="showOnType"]').hide();
    $(".showOnType"+ $("#type_line").data('value') ).show();

	
	$("#type").change(function() {
        $("#form-detroadmap [class*='showOnType']").hide();
        $(".showOnType"+ $( this ).val() ).show();
	});
});
</script>


</div> <!-- Fin div de la fonction dol_fiche_head() -->

<!-- Permet de load correctement le choix par défaut en cas de création -->
[onshow;block=begin;when [view.realaction]='create']
<script type="text/javascript" language="javascript">
$(document).ready(function() {
        $('#type').val(2).change();
});
</script>
[onshow;block=end]


[onshow;block=begin;when [view.mode]='edit']
<div class="center">
	
	<!-- '+-' est l'équivalent d'un signe '>' (TBS oblige) -->
	[onshow;block=begin;when [object.getId()]+-0]
	<input type='hidden' name='id' value='[object.getId()]' />
	<input type="submit" value="[langs.transnoentities(Save)]" class="butAction" />
	[onshow;block=end]
	
	[onshow;block=begin;when [object.getId()]=0]
	<input type="submit" value="[langs.transnoentities(Add)]" class="butAction" />
	[onshow;block=end]
	
	<input type="button" onclick="javascript:history.go(-1)" value="[langs.transnoentities(Cancel)]" class="butAction">
	
</div>
[onshow;block=end]

[onshow;block=begin;when [view.mode]!='edit']
<div class="tabsAction">

   <div class="inline-block divButAction" style="text-align:left; float: left;" ><a href="[view.urllist]" class="butAction"><i class="fa fa-arrow-left"></i> [langs.transnoentities(Back)]</a></div>
	

	[onshow;block=begin;when [user.rights.productcomposer.write;noerr]=1]
	
	<div class="inline-block divButAction"><a href="[view.urlcard]?id=[object.getId()]&action=edit" class="butAction">[langs.transnoentities(Modify)]</a></div>
			
	<div class="inline-block divButAction"><a href="[view.urlcard]?id=[object.getId()]&action=delete" class="butActionDelete">[langs.transnoentities(Delete)]</a></div>
			
	
		

	
		
	[onshow;block=end]
</div>
[onshow;block=end]