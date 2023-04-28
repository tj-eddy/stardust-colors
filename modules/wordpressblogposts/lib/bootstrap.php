<?php
$module_folder = 'wordpressblogposts';

/* types */
include_once(_PS_MODULE_DIR_.'/'.$module_folder.'/lib/types/module.php');

/* library classes */
include_once(_PS_MODULE_DIR_.'/'.$module_folder.'/lib/classes/WBPControllerCore.php');
include_once(_PS_MODULE_DIR_.'/'.$module_folder.'/lib/classes/WBPInstallCore.php');

/* models */
include_once(_PS_MODULE_DIR_.'/'.$module_folder.'/models/WBPModel.php');

/* helpers */
include_once(_PS_MODULE_DIR_.'/'.$module_folder.'/helpers/WBPPostsHelper.php');

/* controllers */
include_once(_PS_MODULE_DIR_.'/'.$module_folder.'/controllers/front/WBPCronController.php');
include_once(_PS_MODULE_DIR_.'/'.$module_folder.'/controllers/admin/WBPConfigController.php');
include_once(_PS_MODULE_DIR_.'/'.$module_folder.'/controllers/admin/WBPConfigHookController.php');
include_once(_PS_MODULE_DIR_.'/'.$module_folder.'/controllers/front/WBPFrontController.php');

