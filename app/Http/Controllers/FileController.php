<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFolderRequest;
use App\Http\Resources\FileResource;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use PhpParser\Node\Expr\New_;

class FileController extends Controller
{
    public function myFiles()
    {

        $folder = $this->getRoot();
        $files = File::query()
            ->where('parent_id', $folder->id)
            ->where('created_by', Auth::id())
            ->orderBy('id_folder', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
            $files = FileResource::collection($files);


        return Inertia::render('MyFiles', compact('files'));
        //return Inertia::render('MyFiles', ['files' => $files]);
    }

    public function createFolser(StoreFolderRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;
        

        if (!$parent) {
            $parent = $this->getRoot();
        }

        // dd($data);

        $file = New File();
        $file -> id_folder = 1;
        $file->name = $data['name'];

        $parent->appendNode($file);
    }

    private function getRoot()
    {
        return File::query()->whereIsRoot()->where('created_by', Auth::id())->firstOrFail();
    }
}
