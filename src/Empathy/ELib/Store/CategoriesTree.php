<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

use Empathy\ELib\Tree;
use Empathy\MVC\Config;

class CategoriesTree extends Tree
{
    private readonly mixed $data;
    private mixed $category_ancestors = [0];

    public function __construct(private readonly mixed $category, mixed $collapsed, mixed $url = '')
    {
        $this->url = $url;
        $current_id = $this->category->id;
        if (!$collapsed) {
            $this->category_ancestors[] = $current_id;
        }

        if ($current_id !== 0) {
            $this->category_ancestors = $this->category->getAncestorIDs($current_id, $this->category_ancestors);
        }

        if (!$collapsed) {
            $this->category_ancestors[] = $current_id;
        }

        $this->data = $this->buildTree(0, $this);
        $this->markup = $this->buildMarkup($this->data, 0, $current_id, 0);
    }

    public function buildTree(mixed $id, mixed $tree): mixed
    {
        return $tree->category->buildTree($id, $tree);
    }

    private function buildMarkup(mixed $data, mixed $level, mixed $current_id, mixed $last_id): mixed
    {
        $markup = "\n<ul";

        $ancestors = $this->category_ancestors;

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
            $folder = '<i class="far fa-folder"></i>';
            $url = $this->url === '' ? 'admin/category' : $this->url;

            if (in_array($value['id'], $ancestors, true)) {
                $toggle = '-';
                $folder = '<i class="far fa-folder-open"></i>';
            }

            $children = count($value['children']);
            $markup .= '<li';

            $markup .= ">\n";
            if ($children > 0) {
                $markup .= '<a class="toggle" href="http://'.Config::get('WEB_ROOT').Config::get('PUBLIC_DIR')."/$url/".$value['id'].'/?page=1';
                if ($toggle === '-') {
                    $markup .= '&amp;collapsed=1';
                }
                $markup .= "\">$toggle</a>";
            } else {
                $markup .= '<span class="toggle">&nbsp;</span>';
            }
            $markup .= $folder;
            if ($current_id === $value['id']) {
                $markup .= '<span class="label current">'.$value['label'].'</span>';
            } else {
                $markup .= '<span class="label"><a href="http://'.Config::get('WEB_ROOT').Config::get('PUBLIC_DIR')."/$url/".$value['id'].'/?page=1">'.$value['label'].'</a></span>';
            }
            if ($children > 0) {
                $markup .= $this->buildMarkup($value['children'], $level, $current_id, $value['id']);
            }
            $markup .= "</li>\n";
        }

        return $markup . "</ul>\n";
    }
}
