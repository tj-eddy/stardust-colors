{if $infos|@count > 0}
<!-- MODULE Block reinsurance -->
<div id="reinsurance_block" class="clearfix">
	<div class="container">
		<ul class="width{$nbblocks}">
			{foreach from=$infos item=info}
				<li  {if $smarty.foreach.infos.last}class="hide_vignette_mobile"{/if} >
					<a href="{if $info.lien!='#'}{$info.lien}{else}javascript:;{/if}" {if $info.new_window==1}target="_blank"{/if} title="{$info.text|regex_replace:"/[\r\n]/" : " "}">
						<img src="{$link->getMediaLink("`$module_dir`img/`$info.file_name|nl2br|escape:'html':'UTF-8'`")}" alt="{$info.text|escape:html:'UTF-8'}" />
						<span>{$info.text|escape:html:'UTF-8'}</span>
					</a>
				</li>
			{/foreach}
		</ul>
	</div>
</div>
<!-- /MODULE Block reinsurance -->
{/if}

{* Ajout une barre horizontale à la fin de la liste *}
{foreach from=$items key=part_id item=prod name=products}
	<a href="#{$part_id}">{$prod}</a>
	{foreachelse}
{/foreach}