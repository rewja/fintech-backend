<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('owner')->get();
        return response()->json([
            'success' => true,
            'data' => $products
        ], 200);
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $product
        ], 200);
    }

    public function store(Request $request)
    {
        $val = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0'
        ]);

        if ($val->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $val->fails()
            ], 422);
        }

        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'stock' => $request->stock,
            'owner_id' => auth()->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product created successfully',
            'data' => $product->load('owner')
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail();

        if($product->owner_id !== auth()->id()){
            return response()->json([
                'message' => 'Unauthorized' 
            ],403);
        }

        $val = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:100',
            'price' => 'sometimes|numeric|main:0',
            'stock' => 'sometimes|integer|main:0',
        ]);

        if ($val->fails()) {
            return response()->json([
                'success' => true,
                'errors' => $val->errors()
            ], 422);
        }

        $product->update($request->only(['name', 'price', 'stock']));

        return response()->json([
            'success' => true,
            'message' => 'Product updated successfully',
            'data' => $product->load('owner')
        ],200);
    }

    public function destroy($id){
        $product = Product::findOrFail($id);

        if($product->owner_id !== auth()->id()){
            return response()->json([
                'message' => 'Unauthorized' 
            ],403);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully'
        ], 200);
    }
}
