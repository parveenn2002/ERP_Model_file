

<?php $__env->startSection('title', 'Warehouses'); ?>
<?php $__env->startSection('page-title', 'Warehouses'); ?>
<?php $__env->startSection('icon', 'warehouse'); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-list me-2"></i> Warehouse List</span>
        <a href="<?php echo e(route('admin.warehouses.create')); ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Add Warehouse
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warehouse): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($warehouse->code); ?></td>
                            <td><?php echo e($warehouse->name); ?></td>
                            <td><?php echo e($warehouse->location ?? 'N/A'); ?></td>
                            <td>
                                <span class="badge bg-<?php echo e($warehouse->is_active ? 'success' : 'danger'); ?> badge-status">
                                    <?php echo e($warehouse->is_active ? 'Active' : 'Inactive'); ?>

                                </span>
                            </td>
                            <td>
                                <a href="<?php echo e(route('admin.warehouses.edit', $warehouse)); ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="<?php echo e(route('admin.warehouses.destroy', $warehouse)); ?>" method="POST" class="d-inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <?php echo e($warehouses->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ERP_Model_file\resources\views/admin/warehouses/index.blade.php ENDPATH**/ ?>