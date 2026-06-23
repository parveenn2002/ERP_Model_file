

<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('page-title', 'Dashboard'); ?>
<?php $__env->startSection('icon', 'tachometer-alt'); ?>

<?php $__env->startSection('content'); ?>
<div class="row">
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase">Low Stock Products</h6>
                        <h2 class="mb-0"><?php echo e($stats['low_stock_count']); ?></h2>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-3x opacity-50"></i>
                </div>
                <small>Products below reorder level</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase">Pending Orders</h6>
                        <h2 class="mb-0"><?php echo e($stats['pending_orders_count']); ?></h2>
                    </div>
                    <i class="fas fa-clock fa-3x opacity-50"></i>
                </div>
                <small>Submitted/Approved purchase orders</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase">Total Stock Value</h6>
                        <h2 class="mb-0">$<?php echo e(number_format($stats['total_stock_value'], 2)); ?></h2>
                    </div>
                    <i class="fas fa-dollar-sign fa-3x opacity-50"></i>
                </div>
                <small>Total inventory value</small>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-file-invoice me-2"></i> Open Purchase Orders
            </div>
            <div class="card-body">
                <?php if($openOrders->count() > 0): ?>
                    <div class="list-group">
                        <?php $__currentLoopData = $openOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('admin.purchase-orders.show', $order)); ?>" class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo e($order->po_number); ?></strong>
                                        <br>
                                        <small><?php echo e($order->supplier->name); ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?php echo e($order->status->badgeColor()); ?> badge-status">
                                            <?php echo e($order->status->label()); ?>

                                        </span>
                                        <br>
                                        <small>$<?php echo e(number_format($order->grand_total, 2)); ?></small>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center py-3">No open purchase orders</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-warehouse me-2"></i> Warehouse Summary
            </div>
            <div class="card-body">
                <?php $__currentLoopData = $warehouseSummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="mb-3 p-3 border rounded">
                        <h6 class="mb-2"><?php echo e($data['warehouse']->name); ?></h6>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="text-muted small">Products</div>
                                <strong><?php echo e($data['summary']['product_count']); ?></strong>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small">Units</div>
                                <strong><?php echo e($data['summary']['total_units']); ?></strong>
                            </div>
                            <div class="col-4">
                                <div class="text-muted small">Value</div>
                                <strong>$<?php echo e(number_format($data['summary']['stock_value'], 2)); ?></strong>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\ERP_model\resources\views/admin/dashboard/index.blade.php ENDPATH**/ ?>