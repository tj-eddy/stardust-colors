<div class="row">
    <div class="reseausociaux col-sm-6">
        {if (isset($titremodules))}
            <div class="titlebloc">{$titremodules}</div>{/if}
        {if (isset($secondetitre))}
            <p class="secondtitlebloc">{$secondetitre}</p>{/if}
        {if (isset($facebooklink))}<a href="https://www.facebook.com/{$facebooklink}"
                                      title="{l s='Suivez-nous sur facebook'}" target="_blank">
                <img src="/modules/arobasesreseauxsociaux/images/iconfacebook.png" alt="icon_facebook"
                     class="jQueryRotate"/>
            </a>{/if}
        {if (isset($twitter))}<a href="https://twitter.com/{$twitter}" title="{l s='Suivez-nous sur Twitter'}"
                                 target="_blank">
                <img src="/modules/arobasesreseauxsociaux/images/icontwitter.png" alt="icon_twitter"
                     class="jQueryRotate"/>
            </a>{/if}
        {if (isset($googleplus))}<a href="https://plus.google.com/{$googleplus}"
                                    title="{l s='Suivez-nous sur Google +'}" target="_blank">
                <img src="/modules/arobasesreseauxsociaux/images/icongoogle+.png" alt="icon_google+"
                     class="jQueryRotate"/>
            </a>{/if}
        {if (isset($instagram))}<a href="https://www.instagram.com/{$instagram}"
                                   title="{l s='Suivez-nous sur Instagram'}" target="_blank">
                <img src="/modules/arobasesreseauxsociaux/images/iconinstagram.png" alt="icon_instagram"
                     class="jQueryRotate"/>
            </a>{/if}
        <a href="https://www.youtube.com/stardustcolors1" title="{l s='Suivez-nous sur youtube'}" target="_blank">
            <img class="jQueryRotate"
                 src="https://stardusthost.stardustcolors.com/themes/panda/assets/img/icone-youtube5.png" width="50"
                 alt="">
        </a>

        {*<a
                href="https://stardusthost.stardustcolors.com/documents/fr/catalogue.pdf"
                target="_blank">
            <img class="download_pdf_social"
                 src="https://stardusthost.stardustcolors.com/themes/panda/assets/img/icone_bas_traduit_FR-min.png"  alt="download"></a>*}

        <div class="chaine-youtube">
            <iframe width="420" height="345" src="{$url_youtube_embed}">
            </iframe>
        </div>
    </div>
    <hr class="vl_footer_rs">
    <div class="catalogue col-sm-6">
        <div class="titlebloc">{l s='Télécharger notre'}</div>
        <span class="titlebloc e-cat">{l s='e-catalogue'}</span>
    </div>
</div>
