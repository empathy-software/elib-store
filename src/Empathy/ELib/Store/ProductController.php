<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

use Empathy\ELib\File\Image as EImageUpload;
use Empathy\ELib\Storage\BrandItem;
use Empathy\ELib\Storage\CategoryItem;
use Empathy\ELib\Storage\CategoryProperty;
use Empathy\ELib\Storage\ProductColour;
use Empathy\ELib\Storage\ProductImage;
use Empathy\ELib\Storage\ProductItem;
use Empathy\ELib\Storage\ProductVariant;
use Empathy\ELib\Storage\ProductVariantPropertyOption;
use Empathy\ELib\Storage\Property;
use Empathy\ELib\Storage\PropertyOption;
use Empathy\MVC\Model;

class ProductController extends AdminController
{
    public function assertID(): void
    {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $this->redirect('admin/category');
        }
    }

    public function edit(): void
    {
        $this->setTemplate('elib://admin/product.tpl');
        $p = Model::load(ProductItem::class);
        //$pr = new ProductRange($this);

        if (isset($_POST['submit_product'])) {

            /*
              if (!isset($_POST['range'])) {
              $_POST['range'] = array();
              }
              $range = $_POST['range'];
              $pr->updateForProduct($p->id, $range);
            */

            $p->load((int) $_POST['id']);
            $old_product_name = $p->name;
            $p->name = $_POST['name'];
            $p->description = $_POST['description'];
            $p->shipping_uk = ($_POST['shipping_uk'] ?? '') === '' ? null : (float) $_POST['shipping_uk'];
            $p->shipping_eu = ($_POST['shipping_eu'] ?? '') === '' ? null : (float) $_POST['shipping_eu'];
            $p->shipping_other = ($_POST['shipping_other'] ?? '') === '' ? null : (float) $_POST['shipping_other'];

            $p->status = $_POST['sold_in_store'] === 1 ? 1 : 0;

            $p->brand_id = (int) $_POST['brand_id'];

            $p->validates();
            if ($p->hasValErrors()) {
                // old_product_name along with code in admin_header
                // prevents breadcrumb from breaking on errors
                $this->assign('product', $p);
                $this->assign('old_product_name', $old_product_name);
                $this->assign('errors', $p->getValErrors());
            } else {
                //$p->price = $_POST['price'];
                $p->save();
                $this->redirect('admin/product/' . $p->id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/product/' . $_GET['id']);
        } else {
            $p->load((int) $_GET['id']);

            $p->setSoldInStore(0);
            if ($p->status > 0) {
                $p->setSoldInStore(1);
            }

            //$product_ranges = $pr->loadForProduct($p->id);

            //$r = new RangeItem($this);
            //$ranges = $r->loadAllIndexed();

            $c = Model::load(CategoryItem::class);
            $category = $c->loadIndexed($c->category_id);

            //$this->assign("product_ranges", $product_ranges);
            //$this->assign("ranges", $ranges);

            $this->assign('product', $p);
            $this->assign('categories', $category);

            $sold = [];
            $sold[0] = 'No';
            $sold[1] = 'Yes';
            $this->assign('sold_in_store', $sold);

            $b = Model::load(BrandItem::class);
            $brands = $b->getBrands();
            $this->assign('brands', $brands);

        }
    }

    #[\Override]
    public function default_event(): void
    {
        $this->setTemplate('elib://admin/product.tpl');
        $p = Model::load(ProductItem::class);
        $p->load((int) $_GET['id']);

        $this->assign('product', $p);

        if (is_numeric($p->brand_id)) {
            $b = Model::load(BrandItem::class);
            $b->load($p->brand_id);
            $this->assign('brand', $b->name);
        }

        $v = Model::load(ProductVariant::class);
        $c = Model::load(ProductColour::class);

        $has_colours = $c->hasColours($p->id);
        if ($has_colours) {
            $variants = $v->getAllColourVariants($p->id);
            $ids = [];
            $params = [];
            foreach ($variants as $index => $item) {
                $ids[] = $item['id'];
                //if($item['image'] == '' && $item['other_image'] != '')
                // product colour images override variant images
                if ($item['other_image'] !== '') {
                    $variants[$index]['image'] = $variants[$index]['other_image'];
                }
            }

            $sql = ' WHERE product_id = ?';
            $params[] = $p->id;
            if (count($ids) > 0) {
                $idsString = $v->buildUnionString($ids);
                $sql .= ' AND id NOT IN ' . $idsString[0];
                $params = array_merge($params, $idsString[1]);
            }
            $variants = array_merge($variants, $v->getAllCustom($sql, $params));
        } else {
            $params = [];
            $sql = ' WHERE product_id = ?';
            $params[] = $p->id;
            $variants = $v->getAllCustom($sql, $params);
        }

        $property = Model::load(Property::class);

        foreach ($variants as $index => $item) {
            $props = $property->loadForVariant($item['id']);
            $variants[$index]['properties'] = $props;
        }

        $this->assign('has_colours', $has_colours);

        $this->assign('variants', $variants);
    }

    public function upload_image(): void
    {
        $this->setTemplate('elib://admin/product.tpl');
        if (isset($_POST['upload'])) {
            $_GET['id'] = $_POST['id'];
        }

        $p = Model::load(ProductItem::class);
        $i = Model::load(ProductImage::class);
        $p->load((int) $_GET['id']);

        $this->assign('product', $p);

        if (isset($_POST['upload'])) {
            $images = [];
            if (!is_array($_FILES['file']['name'])) {

                $images[0] = $_FILES['file'];
            } else {
                $images = EImageUpload::reArrayFiles($_FILES['file']);
            }

            $error = '';
            $j = 0;
            foreach ($images as $image) {
                $_FILES['file'] = $image;

                $d = [['tn_', 100, 100], ['mid_', 400, 276]];
                $u = new ImageUpload('products', true, $d);

                if ($u->error !== '') {
                    $error = $u->error;
                    break;
                } else {
                    // update db
                    $i->image = $u->file;
                    $i->product_id = (int) $_GET['id'];
                    $i->default_image = $j === 0 ? $p->getNoImageFound() ? 1 : 0 : 0;
                    $i->insert();
                }
                $j++;
            }

            if ($error !== '') {
                $this->assign('error', $error);
            } else {
                $this->redirect('admin/product/' . $p->id);
            }

        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/product/' . $_POST['id']);
        }
    }

    public function resize_images(): void
    {
        $this->setTemplate('elib://admin/product.tpl');
        if (isset($_POST['submit'])) {
            set_time_limit(300);
            if (isset($_POST['tn_width']) && is_numeric($_POST['tn_width'])
                && isset($_POST['tn_height']) && is_numeric($_POST['tn_height'])
                && isset($_POST['mid_width']) && is_numeric($_POST['mid_height'])) {
                $p = Model::load(ProductItem::class);
                $images = $p->getAllImages();

                $d = [['tn_', $_POST['tn_width'], $_POST['tn_height']],
                    ['mid_', $_POST['mid_width'], $_POST['mid_height']]];
                $u = new ImageUpload('', false, $d);
                $u->resize($images);
            }
        }
    }

    public function delete_image(): void
    {
        $productId = (int) $_GET['product_id'];
        $imageId = (int) $_GET['image_id'];

        if ($imageId === 0) {
            $this->redirect('admin/product/' . $productId);
            return;
        }

        $i = Model::load(ProductImage::class);
        $i->load($imageId);
        $i->delete();
        $this->redirect('admin/product/' . $i->product_id);
    }

    public function make_default_image(): void
    {
        $imageId = (int) $_GET['image_id'];
        $i = Model::load(ProductImage::class);
        $i->load($imageId);
        $i->makeDefault();
        $this->redirect('admin/product/' . $i->product_id);
    }

    public function delete(): void
    {
        $p = Model::load(ProductItem::class);
        $p->load((int) $_GET['id']);
        if (!$p->hasVariants()) {
            $images_removed = false;
            if ($p->image !== '') {
                $u = new ImageUpload('products', false, []);
                if ($u->remove([$p->image])) {
                    $images_removed = true;
                }
            }
            if ($p->image === '' || $images_removed) {
                $p->delete();
                $this->redirect('admin/category/' . $p->category_id);
            }
        } else {
            $this->redirect('admin/product/' . $p->id);
        }
    }

    public function delete_variant(): void
    {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $_GET['id'] = 0;
        }
        $v = Model::load(ProductVariant::class);
        $v->load((int) $_GET['id']);
        $images_removed = false;
        if ($v->image !== '') {
            $i = new ImageUpload('products', false, []);
            if ($i->remove([$v->image])) {
                $images_removed = true;
            }
        }
        if ($v->image === '' || $images_removed) {
            $o = Model::load(ProductVariantPropertyOption::class);
            $o->emptyByVariant($v->id);
            $v->delete();
        }
        $this->redirect('admin/product/' . $v->product_id);
    }

    public function add_variant(): void
    {
        $this->assertID();
        $v = Model::load(ProductVariant::class);
        $v->product_id = (int) $_GET['id'];
        $v->weight_g = 0;
        $v->weight_lb = 0.0;
        $v->weight_oz = 0.0;
        $v->price = 'DEFAULT';
        $v->status = 'DEFAULT';
        $v->insert();
        $this->redirect('admin/product/' . $_GET['id']);
    }

    public function add(): void
    {
        $p = Model::load(ProductItem::class);
        $p->category_id = $_GET['id'];
        $p->brand_id = 0;
        $p->name = 'New Product';
        $p->description = 'No description.';
        $p->status = 'DEFAULT';
        $p->vendor_verified = 1;
        $p->vendor_id = 1;
        $p->insert();
        $this->redirect('admin/category/' . $_GET['id']);
    }

    public function edit_variant(): void
    {
        $this->assertID();
        $this->setTemplate('elib://admin/product.tpl');
        $v = Model::load(ProductVariant::class);
        $v->load((int) $_GET['id']);
        $this->assign('variant', $v);
        $p = Model::load(ProductItem::class);
        $p->load($v->product_id);
        $this->assign('product', $p);
        // get variant data
        $v_array = $this->entityToJsonArray($v);
        $props = Model::load(Property::class);
        $v_array['properties'] = $props->loadForVariant($v_array['id']);
        $this->assign('v', $v_array);

        if (isset($_POST['save'])) {
            $v = Model::load(ProductVariant::class);
            $v->load((int) $_POST['id']);
            $v->weight_g = (int) $_POST['weight_g'];
            $v->weight_lb = (float) $_POST['weight_lb'];
            $v->weight_oz = (float) $_POST['weight_oz'];
            $v->price = (float) $_POST['price'];
            $v->stock = (int) $_POST['stock'];
            $v->validates();
            if ($v->hasValErrors()) {
                $this->assign('variant', $v);
                $this->assign('errors', $v->getValErrors());
            } else {
                $v->save();
                $this->redirect('admin/product/' . $v->product_id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/product/' . $v->product_id);
        }
    }

    public function upload_variant_image(): void
    {
        $this->setTemplate('elib://admin/product.tpl');
        $this->assertID();
        $v = Model::load(ProductVariant::class);
        $v->load((int) $_GET['id']);

        $this->assign('variant', $v);

        // get variant data
        $v_array = $this->entityToJsonArray($v);
        $props = Model::load(Property::class);
        $v_array['properties'] = $props->loadForVariant($v_array['id']);
        $this->assign('v', $v_array);

        $p = Model::load(ProductItem::class);
        $p->load($v->product_id);
        $this->assign('product', $p);


        if (isset($_POST['upload'])) {
            $d = [['tn_', 100, 100], ['mid_', 400, 276]];
            $u = new ImageUpload('products', true, $d);

            if ($u->error !== '') {
                $this->assign('error', $u->error);
            } else {
                if ($v->image !== '') {
                    $u->remove([$v->image]);
                }
                $v->image = $u->file;
                $v->save();
                $this->redirect('admin/product/' . $p->id);
            }
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/product/' . $p->id);
        }
    }

    public function variant_properties(): void
    {
        $this->setTemplate('elib://admin/product.tpl');
        $this->assertID();

        if (isset($_POST['save'])) {
            $p = Model::load(ProductVariantPropertyOption::class);
            $p->emptyByVariant($_GET['id']);
            $p->product_variant_id = (int) $_GET['id'];
            //if(isset($_POST['property']))
            // {
            foreach ($_POST['property'] as $item) {
                if ($item > 0 && is_numeric($item)) {
                    $p->property_option_id = (int) $item;
                    $p->insert();
                }
            }
            // }
            $v = Model::load(ProductVariant::class);
            $v->load((int) $_GET['id']);
            $this->redirect('admin/product/' . $v->product_id);
        } elseif (isset($_POST['cancel'])) {
            $this->redirect('admin/product/' . $_GET['id']);
        }


        $v = Model::load(ProductVariant::class);
        $v->load((int) $_GET['id']);

        // get variant data
        $v_array = $this->entityToJsonArray($v);
        $property = Model::load(Property::class);
        $v_array['properties'] = $property->loadForVariant($v_array['id']);
        $this->assign('v', $v_array);


        $p = Model::load(ProductItem::class);
        $p->load($v->product_id);

        $c = Model::load(CategoryItem::class);
        $cats = $c->getAncestorIds($p->category_id, []);

        $cp = Model::load(CategoryProperty::class);

        $cats[] = $p->category_id;
        $props = $cp->getPropertiesByCategory($cats);

        //array_push($props, 2); // always allow colour property

        $this->assign('product', $p);
        $this->assign('variant', $v);

        if (count($props) > 0) {
            $properties = $property->getAllWithOptions($props);
            $this->assign('properties', $properties);

            $pv = Model::load(ProductVariantPropertyOption::class);
            $sql = ' WHERE product_variant_id = ?';
            $options = $pv->getAllCustom($sql, [$_GET['id']]);
            $o = [];
            foreach ($options as $value) {
                $o[] = $value['property_option_id'];
            }
            $this->assign('options', $o);
        }
    }

    public function edit_colours(): void
    {
        $this->setTemplate('elib://admin/product.tpl');
        $p = Model::load(ProductItem::class);
        $p->load((int) $_GET['id']);

        $c = Model::load(ProductColour::class);
        $sql = ' WHERE t1.property_option_id = t2.id AND t1.product_id = ?';
        $select = 't1.id AS id,t1.image,t2.option_val';
        $colours = $c->getAllCustomPaginateSimpleJoin($select, Model::getTable(PropertyOption::class), $sql, 1, 100, '', [$p->id]);
        $this->assign('colours', $colours);
        $this->assign('product', $p);
    }

    public function add_colour(): void
    {
        $this->setTemplate('elib://admin/product.tpl');
        $p = Model::load(ProductItem::class);
        $p->load((int) $_GET['id']);
        $this->assign('product', $p);

        $pr = Model::load(Property::class);
        $colour_id = $pr->findColourId();

        $colours = [];
        if ($colour_id > 0) {
            $o = Model::load(PropertyOption::class);
            $colours = $o->getColoursIndexed(2);
        }
        $this->assign('colours', $colours);

        if (isset($_POST['submit_colour'])) {
            $d = [['tn_', 100, 100], ['mid_', 400, 276]];
            $u = new ImageUpload('', true, $d);

            if ($u->error !== '') {
                $this->assign('error', $u->error);
            } else {
                // update db
                $c = Model::load(ProductColour::class);
                $c->product_id = $_POST['id'];
                $c->property_option_id = $_POST['colour'];
                $c->image = $u->file;
                $c->insert();

                $this->redirect('admin/product/edit_colours/' . $_POST['id']);
            }

        }
    }

    public function delete_colour(): void
    {
        $p = Model::load(ProductColour::class);
        $p->load((int) $_GET['id']);

        $images_removed = false;
        if ($p->image !== '') {
            $u = new ImageUpload('', false, []);
            if ($u->remove([$p->image])) {
                $images_removed = true;
            }
        }
        if ($p->image === '' || $images_removed) {
            $p->delete();
        }
        $this->redirect('admin/product/edit_colours/' . $p->product_id);
    }

    public function edit_colour(): void
    {
        $this->setTemplate('elib://admin/product.tpl');
        if (isset($_POST['save_colour'])) {
            $c = Model::load(ProductColour::class);
            $c->load((int) $_POST['id']);
            $c->property_option_id = $_POST['colour'];

            if ($_FILES['file']['name'] !== '') {
                $images_removed = false;
                $u = new ImageUpload('', false, []);
                if ($u->remove([$c->image])) {
                    $images_removed = true;
                }
                if ($c->image === '' || $images_removed) {
                    $d = [['tn_', 100, 100], ['mid_', 400, 276]];
                    $u = new ImageUpload('', true, $d);

                    if ($u->error !== '') {
                        $this->assign('error', $u->error);
                    } else {
                        $c->image = $u->file;
                    }
                }
            }

            $c->save();
            $this->redirect('admin/product/edit_colours/' . $c->product_id);
        }

        $c = Model::load(ProductColour::class);
        $c->load((int) $_GET['id']);

        $p = Model::load(ProductItem::class);
        $p->load($c->product_id);

        $this->assign('product', $p);
        $this->assign('product_colour', $c);

        $o = Model::load(PropertyOption::class);
        $colours = $o->getColoursIndexed(2);

        $this->assign('colours', $colours);

        $this->assign('colour', $colours[$c->property_option_id]);
    }

    public function variants_wizard(): void
    {
        $this->setTemplate('elib://admin/product.tpl');
        if (isset($_POST['submit'])) {
            $sets = [];
            foreach ($_POST['property'] as $value) {
                $sets[] = $value;
            }

            $g = new CombGen($sets);
            $results = $g->getResults();
            $v = Model::load(ProductVariant::class);
            $v->product_id = $_POST['product_id'];
            $v->weight_g = (int) $_POST['weight_g'];
            $v->weight_lb = ($_POST['weight_lb'] ?? '') === '' ? null : (float) $_POST['weight_lb'];
            $v->weight_oz = ($_POST['weight_oz'] ?? '') === '' ? null : (float) $_POST['weight_oz'];
            $v->price = (float) $_POST['price'];
            $v->status = 1;

            $v->validates();
            if ($v->hasValErrors()) {
                //die('errors');
            } else {

                foreach ($results as $item) {
                    $v->id = $v->insert();
                    // filter out empty values
                    $options = array_values(array_filter(explode('-', (string) $item)));
                    foreach ($options as $o) {
                        $p = Model::load(ProductVariantPropertyOption::class);
                        $p->product_variant_id = $v->id;
                        $p->property_option_id = (int) $o;
                        $p->insert();
                    }
                }
            }
            $this->redirect('admin/product/' . $v->product_id);
        }

        $p = Model::load(ProductItem::class);
        $p->load((int) $_GET['id']);
        $this->assign('product', $p);

        $c = Model::load(CategoryItem::class);
        $cats = $c->getAncestorIds($p->category_id, []);
        $cp = Model::load(CategoryProperty::class);
        $cats[] = $p->category_id;
        $props = $cp->getPropertiesByCategory($cats);
        //array_push($props, 2); // always allow colour property

        if (count($props) > 0) {
            $property = Model::load(Property::class);
            $properties = $property->getAllWithOptions($props);
            $this->assign('properties', $properties);

            /*
              $pv = new ProductVariantPropertyOption($this);
              $sql = ' WHERE product_variant_id = '.$_GET['id'];
              $options = $pv->getAllCustom(ProductVariantPropertyOption::$table, $sql);
              $o = array();
              foreach ($options as $index => $value) {
              array_push($o, $value['property_option_id']);
              }
              $this->assign('options', $o);
            */
        }

        $v = Model::load(ProductVariant::class);
        $v->weight_g = 0;
        $v->weight_lb = 0.0;
        $v->weight_oz = 0.0;
        $v->price = 0.0;
        $this->assign('variant', $v);

        // get colours
        $c = Model::load(ProductColour::class);
        $colours = $c->getColourOptionIDs($p->id);
        $this->assign('colours', $colours);

    }

    /**
     * @return array<string, mixed>
     */
    private function entityToJsonArray(object $entity): array
    {
        $json = json_encode($entity);
        if ($json === false) {
            throw new \RuntimeException('json_encode failed');
        }
        $data = json_decode($json, true);
        if (!is_array($data)) {
            throw new \RuntimeException('json_decode failed');
        }

        return $data;
    }

    /*
      public function attributes()
      {
      if (!isset($_GET['id'])) {
      $_GET['id'] = $_POST['product_id'];
      }

      $p = new ProductItem($this);
      $p->id = $_GET['id'];
      $p->load(ProductItem::$table);

      $a = new Attribute($this);
      $pa = new ProductAttribute($this);

      $s = new StockItem($this);
      $stock_exists = $s->stockExists($p->id);

      if (isset($_POST['save_attr']) && !$stock_exists) {
      if (!isset($_POST['attribute'])) {
      $_POST['attribute'] = array();
      }

      $attribute = $_POST['attribute'];

      $product_id = $p->id;

      $pa->updateForProduct($product_id, $attribute);
      $this->redirect("admin/products");
      } else {
      $attr = $a->loadIndexed();
      $selected_attr = $pa->loadForProduct($p->id);

      $this->assign("stock_exists", $stock_exists);
      $this->assign("selected_attr", $selected_attr);
      $this->assign("attributes", $attr);
      $this->assign("product", $p);
      }
      }

      // image related
      public function unlinkImage($file)
      {
      unlink(DOC_ROOT.PUBLIC_DIR."/img/uploads/$file");
      }

      public function reset_image()
      {
      $data = new DataItem();

      if (isset($_POST['reset']) || isset($_POST['cancel'])) {
      $_GET['id'] = $_POST['id'];
      $data->id = $_GET['id'];
      $data->getItem();

      if (isset($_POST['reset'])) {
      $this->unlinkImage($data->image);

      $data->resetImage();
      }
      $this->redirect("admin/?section=sections&id=".$data->section_id);
      }

      $data->getItem();
      $this->setTemplate("data_item.tpl");
      $this->assign("operation", "Reset Image");
      $this->assign("data", $data);
      $this->setNavigation($data->section_id, $data->heading);
      }
    */

}
