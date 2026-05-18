<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Storage;


trait UploadTrait{

    public function upload_file($file, $filePath)
    {
    if (!$file || !$file->isValid()) {
        return null;
    }

    $originalName = $file->getClientOriginalName();
    $path = $filePath . '/' . $originalName;

    // إذا الملف موجود مسبقاً
    if (Storage::disk('public')->exists($path)) {
        return $path;
    }

    // إذا غير موجود
    return $file->storeAs($filePath, $originalName, 'public');
    }

    public function upload_files($files, $filePath)
    {
    if (!is_array($files)) {
        return [];
    }

    $uploadedFiles = [];

    foreach ($files as $file) {
        if (!$file->isValid()) {
            continue;
        }

        $originalName = $file->getClientOriginalName();
        $path = $filePath . '/' . $originalName;

        // إذا الملف موجود
        if (Storage::disk('public')->exists($path)) {
            $uploadedFiles[] = $path;
            continue;
        }

        // إذا غير موجود
        $uploadedFiles[] = $file->storeAs($filePath, $originalName, 'public');
    }

    return $uploadedFiles;
    }

    public function delete_file($filePath)
    {
        if ($filePath && Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->delete($filePath);
        }
        return false;
    }

    public function delete_files($filePaths)
    {
        if (!is_array($filePaths)) {
            return false;
        }

        foreach ($filePaths as $filePath) {
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
        }
        return true;
    }



}
