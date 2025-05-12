<?php

namespace App\Http\Controllers;

use App\Models\Source;
use Illuminate\Http\Request;
use Vedmant\FeedReader\Facades\FeedReader;
class SourceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sources = Source::all();
        return view('admin.sources')->with('sources', $sources);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        ///добавить выбор категории или категорий источника
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $f = FeedReader::read($request->name);

        $title = $f->get_title();
        $link = $f->get_link();
        $description = $f->get_description();
        $image = $f->get_image_url();
        $favicon = $f->get_favicon();
        $language = $f->get_language();

        // dd($f);

        // $item = $f->get_items()[6];

        // echo 'title: ' . $item->get_title() . '<br>';
        // echo 'content: ' . $item->get_content() . '<br>';
        // echo 'link: ' . $item->get_link() . '<br>';

        // // Категории
        // $categories = $item->get_categories();
        // echo 'categories: ';
        // foreach ($categories as $category) {
        //     echo $category->get_term() . ', ';
        // }

        // // Изображение (enclosure)
        // if ($enclosure = $item->get_enclosure()) {
        //     echo '<br>image: ' . $enclosure->get_link();
        // }



        try{
            Source::create([
                'title' => $title,
                'link' => $link,
                'description' => $description,
                'image' => $image,
                'favicon' => $favicon,
                'language' => $language
            ]);

            return redirect(route('admin.sources'))->with('success', 'Source added');

        }
        catch(\Exception $e){
            return redirect(route('admin.sources'))->withErrors(['name' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Source $source)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Source $source)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Source $source)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Source $source)
    {
        //
    }
}
