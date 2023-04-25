<?php
class CategoryController extends CategoryControllerCore
{
    /*
    * module: stoverride
    * date: 2023-04-25 15:28:24
    * version: 1.0.0
    */
    protected function doProductSearch($template, $params = array(), $locale = null)
    {
        if ($this->ajax) {
            ob_end_clean();
            header('Content-Type: application/json');
            $variables = $this->getAjaxProductSearchVariables();
            if(!Configuration::get('STSN_REMOVE_PRODUCTS_VARIABLE'))
                unset($variables['products']);
            $this->ajaxDie(json_encode($variables));
        } else {
            $variables = $this->getProductSearchVariables();
            $this->context->smarty->assign(array(
                'listing' => $variables,
            ));
            $this->setTemplate($template, $params, $locale);
        }
    }
}
