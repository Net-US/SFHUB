<?php

namespace App\Http\Controllers;

use App\Models\ProductivityLog;
use Illuminate\Http\Request;

class ProductivityLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function productivity()
    {
        return view('dashboard.productivity');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ProductivityLog $productivityLog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductivityLog $productivityLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProductivityLog $productivityLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProductivityLog $productivityLog)
    {
        //
    }
}
