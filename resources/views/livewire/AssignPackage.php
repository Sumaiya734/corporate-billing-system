<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Customer;
use App\Models\product;
use Illuminate\Support\Facades\DB;

class Assignproduct extends Component
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

    protected $listeners = ['customerSelected'];

    public function mount()
    {
        $this->products = product::where('status', 'active')->get();
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

    public function updatedproductSelections()
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
                $product = product::find($this->productSelections[$index]);
                if ($product) {
                    $months = intval($this->billingMonths[$index] ?? 1);
                    $this->totalAmount += $product->monthly_price * $months;
                }
            }
        }
    }

    public function getproductAmount($index)
    {
        if (!empty($this->productSelections[$index])) {
            $product = product::find($this->productSelections[$index]);
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
        $hasproductSelected = false;
        foreach ($this->productSelections as $productId) {
            if (!empty($productId)) {
                $hasproductSelected = true;
                break;
            }
        }

        if (!$hasproductSelected) {
            session()->flash('error', 'Please select at least one product.');
            return;
        }

        // Validate no duplicate products
        $selectedproducts = [];
        foreach ($this->productSelections as $productId) {
            if (!empty($productId)) {
                if (in_array($productId, $selectedproducts)) {
                    session()->flash('error', 'You cannot assign the same product multiple times to the same customer.');
                    return;
                }
                $selectedproducts[] = $productId;
            }
        }

        try {
            DB::beginTransaction();

            foreach ($this->rows as $index) {
                if (!empty($this->productSelections[$index])) {
                    $product = product::find($this->productSelections[$index]);
                    
                    if ($product) {
                        // Create customer product assignment
                        $customerproduct = new \App\Models\Customerproduct();
                        $customerproduct->customer_id = $this->selectedCustomer->id;
                        $customerproduct->product_id = $product->id;
                        $customerproduct->billing_months = $this->billingMonths[$index];
                        $customerproduct->assign_date = $this->assignDates[$index];
                        $customerproduct->monthly_price = $product->monthly_price;
                        $customerproduct->total_amount = $product->monthly_price * $this->billingMonths[$index];
                        $customerproduct->status = 'active';
                        $customerproduct->save();
                    }
                }
            }

            DB::commit();

            session()->flash('success', 'products assigned successfully!');
            
            // Reset form
            $this->reset();
            $this->mount();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error assigning products: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.assign-product');
    }
}