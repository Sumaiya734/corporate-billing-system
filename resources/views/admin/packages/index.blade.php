@extends('layouts.admin')

@section('title', 'Product Management - Admin Dashboard')

@section('content')
<div class="container-fluid p-4">
    <!-- Toast container -->
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1080;">
        <div id="toastContainer"></div>
    </div>

    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 page-title">
                <i class="fas fa-cube me-2 text-primary"></i>Product Management
            </h2>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary" id="exportBtn">
                <i class="fas fa-download me-1"></i>Export
            </button>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPackageModal">
                <i class="fas fa-plus me-1"></i>Create Product
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-2">Total Products</h6>
                            <h3 class="mb-0">{{ $stats['total_packages'] ?? 0 }}</h3>
                        </div>
                        <div class="avatar-sm bg-primary rounded-circle text-white d-flex align-items-center justify-content-center">
                            <i class="fas fa-cubes"></i>
                        </div>
                    </div>
                    <p class="text-success mt-3 mb-0">
                        <i class="fas fa-check-circle me-1"></i> All active product
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-2">Regular Product</h6>
                            <h3 class="mb-0">{{ $stats['regular_packages'] ?? 0 }}</h3>
                        </div>
                        <div class="avatar-sm bg-success rounded-circle text-white d-flex align-items-center justify-content-center">
                            <i class="fas fa-bolt"></i>
                        </div>
                    </div>
                    <p class="text-muted mt-3 mb-0">
                        From ৳{{ number_format($stats['price_range_regular']['min'] ?? 0) }} to ৳{{ number_format($stats['price_range_regular']['max'] ?? 0) }}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-2">Special Product</h6>
                            <h3 class="mb-0">{{ $stats['special_packages'] ?? 0 }}</h3>
                        </div>
                        <div class="avatar-sm bg-warning rounded-circle text-white d-flex align-items-center justify-content-center">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    <p class="text-muted mt-3 mb-0">
                        From ৳{{ number_format($stats['price_range_special']['min'] ?? 0) }} to ৳{{ number_format($stats['price_range_special']['max'] ?? 0) }}
                    </p>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-2">Active Customers</h6>
                            <h3 class="mb-0">{{ $stats['active_customers'] ?? 'N/A' }}</h3>
                        </div>
                        <div class="avatar-sm bg-info rounded-circle text-white d-flex align-items-center justify-content-center">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <p class="text-success mt-3 mb-0">
                        <i class="fas fa-arrow-up me-1"></i> 12 new this month
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Packages Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>All Products
            </h5>

            <div class="d-flex gap-2 align-items-center">
                <input type="text" class="form-control form-control-sm search-box" placeholder="Search packages..." style="min-width: 200px;">
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-secondary filter-btn active" data-type="all">All</button>
                    <button class="btn btn-sm btn-outline-secondary filter-btn" data-type="regular">Regular</button>
                    <button class="btn btn-sm btn-outline-secondary filter-btn" data-type="special">Special</button>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">#</th>
                            <th>Product Name</th>
                            <th>Product Type</th>
                            <th>Description</th>
                            <th>Features</th>
                            <th width="120" class="text-end">Price</th>
                            <th width="120" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($packages as $package)
                        <tr data-type="{{ $package->package_type }}" id="package-row-{{ $package->p_id }}">
                            <td class="fw-bold">{{ $package->p_id }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="package-icon me-3 {{ $package->package_type === 'regular' ? 'bg-primary' : 'bg-warning' }}">
                                        <i class="fas {{ $package->package_type === 'regular' ? 'fa-wifi' : 'fa-star' }}"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">{{ $package->name }}</h6>
                                        @if(isset($package->bandwidth) && $package->bandwidth)
                                        <small class="text-muted">{{ $package->bandwidth }} Mbps</small>
                                        @else
                                        <small class="text-muted">Add-on Product</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($package->package_type === 'regular')
                                <span class="badge bg-primary">Regular Product</span>
                                @else
                                <span class="badge bg-warning text-dark">Special product</span>
                                @endif
                            </td>
                            <td>
                                <p class="mb-1">{{ \Illuminate\Support\Str::limit($package->description, 60) }}</p>
                                <small class="text-muted">Created: {{ $package->created_at ? $package->created_at->format('M d, Y') : 'N/A' }}</small>
                            </td>
                            <td>
                                <div class="features-list">
                                    @if(isset($package->features) && is_array($package->features) && count($package->features) > 0)
                                        @foreach(array_slice($package->features, 0, 3) as $feature)
                                            <span class="badge bg-light text-dark mb-1">{{ $feature }}</span>
                                        @endforeach
                                        @if(count($package->features) > 3)
                                            <span class="badge bg-secondary mb-1">+{{ count($package->features) - 3 }} more</span>
                                        @endif
                                    @else
                                        <span class="text-muted">No features</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end">
                                <h6 class="text-success mb-0">৳{{ number_format($package->monthly_price, 2) }}<small class="text-muted">/month</small></h6>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary edit-package" data-id="{{ $package->p_id }}" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-outline-danger delete-package" data-id="{{ $package->p_id }}" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No products found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    Showing {{ $packages->count() }} of {{ $stats['total_packages'] ?? $packages->count() }} packages
                </div>
                <nav>
                    <!-- If you want pagination, send paginated $packages from controller -->
                    @if(method_exists($packages, 'links'))
                        {{ $packages->links() }}
                    @else
                        <ul class="pagination mb-0">
                            <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">Next</a></li>
                        </ul>
                    @endif
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Create Package Modal -->
<div class="modal fade" id="createPackageModal" tabindex="-1" aria-labelledby="createPackageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="createPackageForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createPackageModalLabel">Create New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div id="createErrors" class="alert alert-danger d-none"></div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="e.g., Basic Speed" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product Type *</label>
                            <select class="form-select" name="package_type" id="createPackageType" required>
                                <option value="">Select Type</option>
                                <option value="regular">Regular Product</option>
                                <option value="special">Special Product</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price (৳/month) *</label>
                            <input type="number" name="monthly_price" class="form-control" placeholder="500" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bandwidth (Mbps)</label>
                            <input type="number" name="bandwidth" class="form-control" placeholder="10" id="createBandwidthInput" min="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description *</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Package description..." required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Features</label>
                        <div class="features-input">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control feature-input" id="createFeatureText" placeholder="Add a feature">
                                <button class="btn btn-outline-primary" type="button" id="addFeatureCreate">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            <div class="features-list" id="featuresPreviewCreate"></div>
                            <input type="hidden" name="features" id="featuresInputCreate">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="createPackageBtn">Create Package</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Package Modal -->
<div class="modal fade" id="editPackageModal" tabindex="-1" aria-labelledby="editPackageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="editPackageForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="p_id" id="edit_p_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPackageModalLabel">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body" id="editPackageModalBody">
                    <!-- fields will be populated by JS -->
                    <div class="text-center py-4" id="editLoading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading product details...</p>
                    </div>

                    <div id="editErrors" class="alert alert-danger d-none"></div>

                    <div id="editFields" style="display:none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Product Name *</label>
                                <input type="text" name="name" id="edit_name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Product Type *</label>
                                <select class="form-select" name="package_type" id="edit_package_type" required>
                                    <option value="regular">Regular Product</option>
                                    <option value="special">Special Product</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price (৳/month) *</label>
                                <input type="number" name="monthly_price" id="edit_monthly_price" class="form-control" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bandwidth (Mbps)</label>
                                <input type="number" name="bandwidth" id="edit_bandwidth" class="form-control" min="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea class="form-control" name="description" id="edit_description" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Features</label>
                            <div class="features-input">
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control feature-input" id="editFeatureText" placeholder="Add a feature">
                                    <button class="btn btn-outline-primary" type="button" id="addFeatureEdit">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <div class="features-list" id="featuresPreviewEdit"></div>
                                <input type="hidden" name="features" id="featuresInputEdit">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="updatePackageBtn">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .package-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .bg-purple {
        background-color: #6f42c1 !important;
    }

    .bg-orange {
        background-color: #fd7e14 !important;
    }

    .features-list .badge {
        margin-right: 4px;
        margin-bottom: 4px;
        font-size: 0.75rem;
    }

    .stat-card {
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .avatar-sm {
        width: 50px;
        height: 50px;
    }

    .filter-btn.active {
        background-color: #4361ee;
        color: white;
    }

    .table th {
        border-top: none;
        font-weight: 600;
        color: #2b2d42;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table td {
        vertical-align: middle;
        padding: 16px 12px;
    }
</style>
@endsection

@section('scripts')
<script>
    (function() {
        // CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Toast helper (Bootstrap 5)
        function showToast(message, type = 'success') {
            const toastId = 'toast-' + Date.now();
            const wrapper = document.createElement('div');
            wrapper.innerHTML = `
                <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0 mb-2" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;
            document.getElementById('toastContainer').appendChild(wrapper.firstElementChild);
            const toastEl = document.getElementById(toastId);
            const toast = new bootstrap.Toast(toastEl);
            toast.show();

            // remove after hidden
            toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
        }

        // Utility to show validation errors in an alert element
        function showValidationErrors(containerEl, errors) {
            if (!containerEl) return;
            containerEl.classList.remove('d-none');
            if (typeof errors === 'string') {
                containerEl.innerHTML = errors;
                return;
            }
            const list = Object.values(errors).flat().map(e => `<div>• ${e}</div>`).join('');
            containerEl.innerHTML = list;
        }

        // Filter buttons
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', function() {
                document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                filterPackages(this.getAttribute('data-type'));
            });
        });

        function filterPackages(type) {
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const rowType = row.getAttribute('data-type');
                const match = !type || type === 'all' || rowType === type;
                row.style.display = match ? '' : 'none';
            });
        }

        // Search box
        document.querySelector('.search-box')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            document.querySelectorAll('tbody tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // CREATE: features handling (create)
        let featuresCreate = [];
        const featuresPreviewCreate = document.getElementById('featuresPreviewCreate');
        const featuresInputCreate = document.getElementById('featuresInputCreate');

        document.getElementById('addFeatureCreate')?.addEventListener('click', function() {
            const txt = document.getElementById('createFeatureText').value.trim();
            if (!txt) return;
            featuresCreate.push(txt);
            document.getElementById('createFeatureText').value = '';
            refreshCreateFeatures();
        });

        function refreshCreateFeatures() {
            featuresPreviewCreate.innerHTML = '';
            featuresCreate.forEach((f, i) => {
                const span = document.createElement('span');
                span.className = 'badge bg-primary me-2 mb-2';
                span.innerHTML = `${f} <i class="fas fa-times ms-1" style="cursor:pointer"></i>`;
                span.querySelector('i').addEventListener('click', () => {
                    featuresCreate.splice(i, 1);
                    refreshCreateFeatures();
                });
                featuresPreviewCreate.appendChild(span);
            });
            featuresInputCreate.value = JSON.stringify(featuresCreate);
        }

        // CREATE: disable bandwidth when special
        document.getElementById('createPackageType')?.addEventListener('change', function() {
            const bandwidthInput = document.getElementById('createBandwidthInput');
            if (this.value === 'special') {
                bandwidthInput.disabled = true;
                bandwidthInput.value = '';
                bandwidthInput.placeholder = 'Not applicable for add-ons';
            } else {
                bandwidthInput.disabled = false;
                bandwidthInput.placeholder = '10';
            }
        });

        // CREATE: submit form
        document.getElementById('createPackageForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const form = e.target;
            const btn = document.getElementById('createPackageBtn');
            btn.disabled = true;
            document.getElementById('createErrors').classList.add('d-none');

            const formData = new FormData(form);
            // ensure features value is included
            formData.set('features', featuresInputCreate.value || '[]');

            fetch('/admin/packages', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async res => {
                btn.disabled = false;
                if (!res.ok) {
                    const json = await res.json().catch(()=>null);
                    if (json && json.errors) {
                        showValidationErrors(document.getElementById('createErrors'), json.errors);
                    } else if (json && json.message) {
                        showValidationErrors(document.getElementById('createErrors'), json.message);
                    } else {
                        showToast('Failed to create package', 'danger');
                    }
                    throw new Error('Create failed');
                }
                return res.json();
            })
            .then(json => {
                if (json.success) {
                    bootstrap.Modal.getInstance(document.getElementById('createPackageModal')).hide();
                    showToast(json.message || 'Package created', 'success');
                    //Reload to reflect new package (recommended). If you prefer insert row, we can append dynamically.
                    setTimeout(()=> location.reload(), 700);
                } else {
                    showValidationErrors(document.getElementById('createErrors'), json.message || 'Unknown error');
                }
            })
            .catch(err => console.error(err));
        });

        // DELEGATED: Edit package (open modal & populate)
        document.body.addEventListener('click', function(e) {
            const editBtn = e.target.closest('.edit-package');
            if (editBtn) {
                const pId = editBtn.getAttribute('data-id');
                openEditModal(pId);
            }
        });

        async function openEditModal(pId) {
            // reset
            document.getElementById('editErrors').classList.add('d-none');
            document.getElementById('editLoading').style.display = '';
            document.getElementById('editFields').style.display = 'none';
            featuresEdit = [];
            refreshEditFeatures();

            const modalEl = document.getElementById('editPackageModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            try {
                const res = await fetch(`/admin/packages/${pId}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) throw new Error('Failed to fetch package');
                const pkg = await res.json();

                // populate fields
                document.getElementById('edit_p_id').value = pkg.p_id;
                document.getElementById('edit_name').value = pkg.name || '';
                document.getElementById('edit_package_type').value = pkg.package_type || 'regular';
                document.getElementById('edit_monthly_price').value = pkg.monthly_price ?? '';
                document.getElementById('edit_bandwidth').value = pkg.bandwidth ?? '';
                document.getElementById('edit_description').value = pkg.description ?? '';

                // features - array expected
                try {
                    featuresEdit = Array.isArray(pkg.features) ? pkg.features.slice() : (pkg.features ? JSON.parse(pkg.features) : []);
                } catch {
                    featuresEdit = [];
                }
                refreshEditFeatures();

                // adjust bandwidth disabled based on type
                if (pkg.package_type === 'special') {
                    document.getElementById('edit_bandwidth').disabled = true;
                    document.getElementById('edit_bandwidth').placeholder = 'Not applicable for add-ons';
                } else {
                    document.getElementById('edit_bandwidth').disabled = false;
                }

                document.getElementById('editLoading').style.display = 'none';
                document.getElementById('editFields').style.display = '';
            } catch (err) {
                console.error(err);
                document.getElementById('editPackageModalBody').innerHTML = `
                    <div class="alert alert-danger">Failed to load package details. Please try again.</div>
                `;
            }
        }

        // EDIT: features handling
        let featuresEdit = [];
        const featuresPreviewEdit = document.getElementById('featuresPreviewEdit');
        const featuresInputEdit = document.getElementById('featuresInputEdit');

        document.getElementById('addFeatureEdit')?.addEventListener('click', function() {
            const txt = document.getElementById('editFeatureText').value.trim();
            if (!txt) return;
            featuresEdit.push(txt);
            document.getElementById('editFeatureText').value = '';
            refreshEditFeatures();
        });

        function refreshEditFeatures() {
            featuresPreviewEdit.innerHTML = '';
            featuresEdit.forEach((f, i) => {
                const span = document.createElement('span');
                span.className = 'badge bg-primary me-2 mb-2';
                span.innerHTML = `${f} <i class="fas fa-times ms-1" style="cursor:pointer"></i>`;
                span.querySelector('i').addEventListener('click', () => {
                    featuresEdit.splice(i, 1);
                    refreshEditFeatures();
                });
                featuresPreviewEdit.appendChild(span);
            });
            featuresInputEdit.value = JSON.stringify(featuresEdit);
        }

        // EDIT: disable bandwidth when special
        document.getElementById('edit_package_type')?.addEventListener('change', function() {
            const bandwidthInput = document.getElementById('edit_bandwidth');
            if (this.value === 'special') {
                bandwidthInput.disabled = true;
                bandwidthInput.value = '';
                bandwidthInput.placeholder = 'Not applicable for add-ons';
            } else {
                bandwidthInput.disabled = false;
                bandwidthInput.placeholder = '10';
            }
        });

        // UPDATE: submit update (edit form)
        document.getElementById('editPackageForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            await submitUpdate();
        });

        // Also allow update via update button in footer
        document.getElementById('updatePackageBtn')?.addEventListener('click', async function() {
            await submitUpdate();
        });

        async function submitUpdate() {
            const form = document.getElementById('editPackageForm');
            const pId = document.getElementById('edit_p_id').value;
            const btn = document.getElementById('updatePackageBtn');
            btn.disabled = true;
            document.getElementById('editErrors').classList.add('d-none');

            const formData = new FormData(form);
            formData.set('features', featuresInputEdit.value || '[]');

            try {
                const res = await fetch(`/admin/packages/${pId}`, {
                    method: 'POST', // use method override for compatibility
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-HTTP-Method-Override': 'PUT',
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const json = await res.json().catch(()=>({}));
                btn.disabled = false;

                if (!res.ok) {
                    if (json && json.errors) {
                        showValidationErrors(document.getElementById('editErrors'), json.errors);
                    } else if (json && json.message) {
                        showValidationErrors(document.getElementById('editErrors'), json.message);
                    } else {
                        showToast('Failed to update package', 'danger');
                    }
                    return;
                }

                if (json.success) {
                    bootstrap.Modal.getInstance(document.getElementById('editPackageModal')).hide();
                    showToast(json.message || 'Package updated', 'success');
                    setTimeout(()=> location.reload(), 700);
                } else {
                    showValidationErrors(document.getElementById('editErrors'), json.message || 'Unknown error');
                }
            } catch (err) {
                console.error(err);
                btn.disabled = false;
            }
        }

        // DELEGATED: Delete package
        document.body.addEventListener('click', function(e) {
            const delBtn = e.target.closest('.delete-package');
            if (!delBtn) return;
            const pId = delBtn.getAttribute('data-id');
            if (!confirm('Are you sure you want to delete this package? This action cannot be undone.')) return;
            deletePackage(pId);
        });

        async function deletePackage(pId) {
            try {
                const res = await fetch(`/admin/packages/${pId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });
                const json = await res.json().catch(()=>({}));
                if (!res.ok) {
                    showToast(json.message || 'Failed to delete package', 'danger');
                    return;
                }
                showToast(json.message || 'Package deleted', 'success');
                // remove row or reload
                document.getElementById(`package-row-${pId}`)?.remove();
            } catch (err) {
                console.error(err);
                showToast('Failed to delete package', 'danger');
            }
        }

        // EXPORT button (simple CSV from current DOM table) - optional
        document.getElementById('exportBtn')?.addEventListener('click', function() {
            const rows = Array.from(document.querySelectorAll('table tbody tr'));
            const csv = [];
            csv.push(['p_id', 'name', 'package_type', 'monthly_price', 'description'].join(','));
            rows.forEach(row => {
                // skip hidden rows
                if (row.style.display === 'none') return;
                const cols = row.querySelectorAll('td');
                if (cols.length < 6) return;
                const rowData = [
                    `"${cols[0].textContent.trim()}"`,
                    `"${cols[1].querySelector('h6') ? cols[1].querySelector('h6').textContent.trim() : cols[1].textContent.trim()}"`,
                    `"${cols[2].textContent.trim()}"`,
                    `"${cols[5].textContent.trim().replace('/month','').replace('৳','').trim()}"`,
                    `"${cols[3].querySelector('p') ? cols[3].querySelector('p').textContent.trim() : cols[3].textContent.trim()}"`
                ];
                csv.push(rowData.join(','));
            });
            const csvContent = 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv.join('\n'));
            const a = document.createElement('a');
            a.setAttribute('href', csvContent);
            a.setAttribute('download', `packages_export_${Date.now()}.csv`);
            document.body.appendChild(a);
            a.click();
            a.remove();
            showToast('Export started', 'success');
        });

        // initial filter (all)
        filterPackages('all');

    })();
</script>
@endsection
