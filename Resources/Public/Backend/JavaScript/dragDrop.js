
Event.observe(window, 'load', function() {
	var draggableElements = $$('div.t3-page-ce-dragitem');
	
	draggableElements.each(function(el) {
		Event.observe(el,'mousedown',function(evt) {
			var colList = $$('div#' + this.id + ' span.disableDragDrop')[0].readAttribute('data-cols').split(',');
			
			colList.each(function(col) {
				$$('.t3-page-column-' + col)[0].addClassName('contentDesigner-droptarget-hidden');
				/*$$('.t3-page-column-' + col + ' .t3-page-ce-dropzone').each(function(dropZone) {
					dropZone.addClassName('contentDesigner-hidden');
				});*/
			});
		});
		
		Event.observe(el,'mouseup',function(evt) {
			var colList = $$('div#' + this.id + ' span.disableDragDrop')[0].readAttribute('data-cols').split(',');
			
			colList.each(function(col) {
				$$('.t3-page-column-' + col)[0].removeClassName('contentDesigner-droptarget-hidden');
				/*$$('.t3-page-column-' + col + ' .t3-page-ce-dropzone').each(function(dropZone) {
					dropZone.removeClassName('contentDesigner-hidden');
				});*/
			});
		});
		
	});
});