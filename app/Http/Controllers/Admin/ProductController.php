<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index()
    {
        if (request()->ajax()) {
            $products = Product::with(['category', 'warehouseStocks'])->get();
            
            return datatables()->of($products)
                ->addColumn('action', function ($product) {
                    $actions = '';
                    if (auth()->user()->can('products.manage')) {
                        $actions .= '<a href="'.route('admin.products.edit', $product).'" class="btn btn-sm btn-warning me-1">
                            <i class="fas fa-edit"></i>
                        </a>';
                        $actions .= '<form action="'.route('admin.products.destroy', $product).'" method="POST" class="d-inline">
                            '.csrf_field().'
                            '.method_field('DELETE').'
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>';
                    }
                    return $actions;
                })
                ->addColumn('status', function ($product) {
                    return '<span class="badge bg-'.($product->is_active ? 'success' : 'danger').'">
                        '.($product->is_active ? 'Active' : 'Inactive').'
                    </span>';
                })
                ->addColumn('total_stock', function ($product) {
                    return $product->warehouseStocks->sum('quantity');
                })
                ->addColumn('category_name', function ($product) {
                    return $product->category ? $product->category->name : 'N/A';
                })
                ->editColumn('cost_price', function ($product) {
                    return '$'.number_format($product->cost_price, 2);
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        
        return view('admin.products.index');
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->pluck('name', 'id');
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sku' => ['required', 'string', 'max:50', Rule::unique('products')],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:20',
            'cost_price' => 'required|numeric|min:0',
            'reorder_level' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ], [
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category is invalid.',
            'sku.required' => 'The SKU field is required.',
            'sku.unique' => 'This SKU is already taken.',
            'name.required' => 'The product name is required.',
            'unit.required' => 'Please select a unit.',
            'cost_price.required' => 'The cost price is required.',
            'cost_price.min' => 'The cost price must be greater than 0.',
            'reorder_level.required' => 'The reorder level is required.',
            'reorder_level.min' => 'The reorder level must be at least 0.',
        ]);
        
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        
        Product::create($validated);
        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->pluck('name', 'id');
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sku' => ['required', 'string', 'max:50', Rule::unique('products')->ignore($product->id)],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'required|string|max:20',
            'cost_price' => 'required|numeric|min:0',
            'reorder_level' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ], [
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'The selected category is invalid.',
            'sku.required' => 'The SKU field is required.',
            'sku.unique' => 'This SKU is already taken.',
            'name.required' => 'The product name is required.',
            'unit.required' => 'Please select a unit.',
            'cost_price.required' => 'The cost price is required.',
            'cost_price.min' => 'The cost price must be greater than 0.',
            'reorder_level.required' => 'The reorder level is required.',
            'reorder_level.min' => 'The reorder level must be at least 0.',
        ]);
        
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;
        
        $product->update($validated);
        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->purchaseOrderItems()->count() > 0) {
            return back()->with('error', 'Cannot delete product with associated purchase orders.');
        }
        $product->delete();
        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }
}