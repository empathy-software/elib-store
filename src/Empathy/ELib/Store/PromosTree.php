<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

use Empathy\ELib\Tree;
use Empathy\MVC\Config;

class PromosTree extends Tree
{
    private mixed $category;
    private mixed $data;
    private mixed $category_ancestors;

    public function __construct(mixed $category, mixed $collapsed)
    {
        $this->category = $category;
        $current_id = $this->category->id;
        $this->category_ancestors = [0];

        if (!$collapsed) {
            array_push($this->category_ancestors, $current_id);
        }

        if ($current_id !== 0) {
            $this->category_ancestors = $this->category->getAncestorIDs($current_id, $this->category_ancestors);
        }

        if (!$collapsed) {
            array_push($this->category_ancestors, $current_id);
        }

        $this->data = $this->buildTree(0, $this);
        $this->markup = $this->buildMarkup($this->data, 0, $current_id, 0);
    }

    public function buildTree(mixed $id, mixed $tree): mixed
    {
        $nodes = [];
        $nodes = $tree->category->buildTree($id, $tree);

        return $nodes;
    }

    private function buildMarkup(mixed $data, mixed $level, mixed $current_id, mixed $last_id): mixed
    {
        $markup = "\n<ul";

        $ancestors = $this->category_ancestors;

        if (!in_array($last_id, $ancestors, true)) {
            $markup .= ' class="hidden_sections"';
        }
        if ($level === 0) {
            $markup .= ' id="tree"';
            $level++;
        }
        $markup .= ">\n";
        foreach ($data as $index => $value) {
            $toggle = '+';
            $folder = 't_folder_closed.gif';
            $url = 'promo_category';

            if (in_array($value['id'], $ancestors, true)) {
                $toggle = '-';
                $folder = 't_folder_open.gif';
            }

            $children = sizeof($value['children']);
            $markup .= '<li';

            $markup .= ">\n";
            if ($children > 0) {
                $markup .= '<a class="toggle" href="http://'.Config::get('WEB_ROOT').Config::get('PUBLIC_DIR')."/admin/$url/".$value['id'].'/?page=1';
                if ($toggle === '-') {
                    $markup .= '&amp;collapsed=1';
                }
                $markup .= "\">$toggle</a>";
            } else {
                $markup .= '<span class="toggle">&nbsp;</span>';
            }
            $markup .= '<img src="http://'.Config::get('WEB_ROOT').Config::get('PUBLIC_DIR')."/elib/$folder\" alt=\"\" />\n";
            if ($current_id === $value['id']) { // && $value['data'] != $current_is_section)
                $markup .= '<span class="label current">'.$value['label'].'</span>';
            } else {
                $markup .= '<span class="label"><a href="http://'.Config::get('WEB_ROOT').Config::get('PUBLIC_DIR')."/admin/$url/".$value['id'].'/?page=1">'.$value['label'].'</a></span>';
            }
            if ($children > 0) {
                $markup .= $this->buildMarkup($value['children'], $level, $current_id, $value['id']);
            }
            $markup .= "</li>\n";
        }
        $markup .= "</ul>\n";

        return $markup;
    }
}
