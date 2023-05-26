{if $infos|@count > 0}
<!-- MODULE Block reinsurance -->
<div id="reinsurance_block" class="clearfix">
	<div class="container">
		<ul class="width{$nbblocks}">
			{foreach from=$infos item=info}
				<li>
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