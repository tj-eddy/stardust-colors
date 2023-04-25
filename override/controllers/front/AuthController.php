<?php
class AuthController extends AuthControllerCore
{
    /*
    * module: stoverride
    * date: 2023-04-25 15:28:23
    * version: 1.0.0
    */
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = [
            'title' => $this->trans('Account', array(), 'Shop.Theme.Panda'),
            'url' => $this->context->link->getPageLink('authentication'),
        ];
        return $breadcrumb;
    }
}
