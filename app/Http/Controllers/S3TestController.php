<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class S3TestController extends Controller
{
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $file = $request->file('image');
        $fileName = time() . '_' . $file->getClientOriginalName();
        
        // Upload the image to S3
        Storage::disk('s3')->put($fileName, file_get_contents($file), 'public');
        
        // Get the public URL
        $url = Storage::disk('s3')->url($fileName);
        
        return response()->json(['message' => 'Image uploaded successfully to S3!', 'url' => $url]);
    }
}
