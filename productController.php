<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class productController extends Controller
{
    public function index(Request $request)
    {
        $product_link = $request->product;
        $product = \App\Models\Product::where('link', $product_link)->firstOrFail();
        $product_specification = $product->product_specification->first()->specifications;
        if($product_specification){
            $product_specification = explode(';',$product_specification);
            array_pop($product_specification);
        }
    
        return view('product', [
            'product' => $product,
            'product_specification' => $product_specification
        ]);
    }
}
