<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\ProductGallery;

class ImportProduct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:product';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $url = "https://dummyjson.com/products?limit=100";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$url);
        $result=curl_exec($ch);
        curl_close($ch);
        $product = json_decode($result, true)['products'];
        ProductCategory::truncate();
        Product::truncate();
        ProductGallery::truncate();

        foreach($product as $key=>$item){
            $cat = ProductCategory::where('name',$item['category'])->first();
            if(!$cat){
                $cat = new ProductCategory;
                $cat->name = $item['category'];
                $cat->save();
            }
            $product = new Product;
            $product->title = $item['title'];
            $product->description = $item['description'];
            $product->price = $item['price'];
            $product->discountPercentage = $item['discountPercentage'];
            $product->rating = $item['rating'];
            $product->brand = $item['brand'];
            $product->stock = $item['stock'];
            $product->categories_id = $cat->id;
            $product->thumbnail = $item['thumbnail'];
            $product->save();

            foreach($item['images'] as $image){
                $img = new ProductGallery;
                $img->products_id = $product->id;
                $img->url = $image;
                $img->save();
            }
        }

        return 0;
    }
}
