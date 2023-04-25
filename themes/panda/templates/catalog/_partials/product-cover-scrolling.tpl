{assign var="pro_thumnbs_per_fw" value=$sttheme.pro_thumnbs_per_fw}
{if $sttheme.pro_thumnbs_per_odd_fw}
	{if ($sttheme.pro_thumnbs_per_fw+$sttheme.pro_thumnbs_per_odd_fw)<$curr_index}
		{assign var="curr_index_fw" value=$curr_index%($sttheme.pro_thumnbs_per_fw+$sttheme.pro_thumnbs_per_odd_fw)}
		{if !$curr_index_fw}{assign var="curr_index_fw" value=$sttheme.pro_thumnbs_per_fw+$sttheme.pro_thumnbs_per_odd_fw}{/if}
	{else}
		{assign var="curr_index_fw" value=$curr_index}
	{/if}
	{if $curr_index_fw>$pro_thumnbs_per_fw}
		{assign var="pro_thumnbs_per_fw" value=$sttheme.pro_thumnbs_per_odd_fw}
	{/if}
{/if}
{assign var="pro_thumnbs_per_xxl" value=$sttheme.pro_thumnbs_per_xxl}
{if $sttheme.pro_thumnbs_per_odd_xxl}
	{if ($sttheme.pro_thumnbs_per_xxl+$sttheme.pro_thumnbs_per_odd_xxl)<$curr_index}
		{assign var="curr_index_xxl" value=$curr_index%($sttheme.pro_thumnbs_per_xxl+$sttheme.pro_thumnbs_per_odd_xxl)}
		{if !$curr_index_xxl}{assign var="curr_index_xxl" value=$sttheme.pro_thumnbs_per_xxl+$sttheme.pro_thumnbs_per_odd_xxl}{/if}
	{else}
		{assign var="curr_index_xxl" value=$curr_index}
	{/if}
	{if $curr_index_xxl>$pro_thumnbs_per_xxl}
		{assign var="pro_thumnbs_per_xxl" value=$sttheme.pro_thumnbs_per_odd_xxl}
	{/if}
{/if}
{assign var="pro_thumnbs_per_xl" value=$sttheme.pro_thumnbs_per_xl}
{if $sttheme.pro_thumnbs_per_odd_xl}
	{if ($sttheme.pro_thumnbs_per_xl+$sttheme.pro_thumnbs_per_odd_xl)<$curr_index}
		{assign var="curr_index_xl" value=$curr_index%($sttheme.pro_thumnbs_per_xl+$sttheme.pro_thumnbs_per_odd_xl)}
		{if !$curr_index_xl}{assign var="curr_index_xl" value=$sttheme.pro_thumnbs_per_xl+$sttheme.pro_thumnbs_per_odd_xl}{/if}
	{else}
		{assign var="curr_index_xl" value=$curr_index}
	{/if}
	{if $curr_index_xl>$pro_thumnbs_per_xl}
		{assign var="pro_thumnbs_per_xl" value=$sttheme.pro_thumnbs_per_odd_xl}
	{/if}
{/if}
{assign var="pro_thumnbs_per_lg" value=$sttheme.pro_thumnbs_per_lg}
{if $sttheme.pro_thumnbs_per_odd_lg}
	{if ($sttheme.pro_thumnbs_per_lg+$sttheme.pro_thumnbs_per_odd_lg)<$curr_index}
		{assign var="curr_index_lg" value=$curr_index%($sttheme.pro_thumnbs_per_lg+$sttheme.pro_thumnbs_per_odd_lg)}
		{if !$curr_index_lg}{assign var="curr_index_lg" value=$sttheme.pro_thumnbs_per_lg+$sttheme.pro_thumnbs_per_odd_lg}{/if}
	{else}
		{assign var="curr_index_lg" value=$curr_index}
	{/if}
	{if $curr_index_lg>$pro_thumnbs_per_lg}
		{assign var="pro_thumnbs_per_lg" value=$sttheme.pro_thumnbs_per_odd_lg}
	{/if}
{/if}
{assign var="pro_thumnbs_per_md" value=$sttheme.pro_thumnbs_per_md}
{if $sttheme.pro_thumnbs_per_odd_md}
	{if ($sttheme.pro_thumnbs_per_md+$sttheme.pro_thumnbs_per_odd_md)<$curr_index}
		{assign var="curr_index_md" value=$curr_index%($sttheme.pro_thumnbs_per_md+$sttheme.pro_thumnbs_per_odd_md)}
		{if !$curr_index_md}{assign var="curr_index_md" value=$sttheme.pro_thumnbs_per_md+$sttheme.pro_thumnbs_per_odd_md}{/if}
	{else}
		{assign var="curr_index_md" value=$curr_index}
	{/if}
	{if $curr_index_md>$pro_thumnbs_per_md}
		{assign var="pro_thumnbs_per_md" value=$sttheme.pro_thumnbs_per_odd_md}
	{/if}
{/if}

