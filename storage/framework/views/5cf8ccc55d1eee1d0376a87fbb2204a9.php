

<?php $__env->startSection('title', 'Suppliers'); ?>
<?php $__env->startSection('page-title', 'Suppliers'); ?>
<?php $__env->startSection('icon', 'truck'); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-list me-2"></i> Supplier List</span>
        <a href="<?php echo e(route('admin.suppliers.create')); ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Add Supplier
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($supplier->code); ?></td>
                            <td><?php echo e($supplier->name); ?></td>
                            <td><?php echo e($supplier->email); ?></td>
                            <td><?php echo e($supplier->phone); ?></td>
                            <td>
                                <span class="badge bg-<?php echo e($supplier->is_active ? 'success' : 'danger'); ?> badge-status">
                                    <?php echo e($supplier->is_active ? 'Active' : 'Inactive'); ?>

                                </span>
                            </td>
                            <td>
                                <a href="<?php echo e(route('admin.suppliers.edit', $supplier)); ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="<?php echo e(route('admin.suppliers.destroy', $supplier)); ?>" method="POST" class="d-inline">
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
        <?php echo e($suppliers->links()); ?>

    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ERP_Model_file\resources\views/admin/suppliers/index.blade.php ENDPATH**/ ?>