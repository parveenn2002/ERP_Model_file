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

    // Relationships
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

    // Scopes
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

    // Status Check Methods
    public function isDraft(): bool
    {
        return $this->status === PurchaseOrderStatus::DRAFT;
    }

    public function isSubmitted(): bool
    {
        return $this->status === PurchaseOrderStatus::SUBMITTED;
    }

    public function isApproved(): bool
    {
        return $this->status === PurchaseOrderStatus::APPROVED;
    }

    public function isReceived(): bool
    {
        return $this->status === PurchaseOrderStatus::RECEIVED;
    }

    public function isCancelled(): bool
    {
        return $this->status === PurchaseOrderStatus::CANCELLED;
    }

    // Workflow Action Methods
    public function isEditable(): bool
    {
        return $this->isDraft();
    }

    public function canBeEdited(): bool
    {
        return $this->isDraft();
    }

    public function canBeSubmitted(): bool
    {
        return $this->isDraft();
    }

    public function canBeApproved(): bool
    {
        return $this->isSubmitted();
    }

    public function canBeReceived(): bool
    {
        return $this->isApproved();
    }

    public function canBeCancelled(): bool
    {
        return !$this->isReceived() && !$this->isCancelled();
    }

    // Transition Methods
    public function markAsSubmitted(): void
    {
        $this->status = PurchaseOrderStatus::SUBMITTED;
        $this->save();
    }

    public function markAsApproved(int $userId): void
    {
        $this->status = PurchaseOrderStatus::APPROVED;
        $this->approved_by = $userId;
        $this->approved_at = now();
        $this->save();
    }

    public function markAsReceived(int $userId): void
    {
        $this->status = PurchaseOrderStatus::RECEIVED;
        $this->received_by = $userId;
        $this->received_at = now();
        $this->save();
    }

    public function markAsCancelled(): void
    {
        $this->status = PurchaseOrderStatus::CANCELLED;
        $this->save();
    }

    // Helper methods
    public function getStatusLabel(): string
    {
        return $this->status->label();
    }

    public function getStatusBadgeColor(): string
    {
        return $this->status->badgeColor();
    }
}