{*
* 2007-2017 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    ST-themes <hellolee@gmail.com>
*  @copyright 2007-2017 ST-themes
*  @license   Use, by you or one client for one Prestashop instance.
*}
{if isset($news_letter_array) && count($news_letter_array)}
	<!-- MODULE st stnewsletter -->
	{assign var='has_news_letter_popup' value=0}
    {foreach $news_letter_array as $ec} 
        {if $ec.location==4}
        	{if !$has_news_letter_popup}
        		{$has_news_letter_popup=1}
        		<div class="modal fade st_news_letter_popup_wrap" id="st_news_letter_popup_{$ec.id_st_news_letter}" data-id_st="{(int)$ec.id_st_news_letter}" data-delay_popup="{(int)$ec.delay_popup}" data-hide_on_mobile="{(int)$ec.hide_on_mobile}" data-show_popup="{(int)$ec.show_popup}" data-cookies_time="{(int)$ec.cookies_time}" data-showonclick="{if isset($ec.showonclick) && $ec.showonclick}1{else}0{/if}" tabindex="-1" role="dialog" aria-labelledby="{l s='Newsletter' d='Shop.Theme.Panda'}" aria-hidden="true">
	        		<div class="modal-dialog" role="document">
		      			<div class="modal-content">
				          <button type="button" class="close st_modal_close" data-dismiss="modal" aria-label="{l s='Close' d='Shop.Theme.Panda'}">
				            <span aria-hidden="true">&times;</span>
				          </button>
					        <div class="modal-body st_modal_body">
					        	<div id="st_news_letter_{$ec.id_st_news_letter}" class="st_news_letter st_news_letter_popup {if !$ec.template} text-{$ec.text_align} text-md-2 {/if}">
				         	      <div class="st_news_letter_box">
				                    <div class="alert alert-danger hidden"></div>
				                    <div class="alert alert-success hidden"></div> 
                                    <div class="news_letter_{$ec.template} {if $ec.template} flex_container flex_column_md text-md-2 {/if}">    
					            	{if $ec.content}<div class="st_news_letter_content style_content flex_child {if $ec.template} m-r-1 {/if}">{$ec.content nofilter}</div>{/if}
					            	{if $ec.show_newsletter}
					            	<form action="{$ajax_url}" method="post" class="st_news_letter_form flex_child_md">
					            		{if isset($ec.show_gender) && $ec.show_gender}
				                            <div class="st_news_letter_gender">
				                            {foreach Gender::getGenders() key=k item=gender}
				                                <label for="id_gender{$gender->id}" class="radio-inline">
				                                <input type="radio" name="id_gender" id="id_gender{$gender->id}" value="{$gender->id|intval}" {if isset($smarty.post.id_gender) && $smarty.post.id_gender == $gender->id}checked="checked"{/if} />
				                                {$gender->name}</label>
				                            {/foreach}
				                            </div>
				                        {/if}
										{hook h='displayGDPRConsent' id_module=$id_module}
				                        <div class="st_news_letter_form_inner">
					                        <div class="input-group round_item js-parent-focus input-group-with-border" >
												<input class="form-control st_news_letter_input js-child-focus" type="text" name="email" value="{if isset($value) && $value}{$value}{/if}" placeholder="{l s='Your e-mail' d='Shop.Theme.Panda'}" />
								                <span class="input-group-btn">
								                	<button type="submit" name="submitStNewsletter" class="btn btn-less-padding st_news_letter_submit link_color">
									                    {l s='Subscribe' d='Shop.Theme.Panda'}
									                </button>
								                </span>
											</div>
										</div>
										<input type="hidden" name="action" value="0" />
										<input type="hidden" name="submitNewsletter" value="1" />
									</form>				
									{/if}
                                    </div>
				            	  </div>
									{if !$ec.show_popup}
									<div class="st_news_letter_do_not_show_outer clearfix">
                                        <div class="st_news_letter_do_not_show_inner form-check fr mb-0">
                                            <label class="form-check-label">
                                              <input type="checkbox" name="st_news_letter_do_not_show" class="st_news_letter_do_not_show form-check-input" autocomplete="off" />
                                              {l s='Do not show again' d='Shop.Theme.Panda'}
                                            </label>
                                        </div>
									</div>
									{/if}	
					            </div>
					        </div>
				        </div>
			        </div>
		        </div>
        	{/if}
        {else}
	        {if isset($ec.is_full_width) && $ec.is_full_width}<div id="st_news_letter_container_{$ec.id_st_news_letter}" class="st_news_letter_container full_container {if $ec.hide_on_mobile}hidden-sm-down{/if} block"><div class="container"><div class="row"><div class="col-12 col-sm-12">{/if}
	            <div id="st_news_letter_{$ec.id_st_news_letter}" class="st_news_letter_{$ec.id_st_news_letter} {if $ec.hide_on_mobile}hidden-sm-down{/if} {if !isset($ec.is_full_width) || !$ec.is_full_width}block{/if} st_news_letter {if isset($ec.is_column) && $ec.is_column} column_block {/if}">
                    {if $ec.title_align!=3}
                    <div class="title_block flex_container title_align_{(int)$ec.title_align} {if isset($ec.sub_title) && $ec.sub_title} st_has_sub_title {/if}">
                        <div class="flex_child title_flex_left"></div>
                        <div class="title_block_inner">
                        	{if isset($ec.title) && $ec.title}{$ec.title}{else}{l s='Newsletter' d='Shop.Theme.Panda'}{/if}
                        </div>
                        <div class="flex_child title_flex_right"></div>
                    </div>
					{if isset($ec.sub_title) && $ec.sub_title}<div class="slider_sub_title">{$ec.sub_title nofilter}</div>{/if}
                    {/if}
	            	<div class="st_news_letter_box {if !$ec.template} text-{$ec.text_align} text-md-2 {/if} block_content">
                    <div class="alert alert-danger hidden"></div>
                    <div class="alert alert-success hidden"></div>
                    <div class="news_letter_{$ec.template} {if $ec.template} flex_container flex_column_md {/if}"> 
                    {if $ec.content}<div class="st_news_letter_content style_content flex_child {if $ec.template} m-r-1 {/if}">{$ec.content nofilter}</div>{/if}
                    {if $ec.show_newsletter}
	            	<form action="{$ajax_url}" method="post" class="st_news_letter_form flex_child_md">
                            {if $ec.show_gender}
                            <label>{l s='Title' d='Shop.Theme.Panda'}</label>
                            {foreach Gender::getGenders() key=k item=gender}
                                <label for="id_gender{$gender->id}" class="radio-inline">
                                <input type="radio" name="id_gender" id="id_gender{$gender->id}" value="{$gender->id|intval}" {if isset($smarty.post.id_gender) && $smarty.post.id_gender == $gender->id}checked="checked"{/if} />
                                {$gender->name}</label>
                            {/foreach}
                            {/if}
							{if empty($ec.gdpr_options)}
							{hook h='displayGDPRConsent' id_module=$id_module}
							{/if}
                            <div class="st_news_letter_form_inner">
                            {capture name="stnewsletter_button"}
			                    {if (isset($ec.button_icon) && $ec.button_icon['value']!='') || (isset($ec.button_text) && $ec.button_text!='')}
								{if (isset($ec.button_icon) && $ec.button_icon['value']!='')}
								<i class="{$ec.button_icon['value']}"></i>
								{/if}
								{if (isset($ec.button_text))}
								{$ec.button_text}
								{/if}
								{else}
								{l s='Subscribe' d='Shop.Theme.Panda'}
								{/if}
                            {/capture}
	                        <div class="input-group round_item js-parent-focus input-group-with-border {if !empty($ec.gdpr_options)} mb-2 {/if}" >
								<input class="form-control st_news_letter_input js-child-focus" type="text" name="email" value="{if isset($value) && $value}{$value}{/if}" placeholder="{l s='Your e-mail' d='Shop.Theme.Panda'}" />
								{if !isset($ec.button_layout) || $ec.button_layout=='1'}
				                <span class="input-group-btn">
				                	<button type="submit" name="submitStNewsletter" class="btn btn-less-padding st_news_letter_submit link_color">{$smarty.capture.stnewsletter_button nofilter}</button>
				                </span>
								{/if}
							</div>
							{if !empty($ec.gdpr_options)}
							{hook h='displayGDPRConsent' id_module=$id_module}
							{/if}
							{if isset($ec.button_layout) && $ec.button_layout=='2'}
			                	<button type="submit" name="submitStNewsletter" class="btn btn-default st_news_letter_submit mar_t10">{$smarty.capture.stnewsletter_button nofilter}</button>
							{/if}
							</div>
							<input type="hidden" name="action" value="0" />
							<input type="hidden" name="submitNewsletter" value="1" />
					</form>
					{/if}
                    </div>
                    </div>
	            </div>
	        {if isset($ec.is_full_width) && $ec.is_full_width}</div></div></div></div>{/if}
        {/if}
    {/foreach}
	<!-- /MODULE st stnewsletter -->
{/if}