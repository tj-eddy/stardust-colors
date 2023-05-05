<div class="reseausociaux col-sm-12">
	{if (isset($titremodules))}<div class="titlebloc">{$titremodules}</div>{/if}
	{if (isset($facebooklink))}<a href="https://www.facebook.com/{$facebooklink}" title="{l s='Suivez-nous sur facebook'}" target="_blank">
			<img src="modules/arobasesreseauxsociaux/images/iconfacebook.png" alt="icon_facebook" class="jQueryRotate"/>
	</a>{/if}
	{if (isset($twitter))}<a href="https://twitter.com/{$twitter}" title="{l s='Suivez-nous sur Twitter'}" target="_blank">
		<img src="modules/arobasesreseauxsociaux/images/icontwitter.png" alt="icon_twitter" class="jQueryRotate"/>
	</a>{/if}
	{if (isset($googleplus))}<a href="https://plus.google.com/{$googleplus}" title="{l s='Suivez-nous sur Google +'}" target="_blank">
		<img src="modules/arobasesreseauxsociaux/images/icongoogle+.png" alt="icon_google+" class="jQueryRotate"/>
	</a>{/if}
	{if (isset($instagram))}<a href="https://www.instagram.com/{$instagram}" title="{l s='Suivez-nous sur Instagram'}" target="_blank">
		<img src="modules/arobasesreseauxsociaux/images/iconinstagram.png" alt="icon_instagram" class="jQueryRotate"/>
	</a>{/if}
	<div class="clear"></div>
</div>