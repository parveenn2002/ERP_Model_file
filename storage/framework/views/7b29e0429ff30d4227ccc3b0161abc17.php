

<?php $__env->startSection('title', 'Purchase Orders'); ?>
<?php $__env->startSection('page-title', 'Purchase Orders'); ?>
<?php $__env->startSection('icon', 'file-invoice'); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-list me-2"></i> Purchase Order List</span>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('purchase_orders.create')): ?>
        <a href="<?php echo e(route('admin.purchase-orders.create')); ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Create PO
        </a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <table class="table table-bordered" id="purchase-orders-table">
            <thead>
                <tr>
                    <th>PO Number</th>
                    <th>Supplier</th>
                    <th>Warehouse</th>
                    <th>Status</th>
                    <th>Grand Total</th>
                    <th>Order Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(function() {
    $('#purchase-orders-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "<?php echo e(route('admin.purchase-orders.index')); ?>",
        columns: [
            { data: 'po_number', name: 'po_number' },
            { data: 'supplier_name', name: 'supplier.name' },
            { data: 'warehouse_name', name: 'warehouse.name' },
            { data: 'status', name: 'status' },
            { data: 'grand_total', name: 'grand_total' },
            { data: 'order_date', name: 'order_date' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        pageLength: 10,
        responsive: true
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ERP_Model_file\resources\views/admin/purchase-orders/index.blade.php ENDPATH**/ ?>