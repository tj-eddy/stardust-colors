<?php
/**
 * Creative Elements - live Theme & Page Builder
 *
 * @author    WebshopWorks
 * @copyright 2019-2023 WebshopWorks.com
 * @license   One domain support license
 */
defined('_PS_VERSION_') or exit;

use PrestaShop\PrestaShop\Adapter\Configuration as ConfigurationAdapter;
use Symfony\Component\Filesystem\Filesystem;

if (version_compare(_PS_VERSION_, '1.7.7', '<')) {
    require_once _CE_PATH_ . 'classes/assets/CEStylesheetManager5.php';
    require_once _CE_PATH_ . 'classes/assets/CEJavascriptManager5.php';
} else {
    require_once _CE_PATH_ . 'classes/assets/CEStylesheetManager.php';
    require_once _CE_PATH_ . 'classes/assets/CEJavascriptManager.php';
}
require_once _CE_PATH_ . 'classes/assets/CECccReducer.php';

class CEAssetManager
{
    protected $controller;

    protected $stylesheetManager;

    protected $javascriptManager;

    protected $template;

    public static function instance()
    {
        static $instance;

        if (null === $instance) {
            $instance = new self();

            CE\add_action('wp_head', 'wp_enqueue_scripts', 1);
            CE\do_action('wp_register_scripts');
        }

        return $instance;
    }

    protected function __construct()
    {
        $ctx = Context::getContext();
        $config = new ConfigurationAdapter();
        $this->controller = $ctx->controller;

        $managers = [
            'stylesheetManager' => 'CEStylesheetManager',
            'javascriptManager' => 'CEJavascriptManager',
        ];
        foreach ($managers as $prop => $class) {
            $managerRef = new ReflectionProperty($this->controller, $prop);
            $managerRef->setAccessible(true);
            $manager = $managerRef->getValue($this->controller);

            $listRef = new ReflectionProperty($manager, 'list');
            $listRef->setAccessible(true);

            $this->$prop = new $class([
                _PS_THEME_URI_,
                _PS_PARENT_THEME_URI_,
                __PS_BASE_URI__,
            ], $config, $listRef->getValue($manager));

            $managerRef->setValue($this->controller, $this->$prop);
        }

        $reducerRef = new ReflectionProperty($this->controller, 'cccReducer');
        $reducerRef->setAccessible(true);
        $this->cccReducer = new CECccReducer(
            _PS_THEME_DIR_ . 'assets/cache/',
            $config,
            new Filesystem()
        );
        $reducerRef->setValue($this->controller, $this->cccReducer);

        $this->template = &Closure::bind(function &() {
            return $this->template;
        }, $this->controller, $this->controller)->__invoke();

        $ctx->smarty->registerFilter('output', [$this, 'outputFilter']);
    }

    public function registerStylesheet($id, $path, array $params = [])
    {
        $params = array_merge([
            'media' => AbstractAssetManager::DEFAULT_MEDIA,
            'priority' => AbstractAssetManager::DEFAULT_PRIORITY,
            'inline' => false,
            'server' => 'local',
        ], $params);

        if (_MEDIA_SERVER_1_ && 'remote' !== $params['server'] && !Configuration::get('PS_CSS_THEME_CACHE')) {
            $params['server'] = 'remote';
            $path = '//' . Tools::getMediaServer($path) . ($this->stylesheetManager->getFullPath($path) ?: $path);
        }
        $this->stylesheetManager->register($id, $path, $params['media'], $params['priority'], $params['inline'], $params['server']);
    }

    public function registerJavascript($id, $path, array $params = [])
    {
        $params = array_merge([
            'position' => AbstractAssetManager::DEFAULT_JS_POSITION,
            'priority' => AbstractAssetManager::DEFAULT_PRIORITY,
            'inline' => false,
            'attributes' => null,
            'server' => 'local',
        ], $params);

        if (_MEDIA_SERVER_1_ && 'remote' !== $params['server'] && !Configuration::get('PS_JS_THEME_CACHE')) {
            $params['server'] = 'remote';
            $path = '//' . Tools::getMediaServer($path) . ($this->javascriptManager->getFullPath($path) ?: $path);
        }
        $this->javascriptManager->register($id, $path, $params['position'], $params['priority'], $params['inline'], $params['attributes'], $params['server']);
    }

    public function outputFilter($out, $tpl)
    {
        if ($this->template === $tpl->template_resource || 'errors/maintenance.tpl' === $tpl->template_resource) {
            $assets = $this->fetchAssets();

            if (false !== $pos = strpos($out, '<!--CE-JS-->')) {
                $out = substr_replace($out, $assets->head, $pos, 0);
            }
            if (false !== $pos = strrpos($out, '<!--CE-JS-->')) {
                $out = substr_replace($out, $assets->bottom, $pos, 0);
            }
        }

        return $out;
    }

    public function fetchAssets()
    {
        $smarty = Context::getContext()->smarty;
        $assets = new stdClass();

        ob_start();
        CE\do_action('wp_head');
        $assets->head = ob_get_clean();

        ob_start();
        CE\do_action('wp_footer');
        $assets->bottom = ob_get_clean();

        $styles = $this->stylesheetManager->listAll();

        foreach ($styles['external'] as $id => &$style) {
            if (isset(CE\Helper::$inline_styles[$id])) {
                $styles['inline'][$id] = [
                    'content' => implode("\n", CE\Helper::$inline_styles[$id]),
                ];
            }
        }

        $scripts = $this->javascriptManager->listAll();
        $js_defs = Media::getJsDef();

        if (!empty($smarty->tpl_vars['js_custom_vars'])) {
            foreach ($smarty->tpl_vars['js_custom_vars']->value as $key => &$val) {
                unset($js_defs[$key]);
            }
        }

        Configuration::get('PS_CSS_THEME_CACHE') && $styles = $this->cccReducer->reduceCss($styles);
        Configuration::get('PS_JS_THEME_CACHE') && $scripts = $this->cccReducer->reduceJs($scripts);

        $smarty->assign([
            'stylesheets' => &$styles,
            'javascript' => &$scripts['head'],
            'js_custom_vars' => &$js_defs,
        ]);
        $assets->head = $smarty->fetch(_CE_TEMPLATES_ . 'front/theme/_partials/assets.tpl') . $assets->head;

        $smarty->assign([
            'stylesheets' => [],
            'javascript' => &$scripts['bottom'],
            'js_custom_vars' => [],
        ]);
        $assets->bottom = $smarty->fetch(_CE_TEMPLATES_ . 'front/theme/_partials/assets.tpl') . $assets->bottom;

        return $assets;
    }
}
