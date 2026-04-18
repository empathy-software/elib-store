<?php

declare(strict_types=1);

namespace Empathy\ELib\Storage;

use Empathy\MVC\Entity;

/**
 * Join entity between {@see ArtistItem} and {@see ProductItem}.
 */
class ProductArtist extends Entity
{
    public const TABLE = 'product_artist';

    public int $id;
    public int $artist_id;
    public int $product_id;
}
