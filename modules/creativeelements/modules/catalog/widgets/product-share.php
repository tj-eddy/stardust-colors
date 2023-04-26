<?php
/**
 * Creative Elements - live Theme & Page Builder
 *
 * @author    WebshopWorks
 * @copyright 2019-2023 WebshopWorks.com
 * @license   One domain support license
 */

namespace CE;

defined('_PS_VERSION_') or exit;

class WidgetProductShare extends WidgetSocialIcons
{
    public function getName()
    {
        return 'product-share';
    }

    public function getTitle()
    {
        return __('Product Share');
    }

    public function getIcon()
    {
        return 'eicon-share';
    }

    public function getCategories()
    {
        return ['product-elements'];
    }

    public function getKeywords()
    {
        return ['shop', 'store', 'share', 'social', 'product'];
    }

    protected function getSocialIconListControls()
    {
        $controls = parent::getSocialIconListControls();

        $controls['social_icon'] = [
            'name' => 'social_icon',
            'label' => __('Network'),
            'type' => ControlsManager::ICONS,
            'fa4compatibility' => 'social',
            'default' => [
                'value' => 'fab fa-tumblr',
                'library' => 'fa-brands',
            ],
            'recommended' => [
                'fa-brands' => [
                    'facebook',
                    'twitter',
                    'pinterest',
                    'tumblr',
                ],
            ],
        ];

        $controls['link'] = [
            'name' => 'link',
            'type' => ControlsManager::URL,
            'classes' => 'elementor-hidden',
            'default' => [
                'is_external' => 'true',
            ],
        ];

        return $controls;
    }

    protected function _registerControls()
    {
        parent::_registerControls();

        $this->updateControl('section_social_icon', [
            'label' => __('Product Share'),
        ]);

        $this->updateControl('social_icon_list', [
            'default' => [
                [
                    'social_icon' => [
                        'value' => 'fab fa-facebook',
                        'library' => 'fa-brands',
                    ],
                ],
                [
                    'social_icon' => [
                        'value' => 'fab fa-twitter',
                        'library' => 'fa-brands',
                    ],
                ],
                [
                    'social_icon' => [
                        'value' => 'fab fa-pinterest',
                        'library' => 'fa-brands',
                    ],
                ],
            ],
        ]);
    }

    protected function render()
    {
        $context = \Context::getContext();

        if ($context->controller instanceof \ProductController) {
            $product = $context->controller->getProduct();
            $image_cover_id = $product->getCover($product->id);

            if (is_array($image_cover_id) && isset($image_cover_id['id_image'])) {
                $image_cover_id = (int) $image_cover_id['id_image'];
            } else {
                $image_cover_id = 0;
            }
            $url = urlencode(addcslashes($context->link->getProductLink($product), "'"));
            $name = urlencode(addcslashes($product->name, "'"));
            $img = urlencode(addcslashes($context->link->getImageLink($product->link_rewrite, $image_cover_id), "'"));
        } else {
            $url = '';
            $name = '';
            $img = '';
        }

        $social_icon_list = $this->getSettings('social_icon_list');

        foreach ($social_icon_list as &$item) {
            $social = $this->getSocialBrand($item);

            switch ($social) {
                case 'facebook':
                    $item['link']['url'] = "https://www.facebook.com/sharer.php?u=$url";
                    break;
                case 'twitter':
                    $item['link']['url'] = "https://twitter.com/intent/tweet?text=$name $url";
                    break;
                case 'pinterest':
                    $item['link']['url'] = "https://www.pinterest.com/pin/create/button/?media=$img&url=$url";
                    break;
                case 'tumblr':
                    $item['link']['url'] = "https://www.tumblr.com/share/link?url=$url";
                    break;
            }
        }
        $this->setSettings('social_icon_list', $social_icon_list);

        parent::render();
    }

    public function renderPlainContent()
    {
    }
}
