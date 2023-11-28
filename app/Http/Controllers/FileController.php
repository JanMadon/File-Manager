<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToFavoritesRequest;
use App\Http\Requests\FileActionRequest;
use App\Http\Requests\ShareFileRequest;
use App\Http\Requests\StoreFileRequest;
use App\Http\Requests\StoreFolderRequest;
use App\Http\Requests\TrashFileRequest;
use App\Http\Resources\FileResource;
use App\Mail\ShareFilesMail;
use App\Models\File;
use App\Models\FileShare;
use App\Models\StarredFile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use PhpParser\Node\Expr\New_;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function myFiles(Request $request, string $folder = null)
    {
        // echo(phpinfo());
        // exit;
        if ($folder) {
            $folder = File::query()
                ->where('created_by', Auth::id())
                ->where('path', $folder)
                ->firstOrFail(); // zapamietaj
        }

        if (!$folder) {
            $folder = $this->getRoot();
        }

        $favorites = (int) $request->get('favorites');


        $files = File::query()
            ->select('files.*')
            ->with('starred')
            ->where('parent_id', $folder->id)
            ->where('created_by', Auth::id())
            ->orderBy('id_folder', 'desc')
            ->orderBy('files.created_at', 'desc')
            ->orderBy('files.id', 'desc');


        if ($favorites === 1) {
            $files->join('starred_files', 'starred_files.file_id', 'files.id')
                ->where('starred_files.user_id', Auth::id());
        }

        $files = $files->paginate(10);

        $files = FileResource::collection($files);

        if ($request->wantsJson()) {
            return $files;
        }

        $ancestors = FileResource::collection([...$folder->ancestors, $folder]);

        $folder = new FileResource($folder);

        return Inertia::render('MyFiles', compact('files', 'folder', 'ancestors'));
        //return Inertia::render('MyFiles', ['files' => $files]);
    }

    public function trash(Request $request)
    {
        $files = File::onlyTrashed()
            ->where('created_by', Auth::id())
            ->orderBy('id_folder', 'desc')
            ->orderBy('deleted_at', 'desc')
            ->paginate(10);

        $files = FileResource::collection($files);

        if ($request->wantsJson()) {
            return $files;
        }

        return Inertia::render('Trash', compact('files'));
    }

    public function createFolser(StoreFolderRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;


        if (!$parent) {
            $parent = $this->getRoot();
        }

        // dd($data);

        $file = new File();
        $file->id_folder = 1;
        $file->name = $data['name'];

        $parent->appendNode($file);
    }

    public function store(StoreFileRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;
        $user = $request->user();
        $fileTree = $request->file_tree;
        if (!$parent) {
            $parent = $this->getRoot();
        }

        if (!empty($fileTree)) {
            $this->saveFileTree($fileTree, $parent, $user);
        } else {
            foreach ($data['files'] as $file) {

                /** @var Illuminate\Http\UploadedFile $file*/

                $this->saveFile($file, $user, $parent);
            }
        }
    }

    private function getRoot()
    {
        return File::query()->whereIsRoot()->where('created_by', Auth::id())->firstOrFail();
    }

    public function saveFileTree($fileTree, $parent, $user)
    {
        foreach ($fileTree as $name => $file) {
            if (is_array($file)) {
                $folder = new File();
                $folder->id_folder = 1;
                $folder->name = $name;

                $parent->appendNode($folder);
                $this->saveFileTree($file, $folder, $user);
            } else {
                $this->saveFile($file, $user, $parent);
            }
        }
    }

    public function destroy(FileActionRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;
        if ($data['all']) {
            $children = $parent->children;

            foreach ($children as $child) {
                $child->moveToTrash();
            }
        } else {
            foreach ($data['ids'] as $id) {
                $file = File::find($id);
                if ($file) {
                    $file->moveToTrash();
                }
            }
        }

        return to_route('myFiles', ['folder' => $parent->path]);
    }
    public function download(FileActionRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;

        $all = $data['all'] ?? false;
        $ids = $data['ids'] ?? [];

        if (!$all && empty($ids)) {
            return [
                'message' => 'Please select files do download'
            ];
        }

        if ($all) {
            $url = $this->createZip($parent->children);
            $filename = $parent->name . '.zip';
        } else {
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
                    $dest = 'public/' . pathinfo($file->storage_path, PATHINFO_BASENAME);
                    Storage::copy($file->storage_path, $dest);

                    $url = asset(Storage::url($dest));
                    $filename = $file->name;
                }
            } else {
                $files = File::query()->whereIn('id', $ids)->get();
                $url = $this->createZip($files);
                $filename = $parent->name . '.zip';
            }
        }

        return [
            'url' => $url,
            'filename' => $filename
        ];
    }

    private function saveFile($file, $user, $parent): void
    {
        $path = $file->store('/files' . $user->id);

        $model = new File();
        $model->storage_path = $path;
        $model->id_folder = false;
        $model->name = $file->getClientOriginalName();
        $model->mime = $file->getMimeType();
        $model->size = $file->getSize();

        $parent->appendNode($model);
    }

    private function createZip($files): string
    {
        $zipPath = 'zip/' . Str::random() . 'zip';
        $publicPath = "public/$zipPath";

        if (!is_dir(dirname($publicPath))) {
            Storage::makeDirectory(dirname($publicPath));
        }

        $zipFile = Storage::path($publicPath);

        $zip = new \ZipArchive();

        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) == true) {
            $this->addFilesToZip($zip, $files);
        }

        $zip->close();

        return asset(Storage::url($zipPath));
    }

    private function addFilesToZip($zip, $files, $ancestors = '')
    {
        foreach ($files as $file) {
            if ($file->id_folder) {
                $this->addFilesToZip($zip, $file->children, $ancestors . $file->name . '/');
            } else {
                $zip->addFile(Storage::path($file->storage_path), $ancestors . $file->name);
            }
        }
    }

    public function restore(TrashFileRequest $request)
    {
        $data = $request->validated();
        if ($data['all']) {
            $children = File::onlyTrashed()->get();
            foreach ($children as $child) {
                $child->resore();
            }
        } else {
            $ids = $data['ids'] ?? [];
            $children = File::onlyTrashed()->whereIn('id', $ids)->get();
            foreach ($children as $child) {
                $child->restore();
            }
        }

        return to_route('trash');
    }

    public function deleteForever(TrashFileRequest $request)
    {
        $data = $request->validated();
        if ($data['all']) {
            $children = File::onlyTrashed()->get();
            foreach ($children as $child) {
                $child->deleteForever();
            }
        } else {
            $ids = $data['ids'] ?? [];
            $children = File::onlyTrashed()->whereIn('id', $ids)->get();
            foreach ($children as $child) {
                $child->deleteForever();
            }
        }

        return to_route('trash');
    }

    public function addToFavorites(AddToFavoritesRequest $request)
    {

        $data = $request->validated();

        $id = $data['id'];
        $file = File::find($id);
        $user_id = Auth::id();

        $starredFile = StarredFile::query()
            ->where('file_id', $file->id)
            ->where('user_id', $user_id)
            ->first();


        if ($starredFile) {
            $starredFile->delete();
        } else {
            StarredFile::create([
                'file_id' => $file->id,
                'user_id' => $user_id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
        return redirect()->back();
    }

    public function share(ShareFileRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;

        $all = $data['all'] ?? false;
        $email = $data['email'] ?? false;
        $ids = $data['ids'] ?? [];

        if (!$all && empty($ids)) {
            return [
                'message' => 'Please select files do share'
            ];
        }

        $user = User::query()->where('email', $email)->first();

        if(!$user){
            return redirect()->back();
        }


        if($all){
            $files = $parent->children;
        } else {
            $files = File::find($ids);
        }

        $data = [];
        $ids = Arr::pluck($files, 'id');
        $existingFileIds = FileShare::query()
                        ->whereIn('file_id', $ids)
                        ->where('user_id', $user->id)
                        ->get()
                        ->keyBy('file_id');

        foreach($files as $file) {
            if($existingFileIds->has($file->id)) {
                continue;
            }
            $data[] =  [
                'file_id' => $file->id,
                'user_id' => $user->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

  // send email address
        Mail::to($user)->send(new ShareFilesMail($user, Auth::user(), $files));

        FileShare::insert($data);

        return redirect()->back();
    }
}
