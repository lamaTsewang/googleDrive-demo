<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\StoreFolderRequest;
use App\Http\Requests\StoreFileRequest;
use App\Models\File;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\FileActionRequest;
use Illuminate\Support\Facades\Storage;

use App\Http\Resources\FileResource;


class FileController extends Controller
{
    public function myFiles(Request $request, string $folder = null)
    {
       
    //   echo phpinfo();
        if ($folder) {
            $folder = File::query()
                ->where('created_by', Auth::id())
                ->where('path', $folder)
                ->firstOrFail();
        }
        if (!$folder) {
            $folder = $this->getRoot();
        }

    // dd($folder);
        $files = File::query()
        ->where('parent_id', $folder->id)
        ->where('created_by', Auth::id())
        ->orderBy('is_folder','desc')
        ->orderBy('created_at','desc')
        ->paginate(10);
         
        $files = FileResource::collection($files);


        if ($request->wantsJson()) {
            return $files;
        }

        $ancestors = FileResource::collection([...$folder->ancestors, $folder]);

        $folder = new FileResource($folder);

        return Inertia::render('MyFiles', compact('files', 'folder','ancestors'));

    }
    public function createFolder(StoreFolderRequest $request)
    {
        $data = $request->validated();
        $parent = $request->parent;

        if (!$parent) {
            $parent = $this->getRoot();
        }

        $file = new File();
        $file->is_folder = 1;
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
                /** @var \Illuminate\Http\UploadedFile $file */
               $path = $file->store('/files/'.$user->id);
                $model = new File();
                $model->storage_path = $path;
                $model->is_folder = false;
                $model->name = $file->getClientOriginalName();
                $model->mime = $file->getMimeType();
                $model->size = $file->getSize();
                $parent->appendNode($model);
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
                $folder->is_folder = 1;
                $folder->name = $name;

                $parent->appendNode($folder);
                $this->saveFileTree($file, $folder, $user);
            } else {

                $path = $file->store('/files/'.$user->id);
                $model = new File();
                $model->storage_path = $path;
                $model->is_folder = false;
                $model->name = $file->getClientOriginalName();
                $model->mime = $file->getMimeType();
                $model->size = $file->getSize();
                $parent->appendNode($model);
            }
        }
    }

    public function destory(FileActionRequest $request){

        $data = $request->validated();

        $parent = $request->parent;

        if($data['all']) {

            $children = $parent->childrean;

            foreach ($children as $child) {
                $child->delete();
            }
        }
        else{
            foreach($data ['ids'] ?? [] as $id){
                $file = File::find($id);
                if ($file){
                    $file->delete();
                }
                
            }
        }

        return to_route('myFiles', ['folder' => $parent->path]);

        
    }

    public function download(FileActionRequest $request){

        $data =$request->validated();

        $parent = $request->parent;

        $all = $data['all'] ?? false;

        $ids = $data['ids'] ?? [];

        if(!$all && !$ids){
            return [
                'message' => 'Please select at least one file'
            ];
        }
        if($all){
           $url =  $this->createZip($parent->children);
           $filename = $parent->name.'.zip';
        }else{
            if(count($ids)==1){
                $file = File::find($ids[0]);
                if($file->is_folder){
                    if($file->children->count() == 0){
                        return[
                            'message' => 'Folder is empty'
                        ];

                    }
                    $url = $this->createZip($file->children);
                    $filename = $file->name.'.zip';
                }else{
                    $dest = 'public/' . pathinfo($file->storage_path, PATHINFO_BASENAME );
                    Storage::copy($file->storage_path, $dest);
                    
                    $url = asset(Storage::url($dest));
                    $filename = $file->name;
                }
            }else {
                $file = File::query()->whereIn('id', $ids)->get();
                $url = $this->createZip($files);
                $filename = $parent->name . '.zip';
            }
        }
        return [
            'url' => $url,
            'filename' => $filename
        ];
        
    }

    public function createZip($files): string{

        $zipPath = 'zip/'.Str::random(40).'.zip';
        $publicPath = "public/$zipPath";

        if(!is_dir(dirname($publicPath))){
            Storage::makeDirectory(dirname($publicPath));
        }

        $zipFile = Storage::path($publicPath);

        $zip = new \ZipArchive();

        if($zip->open($zipFile, \ZipArchieve::CREATE | \ZipArchive::OVERWRITE) == true){
            $this->addFilesToZip($files, $zip);
        }

        $zip->close();

        return asset(Storage::url($publicPath));
    }

    private function addFilesToZip($zip, $files, $ancestors = '')
    {
        foreach($files as $file){
            if($file->is_folder){
                $this->addFilesToZip($zip, $file->children, $ancestors . $file->name . '/');
            }else{
                $zip->addFile(Storage::path($file->storage_path), $ancestors. $file->name);
            }
        }
    }
}
