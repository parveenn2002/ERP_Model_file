<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Enums\PurchaseOrderStatus;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'po_number', 'supplier_id', 'warehouse_id', 'status',
        'order_date', 'expected_date', 'subtotal', 'tax_amount',
        'grand_total', 'notes', 'created_by', 'approved_by',
        'received_by', 'approved_at', 'received_at'
    ];

    protected $casts = [
        'status' => PurchaseOrderStatus::class,
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'order_date' => 'date',
        'expected_date' => 'date',
        'approved_at' => 'datetime',
        'received_at' => 'datetime'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [
            PurchaseOrderStatus::SUBMITTED,
            PurchaseOrderStatus::APPROVED
        ]);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', PurchaseOrderStatus::DRAFT);
    }

    public function isEditable()
    {
        return $this->status === PurchaseOrderStatus::DRAFT;
    }

    public function canBeSubmitted()
    {
        return $this->status === PurchaseOrderStatus::DRAFT;
    }

    public function canBeApproved()
    {
        return $this->status === PurchaseOrderStatus::SUBMITTED;
    }

    public function canBeReceived()
    {
        return $this->status === PurchaseOrderStatus::APPROVED;
    }

    public function canBeCancelled()
    {
        return !in_array($this->status, [
            PurchaseOrderStatus::RECEIVED,
            PurchaseOrderStatus::CANCELLED
        ]);
    }
}