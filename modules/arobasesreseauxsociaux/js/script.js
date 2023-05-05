jQuery('document').ready(function(){
	//$('.reseausociaux a').rotate();
	jQuery('.reseausociaux a').hover(
		function () {
			jQuery(this).find('.jQueryRotate').rotate({animateTo: 360,duration: 1000});
		}, function () {
			jQuery(this).find('.jQueryRotate').rotate({animateTo: 0,duration: 1000});
		}
	);
})