<?php

namespace App\Http\Controllers;

use App\Models\StockInventory;
use App\Models\Order;

class DashboardController extends Controller
{
    public function index()
    {
        $totalPhysical = StockInventory::sum('total_physical_stock');
        $totalReserved = StockInventory::sum('reserved_stock');
        $totalATP = StockInventory::sum('atp_stock');
        
        $orders = Order::with('items.product')->latest()->take(5)->get();

        return view('dashboard.index', compact('totalPhysical', 'totalReserved', 'totalATP', 'orders'));
    }
}
