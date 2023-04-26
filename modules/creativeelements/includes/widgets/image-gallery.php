<?php
/**
 * Creative Elements - live Theme & Page Builder
 *
 * @author    WebshopWorks, Elementor
 * @copyright 2019-2023 WebshopWorks.com & Elementor.com
 * @license   https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace CE;

defined('_PS_VERSION_') or exit;

/**
 * Elementor image gallery widget.
 *
 * Elementor widget that displays a set of images in an aligned grid.
 *
 * @since 1.0.0
 */
class WidgetImageGallery extends WidgetBase
{
    /**
     * Get widget name.
     *
     * Retrieve image gallery widget name.
     *
     * @since 1.0.0
     *
     * @return string Widget name
     */
    public function getName()
    {
        return 'image-gallery';
    }

    /**
     * Get widget title.
     *
     * Retrieve image gallery widget title.
     *
     * @since 1.0.0
     *
     * @return string Widget title
     */
    public function getTitle()
    {
        return __('Image Gallery');
    }

    /**
     * Get widget icon.
     *
     * Retrieve image gallery widget icon.
     *
     * @since 1.0.0
     *
     * @return string Widget icon
     */
    public function getIcon()
    {
        return 'eicon-gallery-grid';
    }

    /**
     * Get widget keywords.
     *
     * Retrieve the list of keywords the widget belongs to.
     *
     * @since 2.1.0
     *
     * @return array Widget keywords
     */
    public function getKeywords()
    {
        return ['images', 'photos', 'visual', 'gallery', 'grid', 'masonry'];
    }

