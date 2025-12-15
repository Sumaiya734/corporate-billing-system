<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Customer;
use App\Models\Product;
use App\Models\CustomerProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssignProduct extends Component
{
    public $search = '';
    public $customers = [];
    public $selectedCustomer = null;

    public $products = [];
    public $rows = [];
    public $totalAmount = 0;
    
    public $productSelections = [];
    public $billingMonths = [];
    public $assignDates = [];

    public function mount()
    {
        $this->products = Product::orderBy('name')->get();
        $this->rows = [0]; // Start with one row
        $this->productSelections[0] = '';
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
                ->where('is_active', true)
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
        $this->dispatch('customerSelected', $this->selectedCustomer);
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
        $this->productSelections[$newIndex] = '';
        $this->billingMonths[$newIndex] = '1';
        $this->assignDates[$newIndex] = now()->format('Y-m-d');
    }

    public function removeRow($index)
    {
        if (count($this->rows) > 1) {
            unset($this->rows[$index]);
            unset($this->productSelections[$index]);
            unset($this->billingMonths[$index]);
            unset($this->assignDates[$index]);
            
            // Reindex arrays
            $this->rows = array_values($this->rows);
            $this->productSelections = array_values($this->productSelections);
            $this->billingMonths = array_values($this->billingMonths);
            $this->assignDates = array_values($this->assignDates);
        }
        
        $this->calculateTotal();
    }

    public function updatedProductSelections()
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
            if (!empty($this->productSelections[$index])) {
                $product = Product::find($this->productSelections[$index]);
                if ($product) {
                    $months = intval($this->billingMonths[$index] ?? 1);
                    $this->totalAmount += $product->monthly_price * $months;
                }
            }
        }
    }

    public function getProductAmount($index)
    {
        if (!empty($this->productSelections[$index])) {
            $product = Product::find($this->productSelections[$index]);
            if ($product) {
                $months = intval($this->billingMonths[$index] ?? 1);
                return $product->monthly_price * $months;
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

        // Validate at least one product is selected
        $hasProductSelected = false;
        foreach ($this->productSelections as $productId) {
            if (!empty($productId)) {
                $hasProductSelected = true;
                break;
            }
        }

        if (!$hasProductSelected) {
            session()->flash('error', 'Please select at least one product.');
            return;
        }

        // Validate no duplicate products
        $selectedProducts = [];
        foreach ($this->productSelections as $productId) {
            if (!empty($productId)) {
                if (in_array($productId, $selectedProducts)) {
                    session()->flash('error', 'You cannot assign the same product multiple times to the same customer.');
                    return;
                }
                $selectedProducts[] = $productId;
            }
        }

        try {
            DB::beginTransaction();

            foreach ($this->rows as $index) {
                if (!empty($this->productSelections[$index])) {
                    $product = Product::find($this->productSelections[$index]);
                    
                    if ($product) {
                        // Check if customer already has this product assigned
                        $existing = CustomerProduct::where('c_id', $this->selectedCustomer->c_id)
                            ->where('p_id', $product->p_id)
                            ->where('status', 'active')
                            ->first();
                            
                        if ($existing) {
                            session()->flash('error', 'Customer already has ' . $product->name . ' assigned.');
                            DB::rollBack();
                            return;
                        }
                        
                        // Calculate due date (same day of month as assign date or next month if assign date has passed)
                        $assignDate = \Carbon\Carbon::parse($this->assignDates[$index])->startOfDay();
                        $dueDay = $assignDate->day;
                        
                        // Create customer product assignment
                        $customerProduct = new CustomerProduct();
                        $customerProduct->c_id = $this->selectedCustomer->c_id;
                        $customerProduct->p_id = $product->p_id;
                        $customerProduct->custom_price = $product->monthly_price * $this->billingMonths[$index];
                        $customerProduct->is_custom_price = true; // Set the flag to indicate custom price is being used
                        $customerProduct->billing_cycle_months = $this->billingMonths[$index];
                        $customerProduct->assign_date = $this->assignDates[$index];
                        
                        // Calculate due date
                        $dueDate = $assignDate->copy()->day($dueDay);
                        if ($dueDate->lt($assignDate)) {
                            $dueDate->addMonth();
                        }
                        $customerProduct->due_date = $dueDate->format('Y-m-d');
                        
                        $customerProduct->status = 'active';
                        $customerProduct->is_active = true;
                        $customerProduct->save();
                    }
                }
            }

            DB::commit();

            session()->flash('success', count($selectedProducts) . ' product(s) assigned successfully!');
            
            // Reset form
            $this->resetExcept(['products']);
            $this->mount();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error assigning products: ' . $e->getMessage());
            session()->flash('error', 'Error assigning products: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.assign-product');
    }
}