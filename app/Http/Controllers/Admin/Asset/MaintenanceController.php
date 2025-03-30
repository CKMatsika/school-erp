<?php

namespace App\Http\Controllers\Admin\Asset;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\AssetMaintenance;
use App\Models\Admin\Asset;

class AssetMaintenanceController extends Controller
{
    /**
     * Display a listing of the asset maintenance records.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $maintenances = AssetMaintenance::with('asset')->latest()->paginate(10);
        return view('admin.asset.maintenance.index', compact('maintenances'));
    }

    /**
     * Show the form for creating a new maintenance record.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $assets = Asset::all();
        return view('admin.asset.maintenance.create', compact('assets'));
    }

    /**
     * Store a newly created maintenance record in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'maintenance_date' => 'required|date',
            'maintenance_type' => 'required|string|max:100',
            'cost' => 'required|numeric',
            'performed_by' => 'required|string|max:255',
            'description' => 'nullable|string',
            'next_maintenance_date' => 'nullable|date|after:maintenance_date',
        ]);

        AssetMaintenance::create($request->all());

        return redirect()->route('admin.asset.maintenance.index')
            ->with('success', 'Maintenance record created successfully.');
    }

    /**
     * Display the specified maintenance record.
     *
     * @param  \App\Models\Admin\AssetMaintenance  $maintenance
     * @return \Illuminate\View\View
     */
    public function show(AssetMaintenance $maintenance)
    {
        return view('admin.asset.maintenance.show', compact('maintenance'));
    }

    /**
     * Show the form for editing the specified maintenance record.
     *
     * @param  \App\Models\Admin\AssetMaintenance  $maintenance
     * @return \Illuminate\View\View
     */
    public function edit(AssetMaintenance $maintenance)
    {
        $assets = Asset::all();
        return view('admin.asset.maintenance.edit', compact('maintenance', 'assets'));
    }

    /**
     * Update the specified maintenance record in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin\AssetMaintenance  $maintenance
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, AssetMaintenance $maintenance)
    {
        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'maintenance_date' => 'required|date',
            'maintenance_type' => 'required|string|max:100',
            'cost' => 'required|numeric',
            'performed_by' => 'required|string|max:255',
            'description' => 'nullable|string',
            'next_maintenance_date' => 'nullable|date|after:maintenance_date',
        ]);

        $maintenance->update($request->all());

        return redirect()->route('admin.asset.maintenance.index')
            ->with('success', 'Maintenance record updated successfully.');
    }

    /**
     * Remove the specified maintenance record from storage.
     *
     * @param  \App\Models\Admin\AssetMaintenance  $maintenance
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(AssetMaintenance $maintenance)
    {
        $maintenance->delete();

        return redirect()->route('admin.asset.maintenance.index')
            ->with('success', 'Maintenance record deleted successfully.');
    }
}