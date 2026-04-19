<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

use Empathy\ELib\Storage\BrandItem;
use Empathy\ELib\Storage\CategoryItem;
use Empathy\ELib\Storage\PromoItem;
use Empathy\MVC\Model;

define('REQUESTS_PER_PAGE', 12);

class PromoCategoryController extends AdminController
{
    public function assertID(): void
    {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $_GET['id'] = 0;
        }
    }

    public function buildNav(): void
    {

        //$this->assertID();
        if (!isset($_GET['collapsed']) || !is_numeric($_GET['collapsed'])) {
            $_GET['collapsed'] = 0;
        }

        $c = Model::load(CategoryItem::class);
        $c->load((int) $_GET['id']);

        $ct = new PromosTree($c, $_GET['collapsed']);


        $this->assign('category', $c);
        $this->assign('category_has_children', $c->hasChildren());

        $this->assign('nav', $ct->getMarkup());

        $b = Model::load(BrandItem::class);
        $this->assign('brands', $b->getBrands());
    }

    #[\Override]
    public function default_event(): void
    {

        $this->setTemplate('elib://admin/promo_category.tpl');
        $ui_array = ['order_by', 'page', 'id'];
        $this->loadUIVars('ui_catalogue', $ui_array);
        if (!isset($_GET['page']) || $_GET['page'] === '') {
            $_GET['page'] = 1;
        }
        if (!isset($_GET['id']) || $_GET['id'] === '') {
            $_GET['id'] = 0;
        }
        if (!isset($_GET['order_by']) || $_GET['order_by'] === '') {
            $_GET['order_by'] = 'id';
        }

        $this->assign('order_by', $_GET['order_by']);
        $this->assign('page', $_GET['page']);
        $this->assign('category_id', $_GET['id']);



        $this->buildNav();

        $sql = '';
        $sql .= 'WHERE category_id = ?';

        $sql .= 'ORDER BY ?';

        $p = Model::load(PromoItem::class);

        $p_nav = $p->getPaginatePages($sql, $_GET['page'], REQUESTS_PER_PAGE, [$_GET['id'], $_GET['order_by']]);
        $this->assign('p_nav', $p_nav);
        $promo = $p->getAllCustomPaginate($sql, $_GET['page'], REQUESTS_PER_PAGE, [$_GET['id'], $_GET['order_by']]);

        $c = Model::load(CategoryItem::class);
        $c->id = $_GET['id'];
        $c->loadIndexed($c->category_id);

        /*
          foreach ($product as $index => $item) {
          // get stock count
          $stock = new StockItem($this);
          $s_count = $stock->getAllCustom(StockItem::$table, 'WHERE product_variant_id = '.$product[$index]['id']);
          $product[$index]['stock'] = sizeof($s_count);

          //$category_id = $product[$index]['category_id'];
          //$product[$index]['category'] = $category[$category_id];
          }
        */

        $this->assign('promos', $promo);
    }

    public function add(): void
    {
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $p = Model::load(PromoItem::class);
            $p->category_id = (int) $_GET['id'];
            $p->name = 'New Promo';
            $p->hidden = 'DEFAULT';
            $id = $p->insert();
        }
        $this->redirect('admin/promo_category/'.$_GET['id']);
    }

}
