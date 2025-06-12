<?php

namespace App\Services\Feed;

use League\ColorExtractor\Palette;
use League\ColorExtractor\ColorExtractor;
use League\ColorExtractor\Color;

class FeedColorService
{

    protected $color;
    protected $image;
    protected $favicon;

    public static function fromFavicon(string $favicon)
    {
        return (new self())->loadFavicon($favicon);
    }
    
    public static function fromImage(string $image)
    {
        return (new self())->loadImage($image);
    }

    public static function getColor($favicon, $image)
    {
        $instance = new self();

        try {
            if ($favicon !== null) {
                $color = $instance->getFromFavicon($favicon);
                if ($color !== false)
                    return $color;
            }

            if ($image !== null) {
                $color = $instance->getFromImage($image);
                if ($color !== false)
                    return $color;
            }
        } catch (\Exception $e) {
            /// Log error if needed
        }

        return '#000000'; // Default color
    }

    public function loadImage(string $image)
    {
        $this->image = $image;
        $this->color = $this->getFromImage($image);
        return $this;
    }

    public function loadFavicon(string $favicon)
    {
        $this->favicon = $favicon;
        $this->color = $this->getFromFavicon($favicon);
        return $this;
    }

    public function get()
    {
        return $this->color;
    }

    protected function getFromImage(string $image)
    {
        if (empty($image)) {
            return false;
        } else {
            $color = Palette::fromUrl($image);
            $extractor = new ColorExtractor($color);
            $topColor = $extractor->extract(1)[0];
            $color = Color::fromIntToHex($topColor);
            return $color;
        }
    }

    protected function getFromFavicon(string $favicon)
    {
        if (empty($favicon)) {
            return false;
        } else {
            $imageData = $this->parseFavicon($favicon);
            $tempFile = tempnam(sys_get_temp_dir(), 'favicon_') . '.png';

            $im = @imagecreatefromstring($imageData);
            if ($im !== false) {
                imagepng($im, $tempFile); 
                $color = Palette::fromFilename($tempFile);
                unlink($tempFile);
                $extractor = new ColorExtractor($color);
                $topColor = $extractor->extract(1)[0];
                $color = Color::fromIntToHex($topColor);
                return $color;
            } else {
                return false;
            }
        }
    }

    protected function parseFavicon(string $url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Разрешаем редиректы
        $imageData = curl_exec($ch);
        curl_close($ch);

        return $imageData;
    }
}