    /**
     * Register image gallery widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 1.0.0
     */
    protected function _registerControls()
    {
        $this->startControlsSection(
            'section_gallery',
            [
                'label' => __('Image Gallery'),
            ]
        );

        $this->addControl(
            'links',
            [
                'type' => ControlsManager::RAW_HTML,
                'raw' => '
                    <style>
                    .elementor-control-links.elementor-hidden-control ~
                    .elementor-control-gallery .elementor-control-link,
                    .elementor-control-links { display: none; }
                    </style>',
                'condition' => [
                    'gallery_link' => 'custom',
                ],
            ]
        );

        $repeater = new Repeater();

        $repeater->addControl(
            'image',
            [
                'label' => __('Choose Image'),
                'type' => ControlsManager::MEDIA,
                'seo' => true,
                'dynamic' => [
                    'active' => true,
                ],
                'default' => [
                    'url' => Utils::getPlaceholderImageSrc(),
                ],
            ]
        );

        $repeater->addControl(
            'caption',
            [
                'label' => __('Caption'),
                'label_block' => true,
                'type' => ControlsManager::TEXT,
                'placeholder' => __('Enter your caption about the image'),
                'title' => __('Input image caption here'),
            ]
        );

        $repeater->addControl(
            'link',
            [
                'label' => __('Link'),
                'type' => ControlsManager::URL,
                'label_block' => true,
                'placeholder' => __('http://your-link.com'),
            ]
        );

        $this->addControl(
            'gallery',
            [
                'label' => __('Images'),
                'type' => ControlsManager::REPEATER,
                'fields' => $repeater->getControls(),
                'default' => [
                    [
                        'image' => [
                            'url' => Utils::getPlaceholderImageSrc(),
                        ],
                    ],
                ],
                'dynamic' => [
                    'active' => true,
                    'categories' => ['gallery'],
                ],
                'title_field' => '<# if (image.url) { #>' .
                    '<img src="{{ elementor.imagesManager.getImageUrl(image) }}" class="ce-repeater-thumb"><# } #>' .
                    '{{{ caption || image.title || image.alt || image.url && image.url.split("/").pop() }}}',
            ]
        );

        $this->addControl(
            'layout',
            [
                'label' => __('Layout'),
                'type' => ControlsManager::SELECT,
                'default' => 'grid',
                'options' => [
                    'grid' => __('Grid'),
                    'masonry' => __('Masonry'),
                ],
                'prefix_class' => 'elementor-image-gallery--layout-',
            ]
        );

        $gallery_columns = range(1, 10);
        $gallery_columns = array_combine($gallery_columns, $gallery_columns);

        $this->addResponsiveControl(
            'gallery_columns',
            [
                'label' => __('Columns'),
                'type' => ControlsManager::SELECT,
                'desktop_default' => 4,
                'tablet_default' => 3,
                'mobile_default' => 2,
                'options' => &$gallery_columns,
                'selectors' => [
                    '{{WRAPPER}} figure' => 'width: calc(100% / {{VALUE}})',
                ],
            ]
        );

        $this->addResponsiveControl(
            'gallery_max_height',
            [
                'label' => __('Max. Height'),
                'type' => ControlsManager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 2000,
                    ],
                    'vh' => [
                        'max' => 400,
                    ],
                ],
                'size_units' => ['px', 'vh'],
                'selectors' => [
                    '{{WRAPPER}} .elementor-image-gallery' => 'max-height: {{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    'layout' => 'masonry',
                ],
            ]
        );

        $this->addControl(
            'gallery_link',
            [
                'label' => __('Link'),
                'type' => ControlsManager::SELECT,
                'default' => 'file',
                'options' => [
                    'none' => __('None'),
                    'file' => __('Media File'),
                    'custom' => __('Custom URL'),
                ],
            ]
        );

        $this->addControl(
            'open_lightbox',
            [
                'label' => __('Lightbox'),
                'type' => ControlsManager::SELECT,
                'default' => 'default',
                'options' => [
                    'default' => __('Default'),
                    'yes' => __('Yes'),
                    'no' => __('No'),
                ],
                'condition' => [
                    'gallery_link' => 'file',
                ],
            ]
        );

        $this->addControl(
            'gallery_rand',
            [
                'label' => __('Order By'),
                'type' => ControlsManager::SELECT,
                'options' => [
                    '' => __('Default'),
                    'rand' => __('Random'),
                ],
            ]
        );

        $this->addControl(
            'view',
            [
                'label' => __('View'),
                'type' => ControlsManager::HIDDEN,
                'default' => 'traditional',
            ]
        );

        $this->endControlsSection();

        $this->startControlsSection(
            'section_gallery_images',
            [
                'label' => __('Images'),
                'tab' => ControlsManager::TAB_STYLE,
            ]
        );

        $this->addResponsiveControl(
            'image_height',
            [
                'label' => __('Height'),
                'type' => ControlsManager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 800,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} img' => 'object-fit: cover; height: {{SIZE}}{{UNIT}}',
                ],
                'condition' => [
                    'layout' => 'grid',
                ],
            ]
        );

        $this->addResponsiveControl(
            'image_spacing_custom',
            [
                'label' => __('Spacing'),
                'type' => ControlsManager::SLIDER,
                'range' => [
                    'px' => [
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'size' => 0,
                ],
                'selectors' => [
                    '{{WRAPPER}} figure' => 'padding: {{SIZE}}{{UNIT}}',
                    '{{WRAPPER}} .elementor-image-gallery' => 'margin: -{{SIZE}}{{UNIT}}',
                ],
            ]
        );

        $this->addGroupControl(
            GroupControlBorder::getType(),
            [
                'name' => 'image_border',
                'selector' => '{{WRAPPER}} figure img',
                'separator' => 'before',
            ]
        );

        $this->addControl(
            'image_border_radius',
            [
                'label' => __('Border Radius'),
                'type' => ControlsManager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} figure img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->endControlsSection();

        $this->startControlsSection(
            'section_caption',
            [
                'label' => __('Caption'),
                'tab' => ControlsManager::TAB_STYLE,
            ]
        );

        $this->addControl(
            'gallery_display_caption',
            [
                'label' => __('Display'),
                'type' => ControlsManager::SELECT,
                'options' => [
                    '' => __('Show'),
                    'none' => __('Hide'),
                ],
                'selectors' => [
                    '{{WRAPPER}} figcaption' => 'display: {{VALUE}};',
                ],
            ]
        );

        $this->addControl(
            'align',
            [
                'label' => __('Alignment'),
                'type' => ControlsManager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => __('Justified'),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} figcaption' => 'text-align: {{VALUE}};',
                ],
                'condition' => [
                    'gallery_display_caption' => '',
                ],
            ]
        );

        $this->addControl(
            'text_color',
            [
                'label' => __('Text Color'),
                'type' => ControlsManager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} figcaption' => 'color: {{VALUE}};',
                ],
                'condition' => [
                    'gallery_display_caption' => '',
                ],
            ]
        );

        $this->addGroupControl(
            GroupControlTypography::getType(),
            [
                'name' => 'typography',
                'scheme' => SchemeTypography::TYPOGRAPHY_4,
                'selector' => '{{WRAPPER}} figcaption',
                'condition' => [
                    'gallery_display_caption' => '',
                ],
            ]
        );

        $this->addGroupControl(
            GroupControlTextShadow::getType(),
            [
                'name' => 'caption_shadow',
                'selector' => '{{WRAPPER}} figcaption',
                'condition' => [
                    'gallery_display_caption' => '',
                ],
            ]
        );

        $this->addResponsiveControl(
            'caption_space',
            [
                'label' => __('Spacing'),
                'type' => ControlsManager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} figcaption' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'gallery_display_caption' => '',
                ],
            ]
        );

        $this->endControlsSection();
    }

    /**
     * Render image gallery widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     */
    protected function render()
    {
        $settings = $this->getSettingsForDisplay();

        if (!$settings['gallery']) {
            return;
        }
        $gallery = $settings['gallery'];

        empty($settings['gallery_rand']) or shuffle($gallery);

        $this->addRenderAttribute('link', [
            'data-elementor-open-lightbox' => $settings['open_lightbox'],
            'data-elementor-lightbox-slideshow' => $this->getId(),
        ]);

        if (Plugin::$instance->editor->isEditMode()) {
            $this->addRenderAttribute('link', 'class', 'elementor-clickable');
        } ?>
        <div class="elementor-image-gallery">
        <?php foreach ($gallery as &$item) { ?>
            <?php if (!empty($item['image']['url'])) { ?>
                <figure class="ce-gallery-item">
                    <div class="ce-gallery-icon">
                    <?php if ($link = $this->getLinkUrl($item, $settings['gallery_link'])) { ?>
                        <a <?php $this->printRenderAttributeString('link'); ?> href="<?php echo esc_attr($link['url']); ?>"
                            <?php echo !empty($link['is_external']) ? 'target="_blank"' : ''; ?>
                            <?php echo !empty($link['nofollow']) ? 'rel="nofollow"' : ''; ?>>
                    <?php } ?>
                        <?php echo GroupControlImageSize::getAttachmentImageHtml($item); ?>
                    <?php if ($link) { ?>
                        </a>
                    <?php } ?>
                    </div>
                    <figcaption class="ce-gallery-caption"><?php echo $item['caption']; ?></figcaption>
                </figure>
            <?php } ?>
        <?php } ?>
        </div>
        <?php
    }

    /**
     * Render image gallery widget output in the editor.
     *
     * Written as a Backbone JavaScript template and used to generate the live preview.
     *
     * @since 2.9.0
     */
    protected function contentTemplate()
    {
        ?>
        <#
        function getLinkUrl(attachment, linkTo) {
            if ('none' === linkTo) {
                return false;
            }
            if ('custom' === linkTo) {
                if (!attachment || !attachment.link || !attachment.link.url) {
                    return false;
                }
                return attachment.link;
            }
            return !attachment || !attachment.image || !attachment.image.url ? false : {
                url: elementor.imagesManager.getImageUrl(attachment.image),
            };
        }
        var gallery = !settings.gallery_rand ? settings.gallery : settings.gallery.sort(function shuffle() {
                return Math.random() > 0.5 ? 1 : -1;
            }),
            link;

        view.addRenderAttribute('link', {
            'class': 'elementor-clickable',
            'data-elementor-open-lightbox': settings.open_lightbox,
            'data-elementor-lightbox-slideshow': view.getID(),
        });
        #>
        <div class="elementor-image-gallery">
        <# gallery.length && gallery.forEach(function (item) { #>
            <# if (item.image && item.image.url) { #>
                <figure class="ce-gallery-item">
                    <div class="ce-gallery-icon">
                    <# if (link = getLinkUrl(item, settings.gallery_link)) { #>
                        <a {{{ view.getRenderAttributeString('link') }}} href="{{ link.url }}">
                    <# } #>
                        <img src="{{ elementor.imagesManager.getImageUrl(item.image) }}" loading="lazy">
                    <# if (link) { #>
                        </a>
                    <# } #>
                    </div>
                    <figcaption class="ce-gallery-caption">{{{ item.caption }}}</figcaption>
                </figure>
            <# } #>
        <# }); #>
        </div>
        <?php
    }

    /**
     * Retrieve image carousel link URL.
     *
     * @since 1.0.0
     *
     * @param array $attachment
     * @param object $instance
     *
     * @return array|string|false An array/string containing the attachment URL, or false if no link
     */
    private function getLinkUrl($attachment, $link_to)
    {
        if ('none' === $link_to) {
            return false;
        }

        if ('custom' === $link_to) {
            if (empty($attachment['link']['url'])) {
                return false;
            }

            return $attachment['link'];
        }

        return empty($attachment['image']['url']) ? false : [
            'url' => Helper::getMediaLink($attachment['image']['url']),
        ];
    }
}
