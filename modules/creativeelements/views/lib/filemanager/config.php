<?php
/**
 * Creative Elements - live Theme & Page Builder
 *
 * @author    WebshopWorks
 * @copyright 2019-2023 WebshopWorks.com
 * @license   One domain support license
 */
defined('_PS_VERSION_') or exit;

const FIELD_IMAGE_ALL = 0xCE;
const FIELD_IMAGE_SVG = 0xCE + 1;

$field_id = (int) Tools::getValue('field_id');

if ($field_id < FIELD_IMAGE_ALL && basename($_SERVER['SCRIPT_NAME']) === 'dialog.php') {
    return;
}

switch ($field_id) {
    case FIELD_IMAGE_SVG:
        $ext = ['svg'];
        $ext_img = ['svg'];

        $mime = ['image/svg'];
        $mime_img = ['image/svg'];
        break;
    default:
        $ext[] = 'webp';
        $ext_img[] = 'webp';

        $mime[] = 'image/webp';
        $mime_img[] = 'image/webp';
        break;
}

if (in_array(Tools::getValue('action'), ['rename_file', 'duplicate_file', 'delete_file'])) {
    // Path fix for actions
    ${'_POST'}['path'] = Tools::substr(${'_POST'}['path_thumb'], Tools::strlen($thumbs_base_path));
}

if (isset($_FILES['file']['name'])) {
    if (!strcasecmp('svg', pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION)) &&
        class_exists('DOMDocument') && class_exists('SimpleXMLElement')
    ) {
        require _PS_MODULE_DIR_ . 'creativeelements/core/files/assets/files-upload-handler.php';
        require _PS_MODULE_DIR_ . 'creativeelements/core/files/assets/svg/svg-handler.php';

        $svg_handler = new CE\CoreXFilesXAssetsXSvgXSvgHandler();
        $svg_handler->sanitizeSvg($_FILES['file']['tmp_name']);
    }

    register_shutdown_function(function () {
        $error = error_get_last();

        if ($error && strpos($error['message'], '.webp is missing or invalid') !== false) {
            // Ignore WEBP thumbnail generation error
            http_response_code(200);
        }
    });
}
