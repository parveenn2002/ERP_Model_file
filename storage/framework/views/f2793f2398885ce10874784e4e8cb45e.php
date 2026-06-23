<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Inventory ERP'); ?> - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #2c3e50 0%, #1a252f 100%);
            padding-top: 20px;
        }
        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 12px 20px;
            border-radius: 8px;
            margin: 4px 10px;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.1);
            transform: translateX(5px);
        }
        .sidebar .nav-link.active {
            background: #3498db;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
        }
        .sidebar .brand {
            color: white;
            text-align: center;
            padding: 20px 0;
            font-size: 24px;
            font-weight: 700;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        .sidebar .brand i {
            margin-right: 10px;
        }
        .content {
            padding: 20px;
            background: #f8f9fa;
            min-height: 100vh;
        }
        .navbar {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .navbar-brand {
            font-weight: 600;
            color: #2c3e50;
        }
        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        .card {
            border: none;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .card-header {
            background: white;
            border-bottom: 1px solid #eee;
            padding: 18px 24px;
            border-radius: 12px 12px 0 0 !important;
            font-weight: 600;
        }
        .card-body {
            padding: 24px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 8px 20px;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .table {
            margin-bottom: 0;
        }
        .table th {
            background: #f8f9fa;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 20px;
            padding: 8px 20px;
            border: 1px solid #ddd;
        }
        .dataTables_wrapper .dataTables_length select {
            border-radius: 20px;
            padding: 5px 15px;
        }
        .user-dropdown .dropdown-toggle {
            background: none;
            border: none;
            color: #2c3e50;
        }
        .user-dropdown .dropdown-toggle:hover {
            background: #f0f0f0;
            border-radius: 8px;
        }
        .user-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 10px;
        }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0 sidebar">
                <div class="brand">
                    <i class="fas fa-box"></i> ERP
                </div>
                <nav class="nav flex-column">
                    <a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('products.view')): ?>
                    <a href="<?php echo e(route('admin.products.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.products.*') ? 'active' : ''); ?>">
                        <i class="fas fa-box"></i> Products
                    </a>
                    <?php endif; ?>
                    
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('products.manage')): ?>
                    <a href="<?php echo e(route('admin.categories.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.categories.*') ? 'active' : ''); ?>">
                        <i class="fas fa-tags"></i> Categories
                    </a>
                    <?php endif; ?>
                    
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('suppliers.manage')): ?>
                    <a href="<?php echo e(route('admin.suppliers.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.suppliers.*') ? 'active' : ''); ?>">
                        <i class="fas fa-truck"></i> Suppliers
                    </a>
                    <?php endif; ?>
                    
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('warehouses.manage')): ?>
                    <a href="<?php echo e(route('admin.warehouses.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.warehouses.*') ? 'active' : ''); ?>">
                        <i class="fas fa-warehouse"></i> Warehouses
                    </a>
                    <?php endif; ?>
                    
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['purchase_orders.create', 'purchase_orders.approve', 'purchase_orders.review'])): ?>
                    <a href="<?php echo e(route('admin.purchase-orders.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.purchase-orders.*') ? 'active' : ''); ?>">
                        <i class="fas fa-file-invoice"></i> Purchase Orders
                    </a>
                    <?php endif; ?>
                    
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('stock.adjust')): ?>
                    <a href="<?php echo e(route('admin.stock.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.stock.*') ? 'active' : ''); ?>">
                        <i class="fas fa-cubes"></i> Stock Management
                    </a>
                    <?php endif; ?>
                    
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('reports.view')): ?>
                    <a href="#" class="nav-link">
                        <i class="fas fa-chart-bar"></i> Reports
                    </a>
                    <?php endif; ?>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-3">
                <!-- Navbar -->
                <nav class="navbar navbar-expand-lg">
                    <div class="container-fluid">
                        <span class="navbar-brand">
                            <i class="fas fa-<?php echo $__env->yieldContent('icon', 'dashboard'); ?>"></i>
                            <?php echo $__env->yieldContent('page-title', 'Dashboard'); ?>
                        </span>
                        <div class="ms-auto">
                            <div class="dropdown user-dropdown">
                                <button class="dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                                    <div class="user-avatar">
                                        <?php echo e(substr(Auth::user()->name, 0, 1)); ?>

                                    </div>
                                    <div class="text-start">
                                        <div style="font-weight: 600; font-size: 14px;"><?php echo e(Auth::user()->name); ?></div>
                                        <div style="font-size: 12px; color: #6c757d;">
                                            <?php $__currentLoopData = Auth::user()->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php echo e($role->name); ?>

                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </div>
                                    </div>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><span class="dropdown-item-text">
                                        <i class="fas fa-user"></i> <?php echo e(Auth::user()->name); ?>

                                    </span></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="dropdown-item">
                                                <i class="fas fa-sign-out-alt"></i> Logout
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
                
                <!-- Content -->
                <div class="content">
                    <?php if(session('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo e(session('success')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if(session('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo e(session('error')); ?>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php echo $__env->yieldContent('content'); ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH C:\xampp\htdocs\ERP_Model_file\resources\views/layouts/admin.blade.php ENDPATH**/ ?>