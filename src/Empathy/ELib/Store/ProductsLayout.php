<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

use Empathy\ELib\Storage\BrandItem;
use Empathy\ELib\Storage\CategoryItem;
use Empathy\ELib\Storage\ProductItem;
use Empathy\ELib\Storage\ProductItemStatus;
use Empathy\ELib\Storage\ProductVariant;
use Empathy\MVC\Model;

define('BUTTONS_PER_PAGE', 12);

class ProductsLayout
{
    private mixed $buttons = [];
    private mixed $breadcrumb = [];
    private mixed $redirect = '';
    private mixed $p_nav = [];

    public function __construct(private readonly mixed $category, private readonly mixed $product, private readonly mixed $variant, private readonly mixed $option, private readonly mixed $controller)
    {
        $this->buildBC();

        if ($this->option->id !== 0) {
            $this->buildByOption();
        } elseif ($this->variant->id !== 0) {
            //
        } elseif ($this->product->id === 0) {
            $this->buildByCategory();
        } else {
            $variant_id = $this->product->hasOneVariant();
            if ($variant_id) {
                //$this->redirect = 'products/?variant_id='.$variant_id;
            } else {
                //$this->buildByProduct();
            }
        }
    }

    public function getRedirect(): mixed
    {
        return $this->redirect;
    }

    public function getBreadCrumb(): mixed
    {
        return $this->breadcrumb;
    }

    public function getProduct(): mixed
    {
        return $this->product;
    }

    public function randomImage(mixed $c_id): mixed
    {
        $image = '';
        $descendants = [];

        $this->category->buildDescendantIDs($c_id, $descendants);

        $descendantsString = $this->category->buildUnionString($descendants);

        $products = $this->product->getAllCustom(' WHERE category_id IN '. $descendantsString[0], $descendantsString[1]);
        if (count($products) > 0) {
            shuffle($products);
            $p = $products[0];
            $image = $p['image'];
        }
        /*
          $sql = ' WHERE t2.product_id = t1.id AND t1.category_id IN'.$d;
          $variants = $this->variant->getAllCustomPaginateSimpleJoin('*', ProductItem::$table, ProductVariant::$table, $sql, 1, 100);
          //shuffle($variants);
          $v = $variants[0];

          return $v['image'];
        */

        return $image;
    }

    public function getButtons(): mixed
    {
        return $this->buttons;
    }

    public function buildByCategory(): void
    {
        $button = [];

        if (!$this->category->getChildren($this->category->id)) {
            $sql = ' WHERE category_id = ?'
                .' AND t1.status = '.ProductItemStatus::AVAILABLE
                .' AND t1.brand_id = t2.id'
                .' AND t3.product_id = t1.id';

            $page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
            $per_page = BUTTONS_PER_PAGE;
            $group = 't1.id';
            //$order = 't2.name, t1.name';
            $order = 'price';

            $select = '*,t1.name AS product_name, t1.image AS image, t2.name AS brand_name, t1.id AS product_id, MIN(t3.price) AS price';
            $products = $this->product->getAllCustomPaginateMultiJoinGroup(
                $select,
                Model::getTable(BrandItem::class),
                Model::getTable(ProductVariant::class),
                $sql,
                $page,
                $per_page,
                $group,
                $order,
                [$this->category->id]
            );

            foreach ($products as $p) {
                $button['name'] = $p['brand_name'].' '.$p['product_name'];
                $button['image'] = $p['image'];
                $button['product_id'] = $p['product_id'];
                $button['price'] = $p['price'];
                $this->buttons[] = $button;
            }

            $this->p_nav = $this->product->getPaginatePagesMultiJoinGroup(
                $select,
                Model::getTable(BrandItem::class),
                Model::getTable(ProductVariant::class),
                $sql,
                $page,
                $per_page,
                $group,
                $order,
                [$this->category->id]
            );

        } else {
            $children = $this->category->getChildren($this->category->id);
            foreach ($children as $child) {
                $button = [];
                $this->category->load((int) $child);
                if ($this->category->hasChildren()) {
                    $button['name'] = $this->category->name;
                    $button['image'] = $this->randomImage($this->category->id);
                    $button['category_id'] = $this->category->id;
                    if ($button['image'] !== '') {
                        $this->buttons[] = $button;
                    }
                } else {
                    $button['name'] = $this->category->name;
                    $button['category_id'] = $this->category->id;

                    $products = $this->product->getAllCustom(' WHERE category_id = ?', [$this->category->id]);
                    //shuffle($products);
                    if (count($products) > 0) {
                        $p = $products[0];
                        $button['image'] = $p['image'];
                        if ($button['image'] !== '') {
                            $this->buttons[] = $button;
                        }
                    }
                }
            }
        }
    }

    public function getPNav(): mixed
    {
        return $this->p_nav;
    }

    public function buildByProduct(): void
    {
        $button = [];

        $variants = $this->variant->getAllCustom(' WHERE product_id = ?', [$this->product->id]);
        shuffle($variants);
        foreach ($variants as $v) {
            $button['name'] = $this->variant->getVariantName($v['id']);
            $button['image'] = $v['image'];
            $button['variant_id'] = $v['id'];
            $this->buttons[] = $button;
        }
    }

    public function buildBC(): void
    {
        $cats = [];

        if ($this->product->id !== 0) {
            if (!$this->product->load($this->product->id)) {
                $this->controller->http_error(404);
            }

            // set image to first variant image
            $variants = $this->variant->getAllCustom(' WHERE product_id = ? ORDER BY id', [$this->product->id]);
            if (count($variants) > 0) {
                $this->product->image = $variants[0]['image'];
            }

            $this->category->id = $this->product->category_id;
        } elseif ($this->variant->id !== 0) {
            if (!$this->variant->load($this->variant->id)) {
                $this->controller->http_error(404);
            }
            $this->product->load($this->variant->product_id);
            $this->category->id = $this->product->category_id;
        }

        if ($this->category->id > 0 && !$this->category->load((int) $this->category->id)) {
            $this->controller->http_error(404);
        }

        if ($this->category->id !== 0) {
            $current['id'] = $this->category->id;
            $current['name'] = $this->category->name;
            $cats[] = $current;

            if ($this->category->category_id > 0) {
                $this->category->buildBreadCrumb($this->category->category_id, $cats);
            }
        }

        $root['id'] = 0;
        $root['name'] = 'All Products';
        $cats[] = $root;
        $this->breadcrumb = array_reverse($cats);
    }

    public function buildByOption(): void
    {
        $button = [];

        $variants = $this->variant->getAllForOption($this->option->id);
        if (count($variants) < 1) {
            $this->controller->http_error(404);
        }

        foreach ($variants as $v) {
            $button['name'] = $v['name'];
            //	$button['name'] = $this->variant->getVariantName($v['id']);
            $button['image'] = $v['image'];
            $button['variant_id'] = $v['id'];
            $this->buttons[] = $button;
        }
    }

    public static function getTopCats(): mixed
    {
        $categories = [];
        $c = Model::load(CategoryItem::class);
        if ($_GET['module'] === 'store') {
            $sql = ' WHERE category_id = 0 AND hidden = 0 ORDER BY name';
            $categories = $c->getAllCustom($sql);
        }

        foreach ($categories as &$cat) {
            $sql = ' WHERE category_id = ? AND hidden = 0 ORDER BY name';
            $cat['sub_cats'] = $c->getAllCustom($sql, [$cat['id']]);
        }

        return $categories;
    }
}
