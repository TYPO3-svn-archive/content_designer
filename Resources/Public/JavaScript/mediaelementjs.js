$(function() {
	if ( $('video,audio') ) {
		$('video,audio').mediaelementplayer({
			pluginPath: '/typo3conf/ext/content_designer/Resources/Public/JavaScript/mediaelementjs/',
			iPadUseNativeControls: true,
			iPhoneUseNativeControls: true
		});
	}
});