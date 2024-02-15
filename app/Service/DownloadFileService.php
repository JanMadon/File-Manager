<?php

namespace App\Service;

use App\Models\File;
use Illuminate\Support\Facades\Storage;

class DownloadFileService {

    protected function getDownloadUrl($ids, $zipName)
    {
        if (count($ids) === 1) {
            $file = File::find($ids[0]);
            if ($file->id_folder) {
                if ($file->children->count() == 0) {
                    return [
                        'message' => 'The folder is empty'
                    ];
                }
                $url = $this->createZip($file->children);
                $filename = $file->name . '.zip';
            } else {
                $dest = pathinfo($file->storage_path, PATHINFO_BASENAME);
                if ($file->uploaded_on_cloud) {
                    $content = Storage::get($file->storage_path);
                } else {
                    $content = Storage::disk('local')->get($file->storage_path);
                }
                Storage::disk('public')->put($dest, $content);
                $url = asset(Storage::disk('public')->url($dest));
                $filename = $file->name;
            }
        } else {
            $files = File::query()->whereIn('id', $ids)->get();
            $url = $this->createZip($files);
            $filename = $zipName . '.zip';
        }

        return [$url, $filename];
    }
}