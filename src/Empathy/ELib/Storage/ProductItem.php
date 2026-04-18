<?php

declare(strict_types=1);

namespace Empathy\ELib\Storage;

use Empathy\MVC\Entity;
use Empathy\MVC\Model;

class ProductItem extends Entity
{
    public const TABLE = 'product';

    public int $id;

    public int $category_id = 1;
    public ?int $brand_id = null;
    public string $name = '';
    public ?string $description = null;
    public ?string $image = null;
    public ?string $upc = null;
    /**
     * May be {@see \Empathy\MVC\Entity} insert sentinel <code>'DEFAULT'</code>.
     */
    public int|string $status = 0;
    public ?int $vendor_id = null;
    public ?string $min_price = null;
    public int $vendor_verified = 0;
    public ?string $shipping_uk = null;
    public ?string $shipping_eu = null;
    public ?string $shipping_other = null;

    /** Populated in {@see load()}; not a DB column. */
    /** @var list<array<string, mixed>> */
    private array $images = [];

    private bool $noImageFound = false;
    private int $soldInStore = 0;

    /** Populated in {@see load()} from variant stock aggregate. */
    private int|string|null $stock = null;

    /** Populated in {@see load()} from {@see BrandItem::name}. */
    private ?string $brand = null;

    public function validates(): void
    {
        if ($this->name === '') { // || !ctype_alnum(str_replace(' ', '', $this->name)))
            $this->addValError('Invalid product name');
        }
        if ($this->description === '') {
            $this->addValError('Invalid product description');
        }
    }

    #[\Override]
    public function load(mixed $id = null): bool
    {
        $i = Model::load(ProductImage::class);
        parent::load($id);
        [$this->images, $this->noImageFound] = $i->loadByProductItemDisplayed($this);

        $v = Model::load(ProductVariant::class);
        $this->stock = $v->getStockLevels($id);

        if ($this->brand_id) {
            $b = Model::load(BrandItem::class);
            $b->load($this->brand_id);
            $this->brand = $b->name;
        }

        return true;
    }

    public function getStock(): mixed
    {
        return $this->stock;
    }

    public function getImages(): mixed
    {
        return $this->images;
    }

    public function getNoImageFound(): mixed
    {
        return $this->noImageFound;
    }

    public function getDefaultImage(): mixed
    {
        return $this->images[0];
    }

    public function hasOneVariant(): mixed
    {
        $id = 0;
        $sql = 'SELECT id FROM '.Model::getTable(ProductVariant::class).' WHERE product_id = ?';
        $error = 'Could not check for single variant on product.';
        $result = $this->query($sql, $error, [$this->id]);
        if ($result->rowCount() === 1) {
            $row = $result->fetch();
            $id = $row['id'];
        }

        return $id;
    }

    public function hasVariants(): mixed
    {
        $variants = false;
        $sql = 'SELECT id FROM '.Model::getTable(ProductVariant::class).' WHERE product_id = ?';
        $error = 'Could not check for product variants.';
        $result = $this->query($sql, $error, [$this->id]);
        if ($result->rowCount() > 0) {
            $variants = true;
        }

        return $variants;
    }

    public function convertCategory(): mixed
    {
        $sql = 'SELECT name from '.Model::getTable(CategoryItem::class).' WHERE id = ?';
        $error = 'Could not get category name.';
        $result = $this->query($sql, $error, [$this->category_id]);
        $row = $result->fetch();

        return $row['name'];
    }

    public function getOnlyVariantID(): mixed
    {
        $id = 0;
        $sql = 'SELECT id FROM '.Model::getTable(ProductVariant::class)
            .' WHERE product_id = ? LIMIT 0,1';
        $error = 'Could not get id for only product variant.';
        $result = $this->query($sql, $error, [$this->id]);
        if ($result->rowCount() > 0) {
            $row = $result->fetch();
            $id = $row['id'];
        }

        return $id;
    }

    // including variants

    public function getAllImages(): mixed
    {
        $image = [];
        $sql = 'SELECT image FROM '.Model::getTable(ProductItem::class);
        $error = 'Could not get product images.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $image[] = $row['image'];
            }
        }
        $sql = 'SELECT image FROM '.Model::getTable(ProductVariant::class);
        $error = 'Could not get variant images.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $image[] = $row['image'];
            }
        }

        return $image;
    }

    public function getPrice(): mixed
    {
        $price = 0;
        $sql = 'SELECT MIN(price) AS price FROM '.Model::getTable(ProductVariant::class)
            .' WHERE product_id = ?'
            .' AND product_id > 0';
        $error = 'Could not get price.';
        $result = $this->query($sql, $error, [$this->id]);
        if ($result->rowCount() === 1) {
            $row = $result->fetch();
            $price = $row['price'];
        }

        return $price;
    }

    // new get price function
    public function getMinPrice(mixed $id): mixed
    {
        $price = 0;
        $sql = 'SELECT MIN(price) AS price FROM '.Model::getTable(ProductVariant::class)
            .' WHERE product_id = ?'
            .' AND status = '.ProductVariantStatus::AVAILABLE;
        $error = 'Could not get price.';
        $result = $this->query($sql, $error, [$id]);
        if ($result->rowCount() === 1) {
            $row = $result->fetch();
            $price = $row['price'];
        }

        return $price;
    }

    public function loadIDByName(mixed $name): mixed
    {
        $id = 0;
        $sql = 'SELECT id FROM '.Model::getTable(ProductItem::class)
            .' WHERE name LIKE ?';

        echo $sql;
        $error = 'Could not get product id by name.';
        $result = $this->query($sql, $error, ['\''.str_replace('-', ' ', $name).'\'']);
        if ($result->rowCount() === 1) {
            $row = $result->fetch();
            $id = $row['id'];
        }

        return $id;
    }

    public function setSoldInStore(mixed $soldInStore): void
    {
        $this->soldInStore = $soldInStore;
    }

    public function getSoldInStore(): mixed
    {
        return $this->soldInStore;
    }

    public function getBrand(): mixed
    {
        return $this->brand;
    }

    /**
     * Mark products for a vendor as verified after the vendor account is approved.
     */
    public function verify(int $vendorId): void
    {
        $sql = 'UPDATE ' . Model::getTable(self::class)
            . ' SET vendor_verified = 1 WHERE vendor_id = ?';
        $this->query($sql, 'Could not verify vendor products.', [$vendorId]);
    }
}
