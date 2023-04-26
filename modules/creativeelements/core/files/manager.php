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

use CE\CoreXCommonXModulesXAjaxXModule as Ajax;
use CE\CoreXFilesXAssetsXSvgXSvgHandler as SvgHandler;
use CE\CoreXFilesXBase as Base;
use CE\CoreXFilesXCSSXGlobalCSS as GlobalCSS;
use CE\CoreXFilesXCSSXPost as PostCSS;
use CE\CoreXResponsiveXFilesXFrontend as Frontend;

/**
 * Elementor files manager.
 *
 * Elementor files manager handler class is responsible for creating files.
 *
 * @since 1.2.0
 */
class CoreXFilesXManager
{
    private $files = [];

    /**
     * Files manager constructor.
     *
     * Initializing the Elementor files manager.
     *
     * @since 1.2.0
     */
    public function __construct()
    {
        // $this->registerActions();

        new SvgHandler();
        // new JsonHandler();
    }

    public function get($class, $args)
    {
        $id = $class . '-' . json_encode($args);

        if (!isset($this->files[$id])) {
            // Create an instance from dynamic args length.
            $reflection_class = new \ReflectionClass($class);
            $this->files[$id] = $reflection_class->newInstanceArgs($args);
        }

        return $this->files[$id];
    }

    // public function onDeletePost($post_id)

    // public function onExportPostMeta($skip, $meta_key)

    /**
     * Clear cache.
     *
     * Delete all meta containing files data. And delete the actual
     * files from the upload directory.
     *
     * @since 1.2.0
     */
    public function clearCache()
    {
        if (\Shop::getContext() == \Shop::CONTEXT_ALL) {
            \Configuration::deleteByName(GlobalCSS::META_KEY);
            \Configuration::deleteByName(Frontend::META_KEY);
        } else {
            \Configuration::deleteFromContext(GlobalCSS::META_KEY);
            \Configuration::deleteFromContext(Frontend::META_KEY);
        }

        $db = \Db::getInstance();
        $table = _DB_PREFIX_ . 'ce_meta';
        $id_shop_list = \Shop::getContextListShopID();
        $id_shop_list[] = 0;

        foreach ($id_shop_list as $id_shop) {
            $id_shop = (int) $id_shop;
            $id = sprintf('%02d', $id_shop);
            $meta_key = $db->escape(PostCSS::META_KEY);

            $db->execute("DELETE FROM $table WHERE id LIKE '%$id' AND name = '$meta_key'");

            // Delete files.
            $path = Base::getBaseUploadsDir() . Base::DEFAULT_FILES_DIR;

            foreach (glob("{$path}*$id.css", GLOB_NOSORT) as $file_path) {
                \Tools::deleteFile($file_path);
            }
            foreach (glob("{$path}$id_shop-*.css", GLOB_NOSORT) as $file_path) {
                \Tools::deleteFile($file_path);
            }
        }

        /*
         * Elementor clear files.
         *
         * Fires after Elementor clears files
         *
         * @since 2.1.0
         */
        do_action('elementor/core/files/clear_cache');
    }

    // public function registerAjaxActions(Ajax $ajax)

    // public function ajaxUnfilteredFilesUpload()

    // private function registerActions()
}
