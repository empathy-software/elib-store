<?php

declare(strict_types=1);

namespace Empathy\ELib\Storage;

use Empathy\MVC\Entity;

class PromoItem extends Entity
{
    public const TABLE = 'promo';

    public int $id;

    public int $category_id;

    public ?string $name = null;
    public ?string $alt = null;
    public ?string $url = null;
    public ?string $image = null;

    public string $hidden = '';

    public function validates(): void
    {
        if (($this->url ?? '') === '') {
            $this->addValError('Invalid URL');
        }
        if (($this->name ?? '') === '') {
            $this->addValError('Invliad name.');
        }
    }

}
