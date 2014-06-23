
jQuery(document).ready(function ($) {
	$("form.gn-list-form").each(function () {
		if(window.gn_FormData && gn_FormData[this.id]) {
			gn_populateForm(this, gn_FormData[this.id]);
		}
	});
	
	$("ul.gnlms-treeview").treeview({
		animated: "fast",
		persist: "location",
		collapsed: true,
		unique: false
	});
});