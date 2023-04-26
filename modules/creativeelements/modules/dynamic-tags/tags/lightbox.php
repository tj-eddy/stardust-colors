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

use CE\CoreXDynamicTagsXDataTag as DataTag;
use CE\ModulesXDynamicTagsXModule as Module;

class ModulesXDynamicTagsXTagsXLightbox extends DataTag
{
    public function getName()
    {
        return 'lightbox';
    }

    public function getTitle()
    {
        return __('Lightbox');
    }

    public function getGroup()
    {
        return Module::ACTION_GROUP;
    }

    public function getCategories()
    {
        return [Module::URL_CATEGORY];
    }

    public function getPanelTemplateSettingKey()
    {
        return 'type';
    }

    public function _registerControls()
    {
        $this->addControl(
            'type',
            [
                'label' => __('Type'),
                'type' => ControlsManager::CHOOSE,
                'label_block' => false,
                'options' => [
                    'video' => [
                        'title' => __('Video'),
                        'icon' => 'eicon-video-camera',
                    ],
                    'image' => [
                        'title' => __('Image'),
                        'icon' => 'eicon-image-bold',
                    ],
                ],
            ]
        );

        $this->addControl(
            'image',
            [
                'label' => __('Image'),
                'type' => ControlsManager::MEDIA,
                'condition' => [
                    'type' => 'image',
                ],
            ]
        );

        $this->addControl(
            'video_url',
            [
                'label' => __('Video Link'),
                'type' => ControlsManager::TEXT,
                'label_block' => true,
                'placeholder' => 'https://www.youtube.com/watch?v=XHOmBV4js_E',
                'condition' => [
                    'type' => 'video',
                ],
            ]
        );
    }

    private function getVideoSettings(&$settings)
    {
        $video_properties = Embed::getVideoProperties($settings['video_url']);
        $video_url = null;
        if (!$video_properties) {
            $video_type = 'hosted';
            $video_url = $settings['video_url'];
        } else {
            $video_type = $video_properties['provider'];
            $video_url = Embed::getEmbedUrl($settings['video_url']);
        }

        if (null === $video_url) {
            return '';
        }

        return [
            'type' => 'video',
            'videoType' => $video_type,
            'url' => $video_url,
        ];
    }

    public function getValue(array $options = [])
    {
        $settings = $this->getSettings();

        if (!$settings['type']) {
            return;
        }

        if ('image' === $settings['type'] && !empty($settings['image']['url'])) {
            $value = [
                'url' => Helper::getMediaLink($settings['image']['url']),
                'type' => 'image',
            ];
        } elseif ('video' === $settings['type'] && $settings['video_url']) {
            $value = $this->getVideoSettings($settings);
        }

        if (isset($value)) {
            return Plugin::$instance->frontend->createActionHash('lightbox', $value);
        }
    }
}
