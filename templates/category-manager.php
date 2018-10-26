<?php response_header(); ?>

<script src="/javascript/TreeMenu/TreeMenu.js"></script>
<script>
<!--
	currentHighlight = null;

	function category_click(e, obj, catID)
	{
		if (e === null && currentHighlight !== null) {
			document.getElementById(currentHighlight).className = 'treeMenuOff';
			currentHighlight = null;
			updateForm(null);
			return;
		} else if (e === null) {
			return;
		}

		if (e) {
			e.cancelBubble = true;
		}

		var layerID = obj.parentNode.parentNode.id;

		if (currentHighlight != layerID) {
			if (currentHighlight !== null) {
				document.getElementById(currentHighlight).className = 'treeMenuOff';
			}
			var layerRef = document.getElementById(layerID);
			layerRef.className = (layerRef.className == 'treeMenuOff' ? 'treeMenuOn' : 'treeMenuOff');
			updateForm(catID);
			currentHighlight = layerID;
		}
	}

	/**
    * Updates form with correct category parent name/id
    */
	function updateForm(catID)
	{
		var formObj = document.forms['category_form'];
		if (catID !== null) {
			formObj.parent_text.value     = categories[catID][0];
			formObj.cat_parent.value  = catID;
			formObj.delete_text.value     = categories[catID][0];
			formObj.cat_delete.value      = catID;
		} else {
			formObj.parent_text.value     = 'none';
			formObj.cat_parent.value  = '';
			formObj.delete_text.value     = 'none';
			formObj.cat_delete.value      = '';
		}
	}

	/**
    * Called when update checkbox is clicked
    */
	function updateCheckToggle(obj)
	{
		var newStatus = obj.checked;
		var formObj = document.forms['category_form'];

		if (newStatus && formObj.parent_text.value != 'none') {
			formObj.catName.value = categories[formObj.cat_parent.value][0];
			formObj.catDesc.value = categories[formObj.cat_parent.value][1] == 'none' ? '' : categories[formObj.cat_parent.value][1];
			formObj.addUpdateSubmit.value = 'Update category';
			formObj.action.value = 'update';
		} else {
			formObj.catName.value = '';
			formObj.catDesc.value = '';
			formObj.addUpdateSubmit.value = 'Add category';
			formObj.action.value = 'add';
		}
	}

	categories = new Array();

	<?php foreach($categories as $c): ?>
categories[<?php echo $c['id']; ?>] = new Array('<?php echo $c['name']; ?>', '<?php echo $c['description']; ?>');
	<?php endforeach; ?>
//-->
</script>

<table>
	<tr>
		<td>
			<div class="treeMenuContainer" onclick="category_click(null)">
				<?php $treeMenuPres->printMenu(); ?>
			</div>
		</td>
		<td valign="top">
			<span class="error"><?php echo @$message; ?></span>

			<form action="category-manager.php" name="category_form" method="post">
			<input type="hidden" name="action" value="" />
			<input type="hidden" name="cat_parent" value="" />
			<input type="hidden" name="cat_delete" value="" />

			<h2>Add new category</h2>
			<table>
				<tr>
					<td>Parent:</td>
					<td><input type="text" value="none" size="25" disabled="disabled" name="parent_text" /></td>
				</tr>
				<tr>
					<td>Name</td>
					<td><input type="text" name="catName" size="25" /></td>
				</tr>
				<tr>
					<td>Description</td>
					<td><input type="text" name="catDesc" size="25" /></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><input type="checkbox" name="update_checkbox" onclick="updateCheckToggle(this)" id="update_checkbox" /> <label for="update_checkbox">Update instead of addition?</label></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><input type="submit" value="Add category" name="addUpdateSubmit" onclick="document.forms['category_form'].action.value = (this.value == 'Add category' ? 'add' : 'update')" /></td>
				</tr>
			</table>

			<h2>Delete category</h2>
			<table>
				<tr>
					<td>Selected:</td>
					<td><input type="text" name="delete_text" value="none" size="25" disabled="disabled" /></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td><input type="submit" value="Delete category" onclick="document.forms['category_form'].action.value = 'delete'; return confirm('Are you sure you wish to delete this category?')" /></td>
				</tr>
			</table>
			</form>
		</td>
	</tr>
</table>


<?php response_footer();
