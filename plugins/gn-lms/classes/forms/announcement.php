<form method="POST" class="gnlms_data_form">
<input type="hidden" name="action" value="gnlms-add-edit-announcement"/>
<input type="hidden" name="gnlms_nonce" value="<?php echo $this->nonce; ?>"/>

<?php if($context['id']): ?>  
	<input type="hidden" name="id" value="<?php echo($context['id']); ?>" />
<?php endif; ?>
<label><input type="text" name="title" value="<?php echo htmlspecialchars($context['title']); ?>"/> Title</label>
<textarea name="text" id="text"><?php echo(htmlspecialchars(trim($context['text']))); ?></textarea>
<script type="text/javascript">

	tinyMCE.init({
		mode : "exact",
		// width:"698px",
		elements: "text",
		theme : "advanced",
		theme_advanced_toolbar_location: 'top',
		theme_advanced_buttons2 : "bullist,numlist,separator,outdent,indent,separator,undo,redo,separator",
		theme_advanced_buttons3 : ""

	});


</script>
</form>