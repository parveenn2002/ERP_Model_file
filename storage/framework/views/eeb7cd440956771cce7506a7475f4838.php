

<?php $__env->startSection('title', 'Products'); ?>
<?php $__env->startSection('page-title', 'Products'); ?>
<?php $__env->startSection('icon', 'box'); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-list me-2"></i> Product List</span>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('products.manage')): ?>
        <a href="<?php echo e(route('admin.products.create')); ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Add Product
        </a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <table class="table table-bordered" id="products-table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Cost Price</th>
                    <th>Total Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    $('#products-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "<?php echo e(route('admin.products.index')); ?>",
        columns: [
            { data: 'sku', name: 'sku' },
            { data: 'name', name: 'name' },
            { data: 'category_name', name: 'category.name' },
            { data: 'cost_price', name: 'cost_price' },
            { data: 'total_stock', name: 'total_stock' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        pageLength: 10,
        responsive: true,
        order: [[1, 'asc']]
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ERP_Model_file\resources\views/admin/products/index.blade.php ENDPATH**/ ?>