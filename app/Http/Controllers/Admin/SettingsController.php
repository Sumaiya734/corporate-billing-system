<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    /**
     * Display the settings page
     */
    public function index()
    {
        try {
            // Get all system settings
            $settings = DB::table('system_settings')->get();
            
            // Convert to key-value pairs for easier access
            $settingsArray = [];
            foreach ($settings as $setting) {
                $settingsArray[$setting->key] = $setting;
            }
            
            return view('admin.settings.index', compact('settingsArray'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load settings: ' . $e->getMessage());
        }
    }
    
    /**
     * Update system settings
     */
    public function update(Request $request)
    {
        try {
            // Define validation rules
            $validator = Validator::make($request->all(), [
                'fixed_monthly_charge' => 'nullable|numeric|min:0',
                'vat_percentage' => 'nullable|numeric|min:0|max:100',
            ]);
            
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            // Update settings
            if ($request->has('fixed_monthly_charge')) {
                DB::table('system_settings')
                    ->where('key', 'fixed_monthly_charge')
                    ->update([
                        'value' => $request->fixed_monthly_charge,
                        'updated_at' => now()
                    ]);
            }
            
            if ($request->has('vat_percentage')) {
                DB::table('system_settings')
                    ->where('key', 'vat_percentage')
                    ->update([
                        'value' => $request->vat_percentage,
                        'updated_at' => now()
                    ]);
            }
            
            return redirect()->back()->with('success', 'Settings updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update settings: ' . $e->getMessage());
        }
    }
}