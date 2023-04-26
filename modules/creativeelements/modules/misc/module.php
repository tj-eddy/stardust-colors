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

use CE\CoreXBaseXModule as BaseModule;

class ModulesXMiscXModule extends BaseModule
{
    public function getName()
    {
        return 'misc';
    }

    public function init()
    {
        $context = \Context::getContext();
        $filter = 'filter';
        $context->smarty->registerPlugin('modifier', 'ce' . $filter, [$this, 'modifierFilter']);

        if ($uid_preview = \CreativeElements::getPreviewUId()) {
            if (UId::TEMPLATE === $uid_preview->id_type && 'kit' === (new \CETemplate($uid_preview->id))->type) {
                \Configuration::set('elementor_active_kit', $uid_preview->id);
            }
            $render = \Tools::getValue('render');

            if ('widget' === $render && \Tools::getIsset('actions')) {
                \CreativeElements::skipOverrideLayoutTemplate();

                $response = Plugin::$instance->widgets_manager->ajaxRenderWidget();
                empty($response) or http_response_code(200);
                exit(json_encode($response));
            } elseif ('tags' === $render && \Tools::getIsset('data')) {
                $this->tplOverride = '';

                $response = Plugin::$instance->dynamic_tags->ajaxRenderTags();
                empty($response) or http_response_code(200);
                exit(json_encode($response));
            }

            header_register_callback(function () {
                header_remove('Content-Security-Policy');
                header_remove('X-Content-Type-Options');
                header_remove('X-Frame-Options');
                header_remove('X-Xss-Protection');
            });
        }

        \CEAssetManager::instance();
    }

    public function modifierFilter($str)
    {
        echo $str;
    }

    public function enqueuePreviewScripts()
    {
        // Redirect to preview page when edited hook is missing
        $uid = \Tools::getValue('preview_id');

        if ($uid && UId::CONTENT === UId::parse($uid)->id_type) {
            $tab = 'AdminCEContent';
            $id_employee = (int) \Tools::getValue('id_employee');

            wp_register_script(
                'editor-preview',
                _CE_ASSETS_URL_ . 'js/editor-preview.js',
                [],
                _CE_VERSION_,
                true
            );

            wp_localize_script(
                'editor-preview',
                'cePreview',
                \Context::getContext()->link->getModuleLink('creativeelements', 'preview', [
                    'id_employee' => $id_employee,
                    'cetoken' => \Tools::getAdminToken($tab . (int) \Tab::getIdFromClassName($tab) . $id_employee),
                    'preview_id' => $uid,
                    'ctx' => (int) \Tools::getValue('ctx'),
                ], null, null, null, true)
            );

            wp_enqueue_script('editor-preview');
        }
    }

    public function enqueueFrontendScripts()
    {
        // Add Quick Edit button on frontend when employee has active session
        if ($editor = $this->getEditorLink()) {
            wp_register_script('frontend-edit', _CE_ASSETS_URL_ . 'js/frontend-edit.js', [], _CE_VERSION_);
            wp_localize_script('frontend-edit', 'ceFrontendEdit', [
                'editor_url' => $editor,
                'edit_title' => __('Edit with Creative Elements'),
            ]);
            wp_enqueue_script('frontend-edit');
            wp_enqueue_style('frontend-edit', _CE_ASSETS_URL_ . 'css/frontend-edit.css', [], _CE_VERSION_);
        }
    }

    private function getEditorLink()
    {
        static $link;

        if (null === $link) {
            $link = '';

            if (\Configuration::get('elementor_frontend_edit') &&
                ($id_employee = get_current_user_id()) &&
                ($dir = glob(_PS_ROOT_DIR_ . '/*/filemanager', GLOB_ONLYDIR))
            ) {
                $tab = 'AdminCEEditor';
                $link = __PS_BASE_URI__ . basename(dirname($dir[0])) . '/index.php?' . http_build_query([
                    'controller' => $tab,
                    'token' => \Tools::getAdminToken($tab . (int) \Tab::getIdFromClassName($tab) . $id_employee),
                ]);
            }
        }

        return $link;
    }

    public function preloadFonts()
    {
        $lib = _MODULE_DIR_ . 'creativeelements/views/lib'; ?>
        <link rel="preload" href="<?php echo esc_attr("$lib/ceicons/fonts/ceicons.woff2?fj664s"); ?>" as="font" type="font/woff2" crossorigin>
        <?php
    }

    public function __construct()
    {
        add_action('elementor/preview/enqueue_scripts', [$this, 'enqueuePreviewScripts']);
        add_action('elementor/frontend/after_enqueue_scripts', [$this, 'enqueueFrontendScripts']);

        if (!is_admin()) {
            add_action('template_redirect', [$this, 'init'], 0);
            add_action('wp_head', [$this, 'preloadFonts']);
        }

        add_action('elementor/frontend/section/before_render', function ($section) {
            array_unshift(Helper::$section_stack, $section);
        });
        add_action('elementor/frontend/section/after_render', function ($section) {
            array_shift(Helper::$section_stack);
        });
        add_filter('elementor/frontend/column/should_render', function ($should_render, $column) {
            isset(Helper::$section_stack[0]->render_tabs) or Helper::$section_stack[0]->render_tabs = [];

            return Helper::$section_stack[0]->render_tabs[] = $should_render;
        }, 999999);
    }
}
