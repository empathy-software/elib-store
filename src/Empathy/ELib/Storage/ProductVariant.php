<?php

declare(strict_types=1);

namespace Empathy\ELib\Storage;

use Empathy\MVC\Entity;
use Empathy\MVC\Model;
use Empathy\MVC\Session;
use Empathy\MVC\Validate;

class ProductVariant extends Entity
{
    public const TABLE = 'product_variant';

    public int $id;
    public $product_id;
    public $image;
    public $sku;
    public $weight_g;
    public $weight_lb;
    public $weight_oz;
    public $price;
    public $status;
    public $stock;

    public function validates()
    {
        $this->doValType(Validate::NUM, 'weight_g', $this->weight_g, true);
        $this->doValType(Validate::NUM, 'weight_lb', $this->weight_lb, true);
        $this->doValType(Validate::NUM, 'weight_oz', $this->weight_oz, true);
        $this->doValType(Validate::NUM, 'price', $this->price, false);
    }

    public function getVariantName($id)
    {
        $name = [];
        $sql = 'SELECT option_val FROM '.Model::getTable(ProductVariantPropertyOption::class)
            .' t1, '.Model::getTable('PropertyOption').' t2 WHERE t2.id = t1.property_option_id'
            .' AND product_variant_id = ?';
        $error = 'Could not get option values for variant name.';
        $result = $this->query($sql, $error, [$id]);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                array_push($name, $row['option_val']);
            }
        }

        return implode(' / ', $name);
    }

    public function getAllForProduct($product_id, $name)
    {
        $variants = [];
        $variant = [];
        $variant_name = [$name];
        $last_id = 0;

        $productIdString = $this->buildUnionString($product_id);

        $sql = 'SELECT t1.id, t4.name AS property_name, t3.option_val, t1.weight_g, t1.weight_lb'
            .' FROM '.Model::getTable('ProductVariant').' t1'
            .' LEFT JOIN '.Model::getTable('ProductVariantPropertyOption').' t2'
            .' ON t2.product_variant_id = t1.id'
            .' LEFT JOIN '.Model::getTable('PropertyOption').' t3'
            .' ON t3.id = t2.property_option_id'
            .' LEFT JOIN '.Model::getTable('Property').' t4'
            .' ON t4.id = t3.property_id'
            .' WHERE t1.product_id IN ' . $productIdString[0];

        // old
        /*
          $sql = 'SELECT t1.product_variant_id AS id, t3.name as property_name, t4.option_val, t2.weight_g, t2.weight_lb FROM '
          .ProductVariant::$table.' t2, '.ProductVariantPropertyOption::$table.' t1, '.Property::$table.' t3 LEFT JOIN '.PropertyOption::$table
          .' t4 ON t3.id = t4.property_id WHERE t4.id = property_option_id AND t2.id = t1.product_variant_id'
          .' AND t2.product_id = '.$product_id.' ORDER BY t2.weight_g, t2.weight_lb, t2.id, property_name';
        */

        $error = 'Could not get properties for product.';
        $result = $this->query($sql, $error, $productIdString[1]);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                if ($last_id !== 0) {
                    if ($last_id !== $row['id']) {
                        $variant['name'] = implode('-', str_replace(' ', '-', $variant_name));
                        array_push($variants, $variant);
                        $variant = [];
                        $variant_name = [$name];
                    }
                }
                if (!isset($variant['id'])) {
                    $variant['id'] = $row['id'];
                    $variant['weight_g'] = $row['weight_g'];
                    $variant['weight_lb'] = $row['weight_lb'];
                }
                $property_name = $row['property_name'];
                $variant[$property_name] = $row['option_val'];
                array_push($variant_name, $row['option_val']);
                $last_id = $row['id'];
            }
        }
        $variant['name'] = implode('-', $variant_name);
        array_push($variants, $variant);

        return $variants;
    }

    public function getAllForOption($option_id)
    {
        $variants = [];
        $sql = 'SELECT v.id AS id, p.name AS name, v.image AS image FROM '.Model::getTable(ProductVariantPropertyOption::class).' o, '.Model::getTable(ProductVariant::class)
            .' v, '.Model::getTable(ProductItem::class).' p WHERE p.id = v.product_id AND o.product_variant_id = v.id AND o.property_option_id = ?';
        $error = 'Could not get variants by property.';
        $result = $this->query($sql, $error, [$option_id]);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                array_push($variants, $row);
            }
        }

        return $variants;
    }

    public function getCartData($ids)
    {
        $products = [];
        $shippingCountry = Session::get('shipping_country') ? Session::get('shipping_country') : 'GB';

        // @todo: override shipping amounts
        // with variant values if they exist

        $sql = 'SELECT t5.name, t5.shipping_uk, t5.shipping_eu, t5.shipping_other, t1.product_id, t1.price, t1.id, t4.name AS p_name, t3.option_val, t1.weight_g, t1.weight_lb'
            .' FROM '.Model::getTable(ProductItem::class).' t5, '.Model::getTable(ProductVariant::class).' t1'
            .' LEFT JOIN '.Model::getTable(ProductVariantPropertyOption::class).' t2'
            .' ON t2.product_variant_id = t1.id'
            .' LEFT JOIN '.Model::getTable(PropertyOption::class).' t3'
            .' ON t3.id = t2.property_option_id'
            .' LEFT JOIN '.Model::getTable(Property::class).' t4'
            .' ON t4.id = t3.property_id'
            .' WHERE t1.id IN '.$ids[0]
            .' AND t5.id = t1.product_id';

        // old
        /*
          $sql = 'SELECT t1.name, t3.name AS p_name, t4.price, t2.option_val, t4.id FROM '.ProductItem::$table.' t1,'
          .' '.PropertyOption::$table.' t2, '
          .' '.Property::$table.' t3, '.ProductVariant::$table.' t4'
          .' LEFT JOIN '.ProductVariantPropertyOption::$table.' t5 ON t5.product_variant_id = t4.id'
          .' WHERE t4.id IN '.$ids
          .' AND t4.product_id = t1.id'
          .' AND t5.property_option_id = t2.id'
          .' AND t3.id = t2.property_id';
        */

        $error = 'Could not load data for shopping cart.';
        $result = $this->query($sql, $error, $ids[1]);

        //        $rows = $result->fetchAll();
        //        print_r($rows);
        //        exit();

        if ($result->rowCount() > 0) {
            $name = '';
            $options = [];
            $properties = [];
            $id = 0;
            $price = 0;
            $product_id = 0;
            $shipping_uk = 0;
            $shipping_eu = 0;
            $shipping_other = 0;
            foreach ($result as $row) {
                /*
                  if ($name != $row['name']) {
                  if ($name != '') {
                */
                if ($id !== $row['id']) {
                    if ($id !== 0) {
                        $item['name'] = $name;
                        $item['options'] = implode(', ', $options);
                        $item['properties'] = implode(', ', $properties);
                        $item['id'] = $id;
                        $item['price'] = $price;
                        $item['product_id'] = $product_id;
                        $item['shipping'] = $shippingCountry === 'GB' ? $shipping_uk :
                            (\Empathy\ELib\Country\Country::isEurope($shippingCountry) ? $shipping_eu : $shipping_other);
                        array_push($products, $item);
                    }
                    $options = [];
                    $properties = [];
                }
                $name = $row['name'];
                $id = $row['id'];
                $price = $row['price'];
                $product_id = $row['product_id'];

                $shipping_uk = $row['shipping_uk'];
                $shipping_eu = $row['shipping_eu'];
                $shipping_other = $row['shipping_other'];

                array_push($options, $row['option_val']);
                array_push($properties, $row['p_name']);
            }
            $item['name'] = $name;
            $item['options'] = implode(', ', $options);
            $item['properties'] = implode(', ', $properties);
            $item['id'] = $id;
            $item['price'] = $price;
            $item['product_id'] = $product_id;
            $item['shipping_uk'] = $shipping_uk;
            $item['shipping_eu'] = $shipping_eu;
            $item['shipping_other'] = $shipping_other;
            $item['shipping'] = $shippingCountry === 'GB' ? $shipping_uk :
                (\Empathy\ELib\Country\Country::isEurope($shippingCountry) ? $shipping_eu : $shipping_other);
            array_push($products, $item);
        }

        return $products;
    }

    public function getAllColourVariants($product_id)
    {
        $variants = [];
        $sql = 'SELECT t1.id, t1.image, t1.weight_g, t1.weight_lb, t1.weight_oz, t1.price, t3.image AS other_image'
            .' FROM '.Model::getTable(ProductVariant::class).' t1'
            .' LEFT JOIN '.Model::getTable(ProductVariantPropertyOption::class).' t2'
            .' ON t2.product_variant_id = t1.id'
            .' LEFT JOIN '.Model::getTable(PropertyOption::class).' t0'
            .' ON t0.id = t2.property_option_id'
            .' LEFT JOIN '.Model::getTable(ProductColour::class).' t3'
            .' ON t3.property_option_id = t0.id'
            .' WHERE t1.product_id = ?'
            .' AND t0.property_id = 2'
            .' AND t3.product_id = t1.product_id';

        $error = 'Could not get variants.';
        $result = $this->query($sql, $error, [$product_id]);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                array_push($variants, $row);
            }
        }

        return $variants;
    }

    public function findVariant($options, $product_id)
    {
        $i = 2;
        $variant_id = 0;
        $sql = 'SELECT t1.id FROM '.Model::getTable(ProductVariant::class).' t1';
        foreach ($options as $option) {
            if ($i === 2) {
                $sql .= ' LEFT JOIN '.Model::getTable(ProductVariantPropertyOption::class).' t'.$i.' ON t'.$i.'.product_variant_id = t'.($i - 1).'.id';
            } else {
                $sql .= ' LEFT JOIN '.Model::getTable(ProductVariantPropertyOption::class).' t'.$i.' ON t'.$i.'.product_variant_id = t'.($i - 1).'.product_variant_id';
            }
            $i++;
        }

        $i = 2;
        foreach ($options as $option) {
            if ($i === 2) {
                $sql .= ' WHERE t'.$i.'.property_option_id = '.$option;
            } else {
                $sql .= ' AND t'.$i.'.property_option_id = '.$option;
            }
            $i++;
        }
        $sql .= ' AND t1.product_id = '.$product_id;
        $sql .= ' AND t1.status = '.ProductVariantStatus::AVAILABLE;

        $error = 'Could not do search for variant.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() === 1) {
            $row = $result->fetch();
            $variant_id = $row['id'];
        }

        return $variant_id;
    }

    public function getCategories($ids)
    {
        $idsString = $this->buildUnionString($ids);
        $cat_ids = [];
        $sql = 'SELECT DISTINCT t1.category_id AS id FROM '.Model::getTable(ProductItem::class).' t1,'
            .' '.Model::getTable(ProductVariant::class).' t2'
            .' WHERE t2.product_id = t1.id'
            .' AND t2.id IN ' . $idsString[0];
        $error = 'Could not get category ids';
        $result = $this->query($sql, $error, $idsString[1]);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                array_push($cat_ids, $row['id']);
            }
        }

        return $cat_ids;
    }

    public function getStockLevels($id)
    {
        $stock = 0;
        $sql = 'SELECT SUM(v.stock) as stock FROM ' . Model::getTable(ProductVariant::class)
            .' v, '.Model::getTable(ProductItem::class).' p WHERE p.id = v.product_id'
            .' and p.id = ?'
            .' and v.status = '.ProductVariantStatus::AVAILABLE;
        $error = 'Could not get variant stock levels.';
        $result = $this->query($sql, $error, [$id]);
        if ($result->rowCount() > 0) {
            $rows = $result->fetchAll();
            $stock = $rows[0]['stock'];
        }
        return $stock;
    }
}