{assign var="pro_thumnbs_per_sm" value=$sttheme.pro_thumnbs_per_sm}
{if $sttheme.pro_thumnbs_per_odd_sm}
	{if ($sttheme.pro_thumnbs_per_sm+$sttheme.pro_thumnbs_per_odd_sm)<$curr_index}
		{assign var="curr_index_sm" value=$curr_index%($sttheme.pro_thumnbs_per_sm+$sttheme.pro_thumnbs_per_odd_sm)}
		{if !$curr_index_sm}{assign var="curr_index_sm" value=$sttheme.pro_thumnbs_per_sm+$sttheme.pro_thumnbs_per_odd_sm}{/if}
	{else}
		{assign var="curr_index_sm" value=$curr_index}
	{/if}
	{if $curr_index_sm>$pro_thumnbs_per_sm}
		{assign var="pro_thumnbs_per_sm" value=$sttheme.pro_thumnbs_per_odd_sm}
	{/if}
{/if}

{assign var="pro_thumnbs_per_xs" value=$sttheme.pro_thumnbs_per_xs}
{if $sttheme.pro_thumnbs_per_odd_xs}
	{if ($sttheme.pro_thumnbs_per_xs+$sttheme.pro_thumnbs_per_odd_xs)<$curr_index}
		{assign var="curr_index_xs" value=$curr_index%($sttheme.pro_thumnbs_per_xs+$sttheme.pro_thumnbs_per_odd_xs)}
		{if !$curr_index_xs}{assign var="curr_index_xs" value=$sttheme.pro_thumnbs_per_xs+$sttheme.pro_thumnbs_per_odd_xs}{/if}
	{else}
		{assign var="curr_index_xs" value=$curr_index}
	{/if}
	{if $curr_index_xs>$pro_thumnbs_per_xs}
		{assign var="pro_thumnbs_per_xs" value=$sttheme.pro_thumnbs_per_odd_xs}
	{/if}
{/if}

<div class="st_image_scrolling_item {if $pro_thumnbs_per_fw}col-fw-{(12/$pro_thumnbs_per_fw)|replace:'.':'-'}{/if}  {if $pro_thumnbs_per_xxl}col-xxl-{(12/$pro_thumnbs_per_xxl)|replace:'.':'-'}{/if}  {if $pro_thumnbs_per_xl}col-xl-{(12/$pro_thumnbs_per_xl)|replace:'.':'-'}{/if} col-lg-{(12/$pro_thumnbs_per_lg)|replace:'.':'-'} col-md-{(12/$pro_thumnbs_per_md)|replace:'.':'-'} col-sm-{(12/$pro_thumnbs_per_sm)|replace:'.':'-'} col-{(12/$pro_thumnbs_per_xs)|replace:'.':'-'}">
{include file='catalog/_partials/product-cover-item.tpl'}
</div>