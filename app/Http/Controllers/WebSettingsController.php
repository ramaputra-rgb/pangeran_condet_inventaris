<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class WebSettingsController extends Controller
{
    /**
     * Menampilkan halaman pengaturan web (mode tampilan light dan dark).
     */
    public function index(): View
    {
        return view('settings.index');
    }
}
