<?php
/**
 * Creative Elements - live Theme & Page Builder
 *
 * @author    WebshopWorks
 * @copyright 2019-2023 WebshopWorks.com
 * @license   One domain support license
 */
defined('_PS_VERSION_') or exit;

class CESmarty
{
    protected static $tpls = [];

    protected static function getTemplate($path)
    {
        if (isset(self::$tpls[$path])) {
            return self::$tpls[$path];
        }

        $tpl = Context::getContext()->smarty->createTemplate($path);
        CE\do_action('smarty/before_fetch', $tpl->smarty);
        $tpl->fetch();
        CE\do_action('smarty/after_fetch', $tpl->smarty);

        return self::$tpls[$path] = $tpl;
    }

    public static function call($path, $func, array $params = [], $nocache = true)
    {
        $tpl = self::getTemplate($path);
        CE\do_action('smarty/before_call', $tpl->smarty);
        isset($tpl->smarty->ext->_tplFunction)
            ? $tpl->smarty->ext->_tplFunction->callTemplateFunction($tpl, $func, $params, $nocache)
            : call_user_func("smarty_template_function_$func", $tpl, $params)
        ;
        CE\do_action('smarty/after_call', $tpl->smarty);
    }

    public static function capture($path, $func, array $params = [], $nocache = true)
    {
        ob_start();

        self::call($path, $func, $params, $nocache);

        return ob_get_clean();
    }

    public static function get($path, $buffer)
    {
        $tpl = self::getTemplate($path);

        return isset($tpl->smarty->ext->_capture)
            ? $tpl->smarty->ext->_capture->getBuffer($tpl, $buffer)
            : Smarty::$_smarty_vars['capture'][$buffer]
        ;
    }

    public static function write($path, $buffer)
    {
        $tpl = self::getTemplate($path);

        echo isset($tpl->smarty->ext->_capture)
            ? $tpl->smarty->ext->_capture->getBuffer($tpl, $buffer)
            : Smarty::$_smarty_vars['capture'][$buffer]
        ;
    }

    public static function printf($path, $buffer)
    {
        $args = func_get_args();
        array_shift($args);
        $args[0] = self::get($path, $buffer);

        call_user_func_array(__FUNCTION__, $args);
    }

    public static function sprintf($path, $buffer)
    {
        $args = func_get_args();
        array_shift($args);
        $args[0] = self::get($path, $buffer);

        return call_user_func_array(__FUNCTION__, $args);
    }
}

function smartyInclude(array $params)
{
    if (empty($params['file'])) {
        return;
    }

    $file = $params['file'];

    try {
        if (strrpos($file, '../') !== false || strcasecmp(substr($file, -4), '.tpl') !== 0) {
            throw new Exception();
        }

        if (strpos($file, 'module:') === 0) {
            $file = substr($file, 7);

            if (!file_exists($path = _PS_THEME_DIR_ . "modules/$file") &&
                (!_PARENT_THEME_NAME_ || !file_exists($path = _PS_PARENT_THEME_DIR_ . "modules/$file")) &&
                !file_exists($path = _PS_MODULE_DIR_ . $file)
            ) {
                throw new Exception();
            }
        } elseif (_PARENT_THEME_NAME_ && strpos($file, 'parent:') === 0) {
            $file = substr($file, 7);

            if (!file_exists($path = _PS_PARENT_THEME_DIR_ . "templates/$file")) {
                throw new Exception();
            }
        } elseif (!file_exists($path = _PS_THEME_DIR_ . "templates/$file") &&
            (!_PARENT_THEME_NAME_ || !file_exists($path = _PS_PARENT_THEME_DIR_ . "templates/$file"))
        ) {
            throw new Exception();
        }

        $cache_id = isset($params['cache_id']) ? $params['cache_id'] : null;
        $compile_id = isset($params['compile_id']) ? $params['compile_id'] : null;
        unset($params['file'], $params['cache_id'], $params['compile_id']);

        $out = Context::getContext()->smarty->fetch($path, $cache_id, $compile_id, $params);
    } catch (Exception $ex) {
        $out = $ex->getMessage() ?: "Failed including: '$file'";
    }

    return $out;
}

function ce__($text, $module = 'creativeelements')
{
    return CE\translate($text, $module);
}

function ce_new($class)
{
    $rc = new ReflectionClass($class);
    $args = func_get_args();
    array_shift($args);

    return $rc->newInstanceArgs($args);
}

function ce_enqueue_miniature($uid)
{
    static $enqueued = [];

    if (isset($enqueued[$uid])) {
        return;
    }
    $enqueued[$uid] = true;

    $forceInline = Tools::getValue('render') === 'widget';

    if ($forceInline || !Context::getContext()->controller->ajax) {
        $css_file = new CE\ModulesXCatalogXFilesXCSSXProductMiniature($uid, $forceInline);
        $css_file->enqueue();
    }
}

function array_export($array)
{
    echo preg_replace(['/\barray\s*\(/i', '/,\r?(\n\s*)\)/'], ['[', '$1]'], var_export($array, true));
}

function _q_c_($if, $then, $else)
{
    return $if ? $then : $else;
}
