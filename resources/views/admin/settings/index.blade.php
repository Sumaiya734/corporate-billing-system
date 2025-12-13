@extends('layouts.admin')

@section('title', 'System Settings')

@section('content')
<div class="page-header">
    <h3 class="mb-0"><i class="fas fa-cog me-2"></i>System Settings</h3>
    <p class="text-muted mb-0">Manage system-wide configuration settings</p>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-sliders-h me-2"></i>Configuration Settings</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                <form action="{{ route('admin.settings.update') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fixed_monthly_charge" class="form-label">Fixed Monthly Charge</label>
                                <div class="input-group">
                                    <span class="input-group-text">৳</span>
                                    <input type="number" 
                                           class="form-control @error('fixed_monthly_charge') is-invalid @enderror" 
                                           id="fixed_monthly_charge" 
                                           name="fixed_monthly_charge" 
                                           value="{{ old('fixed_monthly_charge', $settingsArray['fixed_monthly_charge']->value ?? '0') }}"
                                           step="0.01"
                                           min="0">
                                </div>
                                <div class="form-text">
                                    {{ $settingsArray['fixed_monthly_charge']->description ?? 'Fixed charge applied to all customers monthly' }}
                                </div>
                                @error('fixed_monthly_charge')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vat_percentage" class="form-label">VAT Percentage</label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control @error('vat_percentage') is-invalid @enderror" 
                                           id="vat_percentage" 
                                           name="vat_percentage" 
                                           value="{{ old('vat_percentage', $settingsArray['vat_percentage']->value ?? '0') }}"
                                           step="0.01"
                                           min="0"
                                           max="100">
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="form-text">
                                    {{ $settingsArray['vat_percentage']->description ?? 'VAT percentage applied to invoices' }}
                                </div>
                                @error('vat_percentage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Save Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Information</h5>
            </div>
            <div class="card-body">
                <h6>About System Settings</h6>
                <p class="text-muted">
                    Configure system-wide settings that affect billing calculations and system behavior.
                </p>
                
                <h6>Current Settings</h6>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0">
                        <div class="d-flex justify-content-between">
                            <span>Fixed Monthly Charge:</span>
                            <strong>৳{{ number_format($settingsArray['fixed_monthly_charge']->value ?? 0, 2) }}</strong>
                        </div>
                    </li>
                    <li class="list-group-item px-0">
                        <div class="d-flex justify-content-between">
                            <span>VAT Percentage:</span>
                            <strong>{{ $settingsArray['vat_percentage']->value ?? 0 }}%</strong>
                        </div>
                    </li>
                </ul>
                
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-exclamation-circle me-1"></i>
                        Changes to these settings will affect all future billing calculations.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection