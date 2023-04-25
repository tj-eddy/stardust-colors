<?php
/*
* 2007-2014 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2014 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Product\Search\Pagination;

include_once(dirname(__FILE__).'/../../classes/StProductCommentClass.php');

class StProductCommentsDetailModuleFrontController extends ModuleFrontController
{
    private $_prefix_st = 'ST_PROD_C_';

	public function __construct()
	{
		parent::__construct();
		$this->context = Context::getContext();
	}

	public function initContent()
	{
		parent::initContent();

        if ($id_st_product_comment = Tools::getValue('id_st_product_comment')) {
            $this->assginOne($id_st_product_comment);
        } 
        else {
            $this->errors[] = $this->trans('Invalid parameters', array(), 'Shop.Theme.Panda');
        }
		$this->setTemplate('module:stproductcomments/views/templates/front/detail.tpl');
	}
    private function assginOne($id_st_product_comment = 0)
    {
        $comment = new StProductCommentClass($id_st_product_comment);
        if (!$comment->id || !$comment->id_product) {
            Tools::redirect('index.php?controller=404');
        }
        
        $pcomments = array();
        $comment_temp = StProductCommentClass::getListComments(1, 1, null, null, false, null, null, false, 0, $id_st_product_comment);
        
        if(!$comment_temp && !Configuration::get($this->_prefix_st.'MODERATE')){
            Tools::redirect('index.php?controller=404');
        }
        
        $order_detail  = new OrderDetail($comment->id_order_detail);
        $pcomments['comment'] = isset($comment_temp[0]) ? $comment_temp[0] : array();
        $pcomments['comment']['validate'] = isset($comment_temp[0]) && count($comment_temp[0]);
        $pcomments['g_rich_snippets'] = Configuration::get($this->_prefix_st.'GOOGLE_RICH_SNIPPETS') && Dispatcher::getInstance()->getController() == 'product';
        $pcomments['helpful'] = Configuration::get($this->_prefix_st.'HELPFUL');
        $pc_product = array();
        $assembler = new ProductAssembler($this->context);
        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        $presenter = new ProductListingPresenter(
            new ImageRetriever(
                $this->context->link
            ),
            $this->context->link,
            new PriceFormatter(),
            new ProductColorsRetriever(),
            $this->context->getTranslator()
        );
        $pc_product = $presenter->present(
            $presentationSettings,
            $assembler->assembleProduct(array(
                'id_product' => $comment->id_product,
                'id_product_attribute' => $order_detail->product_attribute_id,
            )),
            $this->context->language
        );
        if(!$pc_product)
            Tools::redirect('index.php?controller=404');
        
        $pcomments['comment']['product_name_full'] = $pcomments['comment']['product_name'] = $pc_product['name'];
        $pcomments['comment']['product_attr_name'] = '';
        $pcomments['comment']['product_link'] = $pc_product['url'];
        $pcomments['comment']['id_product'] = $comment->id_product;
        $pcomments['comment']['timeago'] = Configuration::get($this->_prefix_st.'DISPLAY_DATE') || !isset($pcomments['comment']['date_add']) ? false : StProductCommentClass::Timeago($pcomments['comment']['date_add']);
        $pcomments['comment']['in_detail'] = true;
        $array = explode(' ', $pcomments['comment']['customer_name']);
        $custom_name = '';
        $custom_name_ds = Configuration::get($this->_prefix_st.'CUSTOMER_NAME');
        if (!$custom_name_ds) {
            foreach($array AS $i => $v) {
                if (!$i) {
                    $v = substr($v, 0, 2).str_repeat('*', strlen($v)-2);    
                } elseif ($i == count($array)-1) {
                    $v = str_repeat('*', strlen($v)-2).substr($v, -2);
                } else {
                    $v = str_repeat('*', strlen($v));
                }
                $custom_name .= $v. ' ';
            }
            $pcomments['comment']['customer_name'] = trim($custom_name);
        } elseif ($custom_name_ds == 2) {
            $custom_name = array_shift($array);
            $pcomments['comment']['customer_name'] = trim($custom_name);
        }

        $averages = StProductCommentClass::getAveragesByProduct($comment->id_product, $this->context->language->id);
        
        $averageTotal = 0;
        foreach ($averages as $average) {
            $averageTotal += (float)($average);
        }
        $averageTotal = count($averages) ? round($averageTotal / count($averages), 1) : 0;
        $criterions = StProductCommentCriterionClass::getCriterions($this->context->language->id, true);
        $criterions_arr = array();
        if($criterions){
            foreach ($criterions as $v){
                $criterions_arr[$v['id_st_product_comment_criterion']] = $v['name'];
            }
        }
        $moderate = Configuration::get($this->_prefix_st.'MODERATE');
        $resultsPerPage = (int)Tools::getValue('resultsPerPage');
        $page = max((int)Tools::getValue('page'), 1);
        $resultsPerPage = $resultsPerPage ? $resultsPerPage : 10;
        $replies_total = StProductCommentClass::getReplies($comment->id, $moderate, null, null, true);
        $replies = StProductCommentClass::getReplies($comment->id, $moderate, $page, $resultsPerPage);
        foreach($replies as &$reply) {
            $array = explode(' ', $reply['customer_name']);
            $custom_name = '';
            $custom_name_ds = Configuration::get($this->_prefix_st.'CUSTOMER_NAME');
            if (!$custom_name_ds) {
                foreach($array AS $i => $v) {
                    if (!$i) {
                        $v = substr($v, 0, 2).str_repeat('*', strlen($v)-2);    
                    } elseif ($i == count($array)-1) {
                        $v = str_repeat('*', strlen($v)-2).substr($v, -2);
                    } else {
                        $v = str_repeat('*', strlen($v));
                    }
                    $custom_name .= $v. ' ';
                }
                $reply['customer_name'] = trim($custom_name);
            } elseif ($custom_name_ds == 2) {
                $custom_name = array_shift($array);
                $reply['customer_name'] = trim($custom_name);
            }
        }
        
        $customerName = '';
        $logged = $this->context->customer->isLogged();
        if ($logged && ($this->context->customer->firstname || $this->context->customer->lastname)) {
            $customerName = $this->getTranslator()->trans(
                '%firstname% %lastname%',
                array(
                    '%firstname%' => $this->context->customer->firstname,
                    '%lastname%' => $this->context->customer->lastname,
                ),
                'Modules.Stproductcomments.Admin'
            );
        }

        $this->context->smarty->assign(array(
            // 'logged' => $this->context->customer->isLogged(true),
            // 'order' => $order_array,
            'pcomments' => $pcomments,
            'product' => $pc_product,
            'nbComments' => (int) StProductCommentClass::getCommentNumber($comment->id_product),
            'id_st_product_comment' => $id_st_product_comment,
            'moderate' => $moderate,
            'replies' => $replies,
            'pagination' => $this->getTemplateVarPagination($replies_total, $page, $resultsPerPage),
            'criterions' => $criterions_arr,
            'averages' => $averages,
            'averageTotal' => $averageTotal,
            'customerName' => $customerName,
        ));
    }
    protected function getTemplateVarPagination($resultCount = 0, $page=1, $resultsPerPage=20)
    {
        $totalItems = (int)$resultCount;
        $page = (int)$page ? (int)$page : 1;
        $resultsPerPage = (int)$resultsPerPage ? (int)$resultsPerPage : 20;
        $pagination = new Pagination();
        $pagination
            ->setPage($page)
            ->setPagesCount(
                (int)ceil((int)$totalItems / $resultsPerPage)
            )
        ;
        $itemsShownFrom = ($resultsPerPage * ($page - 1)) + 1;
        $itemsShownTo = $resultsPerPage * $page;

        return array(
            'total_items' => $totalItems,
            'items_shown_from' => $itemsShownFrom,
            'items_shown_to' => ($itemsShownTo <= $totalItems) ? $itemsShownTo : $totalItems,
            'pages' => array_map(function ($link) {
                $link['url'] = $this->updateQueryString(array(
                    'page' => $link['page'],
                ));

                return $link;
            }, $pagination->buildLinks()),
        );
    }
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = array(
            'title' => $this->trans('Reviews', array(), 'Shop.Theme.Panda'),
            'url' => $this->context->link->getModuleLink('stproductcomments', 'list'),
        );
        return $breadcrumb;
    }
    public function getCanonicalURL()
    {
        return $this->context->link->getModuleLink('stproductcomments', 'detail');
    }
}
