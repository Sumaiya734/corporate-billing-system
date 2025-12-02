@extends('layouts.admin')

@section('title', 'Test product Creation')

@section('content')
<div class="page-body p-4">
    <h1>Test product Creation</h1>
    
    <div class="card">
        <div class="card-body">
            <h2>CSRF Token Test</h2>
            <p>CSRF Meta Tag: <span id="csrf-meta">Not found</span></p>
            <p>CSRF Token Value: <span id="csrf-value">Not found</span></p>
            
            <h2>Form Test</h2>
            <form id="testForm" method="POST" action="{{ route('admin.products.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="Test product">
                </div>
                <div class="mb-3">
                    <label class="form-label">product Type</label>
                    <select name="product_type_id" class="form-control">
                        <option value="1">Regular</option>
                        <option value="2">Special</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Price</label>
                    <input type="number" name="monthly_price" class="form-control" value="100">
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control">Test Description</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check for CSRF meta tag
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    if (csrfMeta) {
        document.getElementById('csrf-meta').textContent = 'Found';
        document.getElementById('csrf-value').textContent = csrfMeta.getAttribute('content');
    } else {
        document.getElementById('csrf-meta').textContent = 'Not found';
    }
    
    // Test form submission
    const form = document.getElementById('testForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submitted');
            
            const formData = new FormData(form);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            fetch('{{ route("admin.products.store") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log('Success:', data);
                alert('product created: ' + (data.message || 'Success'));
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error: ' + error.message);
            });
        });
    }
});
</script>
@endsection