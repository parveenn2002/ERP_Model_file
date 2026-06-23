@extends('layouts.admin')

@section('title', 'Purchase Order Details')
@section('page-title', 'Purchase Order Details')
@section('icon', 'file-invoice')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <span><i class="fas fa-file-invoice me-2"></i> PO: {{ $purchaseOrder->po_number }}</span>
            <span class="badge bg-{{ $purchaseOrder->getStatusBadgeColor() }} ms-2 fs-6">
                {{ $purchaseOrder->getStatusLabel() }}
            </span>
        </div>
        <div>
            @if($purchaseOrder->canBeSubmitted() && auth()->user()->can('purchase_orders.submit'))
                <form action="{{ route('admin.purchase-orders.submit', $purchaseOrder) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Submit this PO for approval?')">
                        <i class="fas fa-paper-plane me-1"></i> Submit
                    </button>
                </form>
            @endif
            
            @if($purchaseOrder->canBeApproved() && auth()->user()->can('purchase_orders.approve'))
                <form action="{{ route('admin.purchase-orders.approve', $purchaseOrder) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('Approve this PO?')">
                        <i class="fas fa-check me-1"></i> Approve
                    </button>
                </form>
            @endif
            
            @if($purchaseOrder->canBeReceived() && auth()->user()->can('purchase_orders.review'))
                <form action="{{ route('admin.purchase-orders.receive', $purchaseOrder) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('Receive this PO and update stock?')">
                        <i class="fas fa-arrow-down me-1"></i> Receive
                    </button>
                </form>
            @endif
            
            @if($purchaseOrder->canBeCancelled() && auth()->user()->can('purchase_orders.cancel'))
                <form action="{{ route('admin.purchase-orders.cancel', $purchaseOrder) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Cancel this PO?')">
                        <i class="fas fa-times me-1"></i> Cancel
                    </button>
                </form>
            @endif
            
            @if($purchaseOrder->isEditable() && auth()->user()->can('purchase_orders.create'))
                <a href="{{ route('admin.purchase-orders.edit', $purchaseOrder) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            @endif
        </div>
    </div>
    <div class="card-body">
        <!-- Status Timeline -->
        <div class="mb-4">
            <h6><i class="fas fa-tasks me-2"></i> Workflow Status</h6>
            <div class="row">
                <div class="col-12">
                    <div class="progress" style="height: 35px;">
                        @php
                            $statuses = ['draft', 'submitted', 'approved', 'received'];
                            $currentStatus = $purchaseOrder->status->value;
                            $progress = 0;
                            
                            if ($currentStatus === 'cancelled') {
                                $progress = 100;
                                $barClass = 'bg-danger';
                            } else {
                                $progress = (array_search($currentStatus, $statuses) / (count($statuses) - 1)) * 100;
                                $barClass = $currentStatus === 'received' ? 'bg-success' : 
                                           ($currentStatus === 'approved' ? 'bg-info' : 
                                           ($currentStatus === 'submitted' ? 'bg-warning' : 'bg-secondary'));
                            }
                        @endphp
                        <div class="progress-bar {{ $barClass }} d-flex align-items-center justify-content-center" 
                             role="progressbar" 
                             style="width: {{ $progress }}%;" 
                             aria-valuenow="{{ $progress }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            @if($currentStatus === 'cancelled')
                                <i class="fas fa-times-circle me-1"></i> Cancelled
                            @else
                                {{ $purchaseOrder->getStatusLabel() }}
                            @endif
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <small>Draft</small>
                        <small>Submitted</small>
                        <small>Approved</small>
                        <small>Received</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- PO Details -->
        <div class="row">
            <div class="col-md-6">
                <h6><i class="fas fa-truck me-2"></i> Supplier Information</h6>
                <p><strong>Name:</strong> {{ $purchaseOrder->supplier->name }}</p>
                <p><strong>Email:</strong> {{ $purchaseOrder->supplier->email }}</p>
                <p><strong>Phone:</strong> {{ $purchaseOrder->supplier->phone }}</p>
                <p><strong>Address:</strong> {{ $purchaseOrder->supplier->address ?? 'N/A' }}</p>
            </div>
            <div class="col-md-6">
                <h6><i class="fas fa-info-circle me-2"></i> Order Information</h6>
                <p><strong>Order Date:</strong> {{ $purchaseOrder->order_date->format('Y-m-d') }}</p>
                <p><strong>Expected Date:</strong> {{ $purchaseOrder->expected_date ? $purchaseOrder->expected_date->format('Y-m-d') : 'N/A' }}</p>
                <p><strong>Warehouse:</strong> {{ $purchaseOrder->warehouse->name }}</p>
                <p><strong>Created By:</strong> {{ $purchaseOrder->creator ? $purchaseOrder->creator->name : 'N/A' }}</p>
                @if($purchaseOrder->approved_by)
                    <p><strong>Approved By:</strong> {{ $purchaseOrder->approver ? $purchaseOrder->approver->name : 'N/A' }}</p>
                    <p><strong>Approved At:</strong> {{ $purchaseOrder->approved_at ? $purchaseOrder->approved_at->format('Y-m-d H:i') : 'N/A' }}</p>
                @endif
                @if($purchaseOrder->received_by)
                    <p><strong>Received By:</strong> {{ $purchaseOrder->receiver ? $purchaseOrder->receiver->name : 'N/A' }}</p>
                    <p><strong>Received At:</strong> {{ $purchaseOrder->received_at ? $purchaseOrder->received_at->format('Y-m-d H:i') : 'N/A' }}</p>
                @endif
            </div>
        </div>
        
        <!-- Order Items -->
        <h6 class="mt-4"><i class="fas fa-list me-2"></i> Order Items</h6>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Line Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseOrder->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ $item->product->sku }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>${{ number_format($item->unit_price, 2) }}</td>
                            <td>${{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-end"><strong>Subtotal:</strong></td>
                        <td>${{ number_format($purchaseOrder->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-end"><strong>Tax (5%):</strong></td>
                        <td>${{ number_format($purchaseOrder->tax_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="5" class="text-end"><strong>Grand Total:</strong></td>
                        <td><strong>${{ number_format($purchaseOrder->grand_total, 2) }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <!-- Notes -->
        @if($purchaseOrder->notes)
            <div class="mt-3">
                <h6><i class="fas fa-sticky-note me-2"></i> Notes</h6>
                <p>{{ $purchaseOrder->notes }}</p>
            </div>
        @endif
        
        <!-- Activity Log -->
        <div class="mt-4">
            <h6><i class="fas fa-history me-2"></i> Activity Log</h6>
            @if(isset($activities) && $activities && $activities->count() > 0)
                <div class="list-group">
                    @foreach($activities as $activity)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-circle text-{{ str_contains($activity->description, 'cancelled') ? 'danger' : 'success' }} me-2" style="font-size: 8px;"></i>
                                    {{ $activity->description }}
                                </div>
                                <small class="text-muted">{{ $activity->created_at->format('Y-m-d H:i:s') }}</small>
                            </div>
                            <small class="text-muted">By: {{ $activity->causer ? $activity->causer->name : 'System' }}</small>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted">No activity logs available.</p>
            @endif
        </div>
    </div>
</div>
@endsection