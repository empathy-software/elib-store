<?php

declare(strict_types=1);

namespace Empathy\ELib\Storage;

use Empathy\MVC\Entity;
use Empathy\MVC\Model;

class ArtistItem extends Entity
{
    public const TABLE = 'artist_item';

    public int $id;

    public ?string $artist_alias = null;
    public ?string $forename = null;
    public ?string $surname = null;
    public ?string $bio = null;
    public ?string $image = null;
    public int $active = 1;

    public function validates(): void {
        if ($this->forename === '' || $this->surname === '') {
            $this->addValError('Invalid artist name. Please enter a full name.');
        }
    }

    public function buildTree(mixed $current, mixed $tree): mixed {
        $i = 0;
        $nodes = [];
        $sql = 'SELECT id,artist_alias,forename,surname FROM '.Model::getTable(self::class).' ORDER BY surname, forename';
        $error = 'Could not get artists.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $id = $row['id'];
                $nodes[$i]['id'] = $id;
                $nodes[$i]['artist_alias'] = $row['artist_alias'];
                $nodes[$i]['forename'] = $row['forename'];
                $nodes[$i]['surname'] = $row['surname'];
                $i++;
            }
        }

        return $nodes;
    }

    // produce a list of artists ordered correctly
    // not used to produce the artist tree
    public function getArtists(): mixed {
        $artist = [];
        $sql = 'SELECT * FROM '.Model::getTable(self::class)
            .' WHERE active = 1'
            .' ORDER BY surname, forename';

        $error = 'Could not get list of artists.';
        $result = $this->query($sql, $error);
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                $id = $row['id'];

                if ($row['artist_alias'] === '') {
                    //$artist[$id] = $row['surname'].', '.$row['forename'];
                    $artist[$id] = $row['forename'].' '.$row['surname'];
                } else {
                    $artist[$id] = $row['artist_alias'];
                }

            }
        }

        return $artist;
    }

    public function getBios(): mixed {
        $sql = 'SELECT t1.id AS artist_id, t3.id AS product_id, t1.artist_alias,'
            .' t1.forename, t1.surname, t1.bio, t3.name, t3.image, t3.category_id, t3.price'
            .' FROM '.Model::getTable(self::class).' t1'
            .' LEFT JOIN '.Model::getTable(ProductArtist::class).' t2 ON t2.artist_id = t1.id'
            .' LEFT JOIN '.Model::getTable(ProductItem::class).' t3 ON t3.id = t2.product_id'
            .' ORDER BY t1.id';
        $error = 'Could not get bios.';
        $result = $this->query($sql, $error);
        $last_artist_id = 0;
        $bio = [];
        $bios = [];
        $book = [];
        $books = [];
        if ($result->rowCount() > 0) {
            foreach ($result as $row) {
                if ($last_artist_id !== $row['artist_id']) {
                    if (sizeof($books) > 0) {
                        $bio['books'] = $books;
                        $books = [];
                    }
                    if (sizeof($bio) > 0) {
                        array_push($bios, $bio);
                        $bio = [];
                    }

                    $last_artist_id = $row['artist_id'];
                    $bio['artist_id'] = $row['artist_id'];
                    $bio['artist_alias'] = $row['artist_alias'];
                    $bio['forename'] = $row['forename'];
                    $bio['surname'] = $row['surname'];
                    if ($row['artist_alias'] === '') {
                        $bio['artist'] = $row['forename'].' '.$row['surname'];
                    } else {
                        $bio['artist'] = $row['artist_alias'];
                    }
                    $bio['bio'] = $row['bio'];
                }

                if (isset($row['product_id'])) {
                    if ($row['category_id'] === 14) {
                        $book = [];
                        $book['id'] = $row['product_id'];
                        $book['image'] = $row['image'];
                        $book['name'] = $row['name'];
                        $book['price'] = $row['price'];
                        array_push($books, $book);
                    }
                }
            }

        }
        if (sizeof($books) > 0) {
            $bio['books'] = $books;
        }
        if (sizeof($bio) > 0) {
            array_push($bios, $bio);
        }

        return $bios;
    }
}
