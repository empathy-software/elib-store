<?php

namespace Empathy\ELib\Store;

use Empathy\ELib\Model;

define('REQUESTS_PER_PAGE', 12);

class CategoryController extends AdminController
{

    public function default_event()
    {
        $ui_array = array('order_by', 'page', 'id', 'brand_id');
        $this->loadUIVars('ui_catalogue', $ui_array);
        if (!isset($_GET['page']) || $_GET['page'] == '') {
            $_GET['page'] = 1;
        }
        if (!isset($_GET['id']) || $_GET['id'] == '') {
            $_GET['id'] = 0;
        }
        if (!isset($_GET['order_by']) || $_GET['order_by'] == '') {
            $_GET['order_by'] = 'id';
        }
        if (!isset($_GET['brand_id']) || $_GET['brand_id'] == '') {
            $_GET['brand_id'] = 0;
        }

        $this->presenter->assign('order_by', $_GET['order_by']);
        $this->presenter->assign('page', $_GET['page']);
        $this->presenter->assign('category_id', $_GET['id']);

        $this->buildNav();

        $p = Model::load('ProductItem');
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $showCat = $_GET['id'];
        } else {
            $showCat = 0;
        }

        $sql = '';
        $sql .= 'WHERE category_id = '.$_GET['id'].' ';

        if ($_GET['brand_id'] > 0) {
            $sql .= ' AND brand_id = '.$_GET['brand_id'].' ';
        }

        $sql .= 'ORDER BY '.$_GET['order_by'];

        $p_nav = $p->getPaginatePages(Model::getTable('ProductItem'), $sql, $_GET['page'], REQUESTS_PER_PAGE);
        $this->presenter->assign('p_nav', $p_nav);
        $this->assign('page', $_GET['page']);
        $product = $p->getAllCustomPaginate(Model::getTable('ProductItem'), $sql, $_GET['page'], REQUESTS_PER_PAGE);

        foreach ($product as &$product_item) {
            if ($product_item['status'] > 0) {
                $product_item['sold_in_store'] = 1;
            } else {
                $product_item['sold_in_store'] = 0;
            }
        }

        $c = Model::load('CategoryItem');
        $c->id = $_GET['id'];
        $category = $c->loadIndexed($c->category_id);

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

        $this->presenter->assign("products", $product);
    }

    public function buildNav()
    {
        $this->setTemplate('elib://admin/category.tpl');
        if (!isset($_GET['collapsed']) || !is_numeric($_GET['collapsed'])) {
            $_GET['collapsed'] = 0;
        }

        $c = Model::load('CategoryItem');
        $c->id = $_GET['id'];
        $c->load();

        $ct = new CategoriesTree($c, $_GET['collapsed']);

        $this->presenter->assign('category', $c);
        $this->presenter->assign('category_has_children', $c->hasChildren());

        $this->presenter->assign('nav', $ct->getMarkup());

        $b = Model::load('BrandItem');
        $this->presenter->assign('brands', $b->getBrands());
    }

    public function assertID()
    {
        if (!isset($_GET['id'] ) || !is_numeric($_GET['id'])) {
            $_GET['id'] = 0;
        }
    }

    public function add_category()
    {
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $c = Model::load('CategoryItem');
            $c->category_id = $_GET['id'];
            $c->name = 'New Category';
            $c->hidden = 'DEFAULT';
            $c->insert(Model::getTable('CategoryItem'), 1, array(), 0);
        }
        $this->redirect('admin/category/'.$_GET['id']);
    }

    public function rename()
    {
        $this->buildNav();
        if (isset($_POST['save'])) {
            $c = Model::load('CategoryItem');
            $c->id = $_POST['id'];
            $c->load();
            $c->name = $_POST['name'];
            $c->validates();
            if ($c->hasValErrors()) {
                $this->presenter->assign('category', $c);
                $this->presenter->assign('errors', $c->getValErrors());
            } else {
                $c->save(Model::getTable('CategoryItem'), array(), 1);
                $this->redirect('admin/category/'.$c->id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/category/'.$_POST['id']);
        } else {
            $c = Model::load('CategoryItem');
            $c->id = $_GET['id'];
            $c->load();
            $this->presenter->assign('category', $c);
        }
    }

    public function delete()
    {
        $this->assertID();
        $c = Model::load('CategoryItem');
        $c->id = $_GET['id'];
        $c->load();
        if ($c->hasCategoriesOrProducts($c->id)) {
            $this->redirect('admin/category/'.$c->id);
        } else {
            $c->delete();
            $this->redirect('admin/category/'.$c->category_id);
        }

    }

    public function active_properties()
    {
        $this->buildNav();

        if (isset($_POST['save'])) {
            $c = Model::load('CategoryProperty');
            $c->emptyByCategory($_GET['id']);
            if (isset($_POST['property']) && sizeof($_POST['property'] > 0)) {
                foreach ($_POST['property'] as $index => $item) {
                    $c->category_id = $_GET['id'];
                    $c->property_id = $index;
                    $c->insert(Model::getTable('CategoryProperty'), 1, array(), 0);
                }
            }
            $this->redirect('admin/category/' . $_GET['id']);
        } elseif (isset($_POST['cancel'])) {
            $this->redirecT('admin/category/' . $_GET['id']);
        } else {
            $p = Model::load('Property');
            $properties = $p->getAllWithOptions(array());
            $this->presenter->assign('properties', $properties);

            $c = Model::load('CategoryItem');
            $ancestors = $c->getAncestorIds($_GET['id'], array());

            $cp = Model::load('CategoryProperty');

            $inherited = array();
            if (sizeof($ancestors) > 0) {
                $inherited = $cp->getPropertiesByCategory($ancestors);
            }

            $active = $cp->getPropertiesByCategory(array($_GET['id']));
            //array_push($inherited, 2); // always use colour
            $this->presenter->assign('active_properties', $active);
            $this->presenter->assign('inherited_properties', $inherited);
        }
    }

}
