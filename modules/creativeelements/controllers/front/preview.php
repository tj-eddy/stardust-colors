<?php
/**
 * Creative Elements - live Theme & Page Builder
 *
 * @author    WebshopWorks
 * @copyright 2019-2023 WebshopWorks.com
 * @license   One domain support license
 */
defined('_PS_VERSION_') or exit;

class CreativeElementsPreviewModuleFrontController extends ModuleFrontController
{
    protected $uid;

    protected $title;

    public function init()
    {
        if (Tools::getIsset('redirect') && CreativeElements::hasAdminToken('AdminCEEditor')) {
            $cookie = CE\get_post_meta(0, 'cookie', true);
            CE\delete_post_meta(0, 'cookie');

            if (!empty($cookie)) {
                $lifetime = max(1, (int) Configuration::get('PS_COOKIE_LIFETIME_BO')) * 3600 + time();
                $admin = new Cookie('psAdmin', '', $lifetime);

                foreach ($cookie as $key => &$value) {
                    $admin->$key = $value;
                }
                unset($admin->remote_addr);

                $admin->write();
            }
            Tools::redirectAdmin(urldecode(Tools::getValue('redirect')));
        }

        $this->uid = CreativeElements::getPreviewUId(false);

        if (!$this->uid) {
            Tools::redirect('index.php?controller=404');
        }

        parent::init();
    }

    public function initContent()
    {
        $model = $this->uid->getModel();

        if ('CETemplate' !== $model) {
            $this->warning[] = CESmarty::get(_CE_TEMPLATES_ . 'admin/admin.tpl', 'ce_undefined_position');
        }
        $post = CE\get_post($this->uid);

        $this->title = $post->post_title;
        $this->context->smarty->assign($model::${'definition'}['table'], [
            'id' => $post->_obj->id,
            'content' => '',
        ]);

        parent::initContent();

        $this->title = $post->post_title;
        $this->context->smarty->addTemplateDir(_CE_TEMPLATES_);
        $this->context->smarty->assign([
            'HOOK_LEFT_COLUMN' => '',
            'HOOK_RIGHT_COLUMN' => '',
            'breadcrumb' => $this->getBreadcrumb(),
        ]);
        $this->template = 'front/preview.tpl';
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = [
            'links' => [
                ['url' => 'javascript:;', 'title' => 'Creative Elements'],
                ['url' => 'javascript:;', 'title' => CE\__('Preview')],
            ],
        ];
        if (!empty($this->title)) {
            $breadcrumb['links'][] = ['url' => 'javascript:;', 'title' => $this->title];
        }

        return $breadcrumb;
    }

    public function getBreadcrumbPath()
    {
        $breadcrumb = $this->getBreadcrumbLinks();

        return CESmarty::capture(_CE_TEMPLATES_ . 'admin/admin.tpl', 'ce_preview_breadcrumb', [
            'links' => $breadcrumb['links'],
        ]);
    }
}
