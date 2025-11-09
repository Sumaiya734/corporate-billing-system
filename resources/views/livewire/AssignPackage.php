<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Customer;
use App\Models\Package;
use Illuminate\Support\Facades\DB;

class AssignPackage extends Component
{
    public $search = '';
    public $customers = [];
    public $selectedCustomer = null;

    public $packages = [];
    public $rows = [];
    public $totalAmount = 0;
    
    public $packageSelections = [];
    public $billingMonths = [];
    public $assignDates = [];

    protected $listeners = ['customerSelected'];

    public function mount()
    {
        $this->packages = Package::where('status', 'active')->get();
        $this->rows = [0]; // Start with one row
        $this->packageSelections[0] = '';
        $this->billingMonths[0] = '1';
        $this->assignDates[0] = now()->format('Y-m-d');
    }

    public function updatedSearch()
    {
        if (strlen($this->search) >= 2) {
            $this->customers = Customer::where(function($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                          ->orWhere('phone', 'like', '%' . $this->search . '%')
                          ->orWhere('email', 'like', '%' . $this->search . '%')
                          ->orWhere('customer_id', 'like', '%' . $this->search . '%');
                })
                ->where('status', 'active')
                ->limit(10)
                ->get();
        } else {
            $this->customers = [];
        }
    }

    public function selectCustomer($customerId)
    {
        $this->selectedCustomer = Customer::find($customerId);
        $this->search = ''; // Clear search input
        $this->customers = [];
        $this->dispatchBrowserEvent('customer-selected', [
            'customer' => $this->selectedCustomer
        ]);
    }

    public function clearCustomer()
    {
        $this->selectedCustomer = null;
        $this->search = '';
        $this->customers = [];
    }

    public function addRow()
    {
        $newIndex = count($this->rows);
        $this->rows[] = $newIndex;
        $this->packageSelections[$newIndex] = '';
        $this->billingMonths[$newIndex] = '1';
        $this->assignDates[$newIndex] = now()->format('Y-m-d');
    }

    public function removeRow($index)
    {
        if (count($this->rows) > 1) {
            unset($this->rows[$index]);
            unset($this->packageSelections[$index]);
            unset($this->billingMonths[$index]);
            unset($this->assignDates[$index]);
            
            // Reindex arrays
            $this->rows = array_values($this->rows);
            $this->packageSelections = array_values($this->packageSelections);
            $this->billingMonths = array_values($this->billingMonths);
            $this->assignDates = array_values($this->assignDates);
        }
        
        $this->calculateTotal();
    }

    public function updatedPackageSelections()
    {
        $this->calculateTotal();
    }

    public function updatedBillingMonths()
    {
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->totalAmount = 0;
        
        foreach ($this->rows as $index) {
            if (!empty($this->packageSelections[$index])) {
                $package = Package::find($this->packageSelections[$index]);
                if ($package) {
                    $months = intval($this->billingMonths[$index] ?? 1);
                    $this->totalAmount += $package->monthly_price * $months;
                }
            }
        }
    }

    public function getPackageAmount($index)
    {
        if (!empty($this->packageSelections[$index])) {
            $package = Package::find($this->packageSelections[$index]);
            if ($package) {
                $months = intval($this->billingMonths[$index] ?? 1);
                return $package->monthly_price * $months;
            }
        }
        return 0;
    }

    public function submit()
    {
        // Validate customer selection
        if (!$this->selectedCustomer) {
            session()->flash('error', 'Please select a customer.');
            return;
        }

        // Validate at least one package is selected
        $hasPackageSelected = false;
        foreach ($this->packageSelections as $packageId) {
            if (!empty($packageId)) {
                $hasPackageSelected = true;
                break;
            }
        }

        if (!$hasPackageSelected) {
            session()->flash('error', 'Please select at least one package.');
            return;
        }

        // Validate no duplicate packages
        $selectedPackages = [];
        foreach ($this->packageSelections as $packageId) {
            if (!empty($packageId)) {
                if (in_array($packageId, $selectedPackages)) {
                    session()->flash('error', 'You cannot assign the same package multiple times to the same customer.');
                    return;
                }
                $selectedPackages[] = $packageId;
            }
        }

        try {
            DB::beginTransaction();

            foreach ($this->rows as $index) {
                if (!empty($this->packageSelections[$index])) {
                    $package = Package::find($this->packageSelections[$index]);
                    
                    if ($package) {
                        // Create customer package assignment
                        $customerPackage = new \App\Models\CustomerPackage();
                        $customerPackage->customer_id = $this->selectedCustomer->id;
                        $customerPackage->package_id = $package->id;
                        $customerPackage->billing_months = $this->billingMonths[$index];
                        $customerPackage->assign_date = $this->assignDates[$index];
                        $customerPackage->monthly_price = $package->monthly_price;
                        $customerPackage->total_amount = $package->monthly_price * $this->billingMonths[$index];
                        $customerPackage->status = 'active';
                        $customerPackage->save();
                    }
                }
            }

            DB::commit();

            session()->flash('success', 'Packages assigned successfully!');
            
            // Reset form
            $this->reset();
            $this->mount();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error assigning packages: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.assign-package');
    }
}