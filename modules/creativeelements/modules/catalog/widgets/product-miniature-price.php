<?php
/**
 * Creative Elements - live PageBuilder
 *
 * @author    WebshopWorks
 * @copyright 2019-2023 WebshopWorks.com
 * @license   One domain support license
 */

namespace CE;

defined('_PS_VERSION_') or exit;

include_once __DIR__ . '/product-price.php';

class WidgetProductMiniaturePrice extends WidgetProductPrice
{
    /**
     * Get widget name.
     *
     * @since 2.5.9
     *
     * @return string Widget name
     */
    public function getName()
    {
        return 'product-miniature-price';
    }

    /**
     * Register product price widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 2.5.9
     */
    protected function _registerControls()
    {
        parent::_registerControls();

        $this->updateControl('price_color', ['scheme' => '']);

        $this->updateControl('typography_font_family', ['scheme' => '']);
        $this->updateControl('typography_font_weight', ['scheme' => '']);
    }

    protected function getHtmlWrapperClass()
    {
        return parent::getHtmlWrapperClass() . ' elementor-widget-product-price';
    }

    /**
     * Render product price widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 2.5.9
     */
    protected function render()
    {
        $context = \Context::getContext();
        $product = &$context->smarty->tpl_vars['product']->value;

        if (!$product['show_price']) {
            return;
        }
        $currency = &$context->smarty->tpl_vars['currency']->value;

        $settings = $this->getSettingsForDisplay();
        $t = $context->getTranslator(); ?>
        <div class="ce-product-prices">
        <?php if ($settings['regular'] && $product['has_discount']) { ?>
            <?php echo \Hook::exec('displayProductPriceBlock', ['product' => $product, 'type' => 'old_price']); ?>
            <div class="ce-product-price-regular"><?php echo $product['regular_price']; ?></div>
        <?php } ?>
            <?php echo \Hook::exec('displayProductPriceBlock', ['product' => $product, 'type' => 'before_price']); ?>
            <div class="ce-product-price <?php echo $product['has_discount'] ? 'ce-has-discount' : ''; ?>">
                <span><?php echo $product['price']; ?></span>
        <?php if ($settings['discount'] && $product['has_discount']) { ?>
            <?php if ('percentage' === $product['discount_type']) { ?>
                <span class="ce-product-badge ce-product-badge-sale ce-product-badge-sale-percentage">
                    <?php echo $t->trans('Save %percentage%', ['%percentage%' => $product['discount_percentage_absolute']], 'Shop.Theme.Catalog'); ?>
                </span>
            <?php } else { ?>
                <span class="ce-product-badge ce-product-badge-sale ce-product-badge-sale-amount">
                    <?php echo $t->trans('Save %amount%', ['%amount%' => $product['discount_to_display']], 'Shop.Theme.Catalog'); ?>
                </span>
            <?php } ?>
        <?php } ?>
            </div>
            <?php echo \Hook::exec('displayProductPriceBlock', ['product' => $product, 'type' => 'unit_price']); ?>
            <?php echo \Hook::exec('displayProductPriceBlock', ['product' => $product, 'type' => 'weight']); ?>
        </div>
        <?php
    }

    protected function renderSmarty()
    {
        $settings = $this->getSettingsForDisplay(); ?>
        {if $product['show_price']}
            <div class="ce-product-prices">
        <?php if ($settings['regular']) { ?>
            {if $product['has_discount']}
                {hook h='displayProductPriceBlock' product=$product type='old_price'}
                <div class="ce-product-price-regular">{$product['regular_price']}</div>
            {/if}
        <?php } ?>
                {hook h='displayProductPriceBlock' product=$product type='before_price'}
                <div class="ce-product-price{if $product['has_discount']} ce-has-discount{/if}">
                    <span>{$product['price']}</span>
        <?php if ($settings['discount']) { ?>
            {if $product['has_discount']}
                {if 'percentage' === $product['discount_type']}
                    <span class="ce-product-badge ce-product-badge-sale ce-product-badge-sale-percentage">
                        {l s='Save %percentage%' sprintf=['%percentage%' => $product['discount_percentage_absolute']] d='Shop.Theme.Catalog'}
                    </span>
                {else}
                    <span class="ce-product-badge ce-product-badge-sale ce-product-badge-sale-amount">
                        {l s='Save %amount%' sprintf=['%amount%' => $product['discount_to_display']] d='Shop.Theme.Catalog'}
                    </span>
                {/if}
            {/if}
        <?php } ?>
                </div>
                {hook h='displayProductPriceBlock' product=$product type='unit_price'}
                {hook h='displayProductPriceBlock' product=$product type='weight'}
            </div>
        {/if}
        <?php
    }
}
