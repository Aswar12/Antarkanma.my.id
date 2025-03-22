<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MerchantSectionController extends Controller
{
    public function index()
    {
        $merchants = Merchant::with(['user'])
            ->where('is_active', true)
            ->withCount('orders as total_orders')
            ->withAvg('reviews as rating', 'rating')
            ->get()
            ->map(function ($merchant) {
                $merchant->logo_url = $merchant->logo_path
                    ? Storage::disk('s3')->url($merchant->logo_path)
                    : asset('images/default-merchant.png');
                return $merchant;
            });

        return view('sections.merchant-social-card', compact('merchants'));
    }

    public function show(Merchant $merchant)
    {
        $merchant->load(['user'])
            ->loadCount('orders as total_orders')
            ->loadAvg('reviews as rating', 'rating');

        $merchant->logo_url = $merchant->logo_path
            ? Storage::disk('s3')->url($merchant->logo_path)
            : asset('images/default-merchant.png');

        return view('sections.merchant-social-card', [
            'merchants' => collect([$merchant])
        ]);
    }
}
