function toggleVisibility(id) {
		// OK, kedze je includnuty prototype, mozme ho pouzit
		var element = $(id);
		if (!element.hasClassName('collapsed') ) {
				element.addClassName('collapsed');
		} else {
				element.removeClassName('collapsed');
		}
		
}
