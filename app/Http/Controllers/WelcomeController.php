<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WelcomeController extends Controller
{
    public function index()
    {
        try {
            // Fetch all products with their product types
            $products = DB::table('products as p')
                ->leftJoin('product_type as pt', 'p.product_type_id', '=', 'pt.id')
                ->select(
                    'p.p_id',
                    'p.name',
                    'p.description',
                    'p.monthly_price',
                    'pt.name as type_name'
                )
                ->orderBy('p.monthly_price', 'asc')
                ->get();
            
            // Return exactly what's in the database
            // No transformations, no fake data
            \Log::info('Loaded products from database', ['count' => $products->count()]);
            
        } catch (\Exception $e) {
            \Log::error('Error loading products: ' . $e->getMessage());
            $products = collect([]); // Return empty collection if error
        }
        
        return view('welcome', compact('products'));
    }
}