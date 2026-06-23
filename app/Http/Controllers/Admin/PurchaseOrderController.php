<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\Product;
use App\Services\PurchaseOrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PurchaseOrderController extends Controller
{
    protected $purchaseOrderService;

    public function __construct(PurchaseOrderService $purchaseOrderService)
    {
        $this->purchaseOrderService = $purchaseOrderService;
        // Remove middleware from constructor - use routes instead
    }

    public function index()
    {
        if (request()->ajax()) {
            $purchaseOrders = PurchaseOrder::with(['supplier', 'warehouse'])->get();
            
            return datatables()->of($purchaseOrders)
                ->addColumn('action', function ($po) {
                    $actions = '';
                    if (auth()->user()->can('purchase_orders.create')) {
                        $actions .= '<a href="'.route('admin.purchase-orders.show', $po).'" class="btn btn-sm btn-info me-1">
                            <i class="fas fa-eye"></i>
                        </a>';
                        if ($po->isEditable()) {
                            $actions .= '<a href="'.route('admin.purchase-orders.edit', $po).'" class="btn btn-sm btn-warning me-1">
                                <i class="fas fa-edit"></i>
                            </a>';
                        }
                    }
                    return $actions;
                })
                ->addColumn('status', function ($po) {
                    return '<span class="badge bg-'.$po->status->badgeColor().'">
                        '.$po->status->label().'
                    </span>';
                })
                ->editColumn('grand_total', function ($po) {
                    return '$'.number_format($po->grand_total, 2);
                })
                ->editColumn('order_date', function ($po) {
                    return $po->order_date->format('Y-m-d');
                })
                ->addColumn('supplier_name', function ($po) {
                    return $po->supplier->name;
                })
                ->addColumn('warehouse_name', function ($po) {
                    return $po->warehouse->name;
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        
        return view('admin.purchase-orders.index');
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->pluck('name', 'id');
        $warehouses = Warehouse::where('is_active', true)->pluck('name', 'id');
        $products = Product::where('is_active', true)->with('category')->get();
        return view('admin.purchase-orders.create', compact('suppliers', 'warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            $po = $this->purchaseOrderService->create($validated);
            return redirect()->route('admin.purchase-orders.index')
                ->with('success', 'Purchase order created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'warehouse', 'items.product', 'creator', 'approver', 'receiver']);
        $activities = activity()->performedOn($purchaseOrder)->get();
        return view('admin.purchase-orders.show', compact('purchaseOrder', 'activities'));
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        if (!$purchaseOrder->isEditable()) {
            return back()->with('error', 'This purchase order cannot be edited.');
        }

        $suppliers = Supplier::where('is_active', true)->pluck('name', 'id');
        $warehouses = Warehouse::where('is_active', true)->pluck('name', 'id');
        $products = Product::where('is_active', true)->with('category')->get();
        return view('admin.purchase-orders.edit', compact('purchaseOrder', 'suppliers', 'warehouses', 'products'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after:order_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        try {
            $po = $this->purchaseOrderService->update($purchaseOrder, $validated);
            return redirect()->route('admin.purchase-orders.index')
                ->with('success', 'Purchase order updated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function submit(PurchaseOrder $purchaseOrder)
    {
        try {
            $this->purchaseOrderService->submit($purchaseOrder);
            return redirect()->route('admin.purchase-orders.index')
                ->with('success', 'Purchase order submitted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        try {
            $this->purchaseOrderService->approve($purchaseOrder);
            return redirect()->route('admin.purchase-orders.index')
                ->with('success', 'Purchase order approved successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function receive(PurchaseOrder $purchaseOrder)
    {
        try {
            $this->purchaseOrderService->receive($purchaseOrder);
            return redirect()->route('admin.purchase-orders.index')
                ->with('success', 'Purchase order received successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(PurchaseOrder $purchaseOrder)
    {
        try {
            $this->purchaseOrderService->cancel($purchaseOrder);
            return redirect()->route('admin.purchase-orders.index')
                ->with('success', 'Purchase order cancelled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}