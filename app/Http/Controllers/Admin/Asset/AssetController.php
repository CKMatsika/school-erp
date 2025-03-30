<?php

namespace App\Http\Controllers\Admin\Asset;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Asset;

class AssetController extends Controller
{
    /**
     * Display a listing of the assets.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $assets = Asset::latest()->paginate(10);
        return view('admin.asset.index', compact('assets'));
    }

    /**
     * Show the form for creating a new asset.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.asset.create');
    }

    /**
     * Store a newly created asset in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'asset_code' => 'required|string|max:50|unique:assets',
            'category' => 'required|string|max:100',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric',
            'warranty_period' => 'nullable|string|max:50',
            'supplier' => 'nullable|string|max:255',
            'condition' => 'required|string|max:50',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Asset::create($request->all());

        return redirect()->route('admin.asset.index')
            ->with('success', 'Asset created successfully.');
    }

    /**
     * Display the specified asset.
     *
     * @param  \App\Models\Admin\Asset  $asset
     * @return \Illuminate\View\View
     */
    public function show(Asset $asset)
    {
        return view('admin.asset.show', compact('asset'));
    }

    /**
     * Show the form for editing the specified asset.
     *
     * @param  \App\Models\Admin\Asset  $asset
     * @return \Illuminate\View\View
     */
    public function edit(Asset $asset)
    {
        return view('admin.asset.edit', compact('asset'));
    }

    /**
     * Update the specified asset in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin\Asset  $asset
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Asset $asset)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'asset_code' => 'required|string|max:50|unique:assets,asset_code,'.$asset->id,
            'category' => 'required|string|max:100',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric',
            'warranty_period' => 'nullable|string|max:50',
            'supplier' => 'nullable|string|max:255',
            'condition' => 'required|string|max:50',
            'location' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $asset->update($request->all());

        return redirect()->route('admin.asset.index')
            ->with('success', 'Asset updated successfully.');
    }

    /**
     * Remove the specified asset from storage.
     *
     * @param  \App\Models\Admin\Asset  $asset
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Asset $asset)
    {
        $asset->delete();

        return redirect()->route('admin.asset.index')
            ->with('success', 'Asset deleted successfully.');
    }
}