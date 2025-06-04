<?php

namespace App\Services\Feed;

use App\Services\Feed\FeedColorService;
use Exception;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Http\Request;
use Vedmant\FeedReader\Facades\FeedReader;

class FeedService ///обработка ошибок
{
    protected $feed;
    protected $request;
    protected $url;

    public static function fromUrl($url)
    {
        return (new self())->loadUrl($url);
    }

    public function loadUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public static function fromRequest($request)
    {
        return (new self())->loadRequest($request);
    }

    public function loadRequest(Request $request)
    {
        $this->url = $request->url;
        return $this;
    }

    public function read()
    {
        $f = FeedReader::read($this->url);

        $this->feed = [
            'title' => $f->get_title(),
            'url' => $f->feed_url,
            'site_url' => $f->get_link(),
            'description' => $f->get_description(),
            'language' => $f->get_language(),
            'favicon' => $f->get_favicon(),
            'image' => $f->get_image_url(),
            'color' => FeedColorService::getColor($f->get_favicon(), $f->get_image_url()),
        ];

        return $this;
    }

    public function order()
    {
        $f = FeedReader::read($this->url);
        if($f->error){
            throw new Exception($f->error);
        }
        $this->feed = [
            'user_id' => Auth()->user()->id,
            'title' => $f->get_title(),
            'url' => $f->feed_url,
            'description' => $f->get_description(),
            'favicon' => $f->get_favicon(),
            'color' => FeedColorService::getColor($f->get_favicon(), $f->get_image_url()),
        ];

        return $this;
    }

    public function get()
    {
        return $this->feed;
    }
}
