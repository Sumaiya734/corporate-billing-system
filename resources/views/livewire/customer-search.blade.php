<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Customer;

class CustomerSearch extends Component
{
    public $query = '';
    public $customers = [];
    public $selectedCustomer = null;

    protected $updatesQueryString = ['query'];
    protected $listeners = ['clearCustomer' => 'resetSelection'];

    // Debounce or lazy search after typing stops for 500ms
    public function updatedQuery()
    {
        $this->resetSelection();
        $this->customers = strlen($this->query) > 1
            ? Customer::where('name', 'like', '%' . $this->query . '%')
                ->orWhere('phone', 'like', '%' . $this->query . '%')
                ->orWhere('customer_id', 'like', '%' . $this->query . '%')
                ->limit(10)
                ->get()
            : [];
    }

    public function selectCustomer($id)
    {
        $this->selectedCustomer = Customer::find($id);
        $this->customers = [];
        $this->query = '';
        $this->emitUp('customerSelected', $this->selectedCustomer);
    }

    public function resetSelection()
    {
        $this->selectedCustomer = null;
    }

    public function render()
    {
        return view('livewire.customer-search');
    }
}
