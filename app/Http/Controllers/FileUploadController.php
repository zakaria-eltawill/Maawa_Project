<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileUploadRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadController extends Controller
{
    /**
     * Upload an image file
     *
     * @param FileUploadRequest $request
     * @return JsonResponse
     */
    public function upload(FileUploadRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $folder = $request->input('folder', 'uploads');

        // Sanitize folder name (remove special characters, keep only alphanumeric, dash, underscore)
        $folder = preg_replace('/[^a-zA-Z0-9_-]/', '', $folder);
        if (empty($folder)) {
            $folder = 'uploads';
        }

        // Generate unique filename
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '_' . Str::random(10) . '_' . Str::slug($originalName) . '.' . $extension;

        // Store file in public disk
        $path = $file->storeAs($folder, $filename, 'public');

        // Get public URL using the request's scheme and host
        // This ensures we use the actual server domain, not localhost
        $scheme = $request->getScheme(); // http or https
        $host = $request->getHost(); // e.g., 3.72.254.198 or your-domain.com
        
        // Handle port if present (for development environments)
        $port = $request->getPort();
        $portString = ($port && !in_array($port, [80, 443])) ? ':' . $port : '';
        
        $storagePath = '/storage/' . $path;
        
        // Build absolute URL
        $url = $scheme . '://' . $host . $portString . $storagePath;

        return response()->json([
            'url' => $url,
            'id' => pathinfo($filename, PATHINFO_FILENAME),
            'filename' => $file->getClientOriginalName(),
        ], 201);
    }
}

