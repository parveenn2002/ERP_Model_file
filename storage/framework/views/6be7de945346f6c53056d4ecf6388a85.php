

<?php $__env->startSection('title', 'Stock Management'); ?>
<?php $__env->startSection('page-title', 'Stock Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Stock Levels</h5>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('stock.adjust')): ?>
        <a href="<?php echo e(route('admin.stock.adjust')); ?>" class="btn btn-primary">
            <i class="fas fa-edit"></i> Adjust Stock
        </a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <table class="table table-bordered" id="stock-table">
            <thead>
                <tr>
                    <th>Warehouse</th>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Quantity</th>
                    <th>Reorder Level</th>
                    <th>Status</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(function() {
    $('#stock-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "<?php echo e(route('admin.stock.index')); ?>",
        columns: [
            { data: 'warehouse_name', name: 'warehouse.name' },
            { data: 'product_name', name: 'product.name' },
            { data: 'product_sku', name: 'product.sku' },
            { data: 'quantity', name: 'quantity' },
            { data: 'reorder_level', name: 'product.reorder_level' },
            { data: 'status', name: 'status' }
        ]
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ERP_Model_file\resources\views/admin/stock/index.blade.php ENDPATH**/ ?>