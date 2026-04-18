<?php

declare(strict_types=1);

namespace Empathy\ELib\Store;

use Empathy\MVC\Config;

class ImageUpload
{
    public mixed $error;
    public mixed $target;
    public mixed $target_dir;
    public mixed $file;
    public mixed $deriv;
    public mixed $orig;
    public mixed $origX;
    public mixed $origY;
    public mixed $quality;

    public function __construct(public mixed $gallery, mixed $upload, mixed $deriv)
    {
        if ($this->gallery !== '') {
            //$this->target_dir = DOC_ROOT."/public_html/img/$this->gallery/";
            $this->target_dir = Config::get('DOC_ROOT').'/public_html/uploads/';
        } else {
            $this->target_dir = Config::get('DOC_ROOT').'/public_html/uploads/';
        }

        $this->deriv = count($deriv) < 1 ? [['l_', 800, 600],
                             ['tn_', 200, 200],
                             ['mid_', 500, 500]] : $deriv;
        $this->quality = 85;
        $this->error = '';

        if ($upload) {
            $this->upload();
            if ($this->error === '') {
                if (!$this->create()) {
                    foreach ($this->deriv as $item) {
                        $this->makeDerived($item[0], $item[1], $item[2]);
                    }
                }
                if ($this->orig instanceof \GdImage) {
                    imagedestroy($this->orig);
                }
            }
        }
    }

    /*
      public function create(): mixed {
      $this->orig = imagecreatefromjpeg($this->target);
      $this->origX = imagesx($this->orig);
      $this->origY = imagesy($this->orig);
      }
    */

    // new
    public function create(): bool
    {
        $error = false;
        $gd = imagecreatefromjpeg($this->target);
        if (!$gd instanceof \GdImage) {
            $this->error = 'Could not load JPEG image.';
            return true;
        }
        $this->orig = $gd;
        $this->origX = imagesx($this->orig);
        $this->origY = imagesy($this->orig);

        /*
        if($this->origX != $this->origY
           || $this->origX < 450)
        {
            $this->error = 'Image or not square or is too small. Minimum 450 x 450 pixels.';
            $this->remove(array($this->file));
            $error = true;
        }
        */

        return $error;
    }

    public function makeDerived(mixed $prefix, mixed $max_width, mixed $max_height): void
    {
        $quality = $max_width < 300 || $max_height < 300 ? 100 : $this->quality;
        if ($this->origX > $max_width || $this->origY > $max_height) {
            $factorX = $max_width / $this->origX;
            $factorY = $max_height / $this->origY;
            $factor = $factorX < $factorY ? $factorX : $factorY;
        } else {
            $factor = 1;
        }

        $newX = max(1, (int) round($this->origX * $factor));
        $newY = max(1, (int) round($this->origY * $factor));

        $img = imagecreatetruecolor($newX, $newY);
        if (!$img instanceof \GdImage || !$this->orig instanceof \GdImage) {
            return;
        }
        imagecopyresampled($img, $this->orig, 0, 0, 0, 0, $newX, $newY, $this->origX, $this->origY);
        $newTarget = $this->target_dir.$prefix.$this->file;
        imagejpeg($img, $newTarget, $quality);
        imagedestroy($img);
    }

    public function resize(mixed $files): void
    {
        foreach ($files as $file) {
            $this->file = $file;
            $this->target = $this->target_dir.$file;
            if ($this->file !== '' && file_exists($this->target)) {
                $this->create();
                foreach ($this->deriv as $item) {
                    $this->makeDerived($item[0], $item[1], $item[2]);
                }
                if ($this->orig instanceof \GdImage) {
                    imagedestroy($this->orig);
                }
            }
        }
    }

    public function remove(mixed $files): mixed
    {
        $success_arr = [];
        $all_files = [];

        foreach ($files as $file) {
            if ($file !== '') {
                $matches = glob($this->target_dir.'*'.$file);
                if (is_array($matches)) {
                    $all_files = array_merge($all_files, $matches);
                }
            }
        }
        foreach ($all_files as $file) {
            $success_arr[] = @unlink($file);
        }

        return !in_array(false, $success_arr, true);
    }

    /** @phpstan-impure */
    public function upload(): void
    {
        if ($_FILES['file']['name'] === '') {
            $this->error .= 'Problem uploading file. Empty file?';
        } else {
            $name_array = explode('.', (string) $_FILES['file']['name']);
            $size = count($name_array);
            $ext = $name_array[$size - 1];

            /* check for jpeg */
            $imgInfo = getimagesize($_FILES['file']['tmp_name']);

            if (!preg_match('/jpg|jpeg/', $ext) || !is_array($imgInfo) || $imgInfo['mime'] !== 'image/jpeg') {
                $this->error .= 'Invalid file format.';
            } else {
                $name = '';
                if (count($name_array) > 2) {
                    for ($i = 0; $i < $size - 1; $i++) {
                        $name .= $name_array[$i];
                        if ($i + 1 !== $size - 1) {
                            $name .= '.';
                        }
                    }
                } else {
                    $name = $name_array[0];
                }

                $this->target = $this->target_dir.$name.'.'.$ext;
                // deal with duplicates
                $i = 1;
                while (file_exists($this->target)) {
                    $this->target = $this->target_dir.$name.'_'.$i++.'.'.$ext;
                }
                $this->file = substr($this->target, strlen((string) $this->target_dir));
                if (!@move_uploaded_file($_FILES['file']['tmp_name'], $this->target)) {
                    $this->error .= 'Internal error';
                }
            }
        }
    }

}
