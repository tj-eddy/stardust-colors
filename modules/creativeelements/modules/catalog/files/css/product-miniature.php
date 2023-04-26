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

use CE\CoreXFilesXCSSXBase as Base;
use CE\CoreXFilesXCSSXPost as Post;

class ModulesXCatalogXFilesXCSSXProductMiniature extends Post
{
    private $forceInline;

    public function __construct($post_id, $forceInline = false)
    {
        $this->forceInline = $forceInline;

        parent::__construct($post_id);
    }

    public function enqueue()
    {
        Base::enqueue();

        if ($this->forceInline) {
            $this->printCss();
        }
    }

    public function getMeta($property = null)
    {
        if (!$this->forceInline) {
            return parent::getMeta($property);
        }

        // Parse CSS first, to get the fonts list.
        $css = $this->getContent();

        $meta = [
            'status' => self::CSS_STATUS_INLINE,
            'fonts' => $this->getFonts(),
            'css' => $css,
        ];

        if ($property) {
            return isset($meta[$property]) ? $meta[$property] : null;
        }

        return $meta;
    }

    protected function parseContent()
    {
        if (!class_exists('CE\WidgetProductMiniatureName')) {
            // Init default widgets
            Plugin::$instance->widgets_manager->getWidgetTypes('heading');
            // Init catalog widgets
            $catalog = Plugin::$instance->modules_manager->getModules('catalog');

            $catalog->initWidgets(Plugin::$instance->widgets_manager, 'miniature');
        }

        return parent::parseContent();
    }
}
