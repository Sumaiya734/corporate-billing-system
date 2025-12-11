<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\Invoice;
use App\Models\CustomerProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RollingBillingHelper
{
    /**
     * Check if a customer should be billed in specific month
     * Based on assign_date and billing_cycle_months
     */
    public static function shouldBillThisMonth($cpId, $monthDate)
    {
        $customerProduct = CustomerProduct::find($cpId);
        if (!$customerProduct) return false;
        
        $assignDate = Carbon::parse($customerProduct->assign_date);
        $billingCycle = $customerProduct->billing_cycle_months;
        
        // If assignment is in the future, don't bill
        if ($assignDate > $monthDate->endOfMonth()) {
            return false;
        }
        
        // Calculate months since assignment
        $monthsSinceAssign = $assignDate->diffInMonths($monthDate);
        
        // Check if this is a billing month (months divisible by billing cycle)
        return $monthsSinceAssign >= 0 && ($monthsSinceAssign % $billingCycle) === 0;
    }
    
    /**
     * Get the current cycle position for a customer product
     * 0 = Start of cycle, 1 = middle, 2 = end (for 3-month cycle)
     */
    public static function getCyclePosition($cpId, $monthDate)
    {
        $customerProduct = CustomerProduct::find($cpId);
        if (!$customerProduct) return 0;
        
        $assignDate = Carbon::parse($customerProduct->assign_date);
        $billingCycle = $customerProduct->billing_cycle_months;
        $monthsSinceAssign = $assignDate->diffInMonths($monthDate);
        
        // Position in current cycle
        return $monthsSinceAssign % $billingCycle;
    }
    
    /**
     * Get which billing cycle number this is
     */
    public static function getCycleNumber($cpId, $monthDate)
    {
        $customerProduct = CustomerProduct::find($cpId);
        if (!$customerProduct) return 1;
        
        $assignDate = Carbon::parse($customerProduct->assign_date);
        $billingCycle = $customerProduct->billing_cycle_months;
        $monthsSinceAssign = $assignDate->diffInMonths($monthDate);
        
        // Cycle number (starting from 1)
        return floor($monthsSinceAssign / $billingCycle) + 1;
    }
    
    /**
     * Calculate subtotal for this month
     */
    public static function calculateSubtotal($cpId, $monthDate)
    {
        $customerProduct = CustomerProduct::find($cpId);
        if (!$customerProduct) return 0;
        
        // Check if this is start of billing cycle
        $cyclePosition = self::getCyclePosition($cpId, $monthDate);
        
        if ($cyclePosition == 0) {
            // Start of cycle: charge full cycle amount
            $monthlyPrice = DB::table('products')
                ->where('p_id', $customerProduct->p_id)
                ->value('monthly_price');
            
            return $monthlyPrice * $customerProduct->billing_cycle_months;
        }
        
        // Middle of cycle: no new subtotal
        return 0;
    }
}