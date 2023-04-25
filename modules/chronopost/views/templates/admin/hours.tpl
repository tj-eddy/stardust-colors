 <select name="chronoparams[{$group_name|escape:'htmlall':'UTF-8'}][{$field_name|escape:'htmlall':'UTF-8'}]">
	{for $i=0 to 23}
		<option value="{$i|escape:'htmlall':'UTF-8'}"{if $i==$selected} selected{/if}>{$i|string_format:'%02d'}</option>
	{/for}
</select>