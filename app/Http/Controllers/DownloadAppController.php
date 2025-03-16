<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DownloadAppController extends Controller
{
    public function show()
    {
        return view('auth.download-app');
    }
}
