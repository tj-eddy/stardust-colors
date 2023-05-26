<div class="row">
    <div class="reseausociaux col-sm-8">
       <div class="btn-rs">
           {if (isset($titremodules))}
               <div class="titlebloc">{$titremodules}</div>{/if}
           {if (isset($secondetitre))}
               <p class="secondtitlebloc">{$secondetitre|nl2br}</p>{/if}
           <div class="btn-rotate">
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
               <a href="https://www.youtube.com/{$ytbLink}" title="{l s='Suivez-nous sur youtube'}" target="_blank">
                   <img class="jQueryRotate"
                        src="https://stardusthost.stardustcolors.com/themes/panda/assets/img/icone-youtube5.png" width="50"
                        alt="">
               </a>
           </div>
       </div>

        {if isset($url_youtube_embed)}
            <div class="row cy-bloc">
                <div class="col cy-url">
                    <div class="chaine-youtube">
                        <iframe width="400" height="300" src="{$url_youtube_embed}"></iframe>
                    </div>
                </div>
                <div class="col cy-desc">
                    <h3>{$ytb_title}</h3>
                    <p>{$ytb_desc|nl2br}</p>
                    <a href="https://www.youtube.com/{$ytbLink}" target="_blank" class="btn btn-primary btn-sm">{l s="Je découvre"} ></a>
                </div>
            </div>
        {/if}
    </div>
    <hr class="vl_footer_rs">
    <div class="catalogue col-sm-4">
        <div class="titlebloc">{l s='Télécharger notre'}</div>
        <span class="titlebloc e-cat">{l s='e-catalogue'}</span>
        <div class="catalogue_img">
            <img src="{$imgCatalogue}" alt="catalogue"/>
        </div>
        <div class="download_img">
            <a target="_blank" href="documents/fr/catalogue.pdf"><img src="{$imgDownload}" alt="download"></a>
        </div>
    </div>
</div>
