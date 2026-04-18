<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

use Empathy\ELib\Tree;
use Empathy\MVC\Config;

class ArtistsTree extends Tree
{
    private readonly mixed $data;
    private mixed $artist_ancestors = [0];

    public function __construct(private readonly mixed $artist)
    {
        $current_id = $this->artist->id;
        $this->artist_ancestors[] = $current_id;

        $this->data = $this->buildTree(0, $this);
        $this->markup = $this->buildMarkup($this->data, 0, $current_id, 0);
    }

    public function buildTree(mixed $id, mixed $tree): mixed
    {
        return $tree->artist->buildTree($id, $tree);
    }

    private function buildMarkup(mixed $data, mixed $level, mixed $current_id, mixed $last_id): mixed
    {
        $markup = "\n<ul";

        $ancestors = $this->artist_ancestors;

        $class = 'clearfix';
        if (!in_array($last_id, $ancestors, true)) {
            $class .= ' hidden_sections';
        }
        $markup .= " class=\"$class\"";

        if ($level === 0) {
            $markup .= ' id="tree"';
            $level++;
        }
        $markup .= ">\n";
        foreach ($data as $value) {
            $toggle = '+';
            $folder = '<i class="far fa-file"></i>';
            $url = 'artist';

            if (in_array($value['id'], $ancestors, true)) {
                $toggle = '-';
            }

            $value['label'] = $value['artist_alias'] === '' ? $value['forename'].' '.$value['surname'] : $value['artist_alias'];

            $children = count($value['children']);
            $class = 'clearfix';
            $markup .= '<li';
            if ($current_id === $value['id']) {
                $class .= ' current';
            }

            if (isset($value['hidden']) && $value['hidden']) {
                $class .= ' hidden';
            }

            $markup .= " class=\"$class\"";

            $markup .= ">\n";
            if ($children > 0) {
                $markup .= '<a class="toggle" href="http://'.Config::get('WEB_ROOT').Config::get('PUBLIC_DIR')."/admin/$url/".$value['id'];
                if ($toggle === '-') {
                    $markup .= '/?collapsed=1';
                }
                $markup .= "\">$toggle</a>";
            } else {
                $markup .= '<span class="toggle">&nbsp;</span>';
            }
            $markup .= $folder;

            if ($current_id === $value['id']) {
                $markup .= '<span class="label current">'.$value['label'].'</span>';
            } else {
                $markup .= '<span class="label"><a href="http://'.Config::get('WEB_ROOT').Config::get('PUBLIC_DIR')."/admin/$url/".$value['id'].'">'.$value['label'].'</a></span>';
            }
            if ($children > 0) {
                $markup .= $this->buildMarkup($value['children'], $level, $current_id, $value['id']);
            }
            $markup .= "</li>\n";
        }

        return $markup . "</ul>\n";
    }
}
