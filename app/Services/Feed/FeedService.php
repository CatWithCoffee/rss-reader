<?php

namespace App\Services\Feed;

use App\Services\Feed\FeedColorService;
use Illuminate\Http\Request;
use Vedmant\FeedReader\Facades\FeedReader;

class FeedService ///обработка ошибок
{
    protected $feed;
    protected $request;

    public static function fromRequest($request)
    {
        return (new self())->loadRequest($request);
    }

    public function loadRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    public function read()
    {
        $f = FeedReader::read($this->request->url);

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

    public function get()
    {
        return $this->feed;
    }
}
