<?php
/**
 * Creative Elements - live Theme & Page Builder
 *
 * @author    WebshopWorks
 * @copyright 2019-2023 WebshopWorks.com
 * @license   One domain support license
 */
defined('_PS_VERSION_') or exit;

function upgrade_module_2_5_5($module)
{
    $table = _DB_PREFIX_ . 'ce_meta';
    $rows = Db::getInstance()->executeS("
        SELECT `id`, `value` FROM `$table`
        WHERE `name` = '_elementor_data' AND `value` LIKE '%\"widgetType\":\"video\"%'
    ");

    if ($rows) {
        foreach ($rows as &$row) {
            $do_update = false;
            $data = json_decode($row['value'], true);

            if (empty($data)) {
                continue;
            }

            $data = CE\Plugin::instance()->db->iterateData($data, function ($element) use (&$do_update) {
                if (empty($element['widgetType']) || 'video' !== $element['widgetType']) {
                    return $element;
                }

                $replacements = [];

                if (empty($element['settings']['video_type']) || 'youtube' === $element['settings']['video_type']) {
                    $replacements = [
                        'yt_autoplay' => 'autoplay',
                        'yt_controls' => 'controls',
                        'yt_mute' => 'mute',
                        'yt_rel' => 'rel',
                        'link' => 'youtube_url',
                    ];
                } elseif ('vimeo' === $element['settings']['video_type']) {
                    $replacements = [
                        'vimeo_autoplay' => 'autoplay',
                        'vimeo_loop' => 'loop',
                        'vimeo_color' => 'color',
                        'vimeo_link' => 'vimeo_url',
                    ];
                }

                // cleanup old unused settings.
                unset($element['settings']['yt_rel_videos']);

                foreach ($replacements as $old => $new) {
                    if (!empty($element['settings'][$old])) {
                        $element['settings'][$new] = $element['settings'][$old];
                        $do_update = true;
                    }
                }

                return $element;
            });

            if ($do_update) {
                CE\update_post_meta($row['id'], '_elementor_data', $data);
            }
        }
    }

    return true;
}
