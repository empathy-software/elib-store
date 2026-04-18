<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

use Empathy\ELib\Tree;
use Empathy\MVC\Config;

class ArtistsTree extends Tree
{
    private $artist;
    private $data;
    private $artist_ancestors;

    public function __construct($artist)
    {
        $this->artist = $artist;
        $this->artist_ancestors = [0];

        $current_id = $this->artist->id;
        array_push($this->artist_ancestors, $current_id);

        $this->data = $this->buildTree(0, $this);
        $this->markup = $this->buildMarkup($this->data, 0, $current_id, 0);
    }

    public function buildTree($id, $tree)
    {
        $nodes = [];
        $nodes = $tree->artist->buildTree($id, $tree);

        return $nodes;
    }

    private function buildMarkup($data, $level, $current_id, $last_id)
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
        foreach ($data as $index => $value) {
            $toggle = '+';
            $folder = '<i class="far fa-file"></i>';
            $url = 'artist';

            if (in_array($value['id'], $ancestors, true)) {
                $toggle = '-';
            }

            if ($value['artist_alias'] === '') {
                $value['label'] = $value['forename'].' '.$value['surname'];
            } else {
                $value['label'] = $value['artist_alias'];
            }

            $children = 0;
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
                $markup .= $this->buildMarkup($value['children'], $level, $current_id, $value['id'], $value['banner'], $current_is_dir);
            }
            $markup .= "</li>\n";
        }
        $markup .= "</ul>\n";

        return $markup;
    }
}
