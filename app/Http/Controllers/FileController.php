<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function store($model, $folder, $file, $table = null, $disk = "local")
    {
        $model_id = null;

        if ($model == null) {
            $model = DB::table($table)->latest('id')->first();

            if ($model != null) {
                $model_id = $model->id;
            } else {
                $model_id = 1;
            }
        } else {
            $model_id = $model->id;
        }

        $prependFileName = $folder . "_" . $model_id . "_";
        $base64 = $this->getFileDataFromBase64($file);

        $fileName = $prependFileName . $file['name'];

        $fileName = str_replace('/', '_', $fileName);

        Storage::disk($disk)->put("$folder/$fileName", base64_decode($base64));

        return $fileName;
    }

    public function downloadFile(Request $request)
    {
        if (!Storage::exists("$request->folder/$request->fileName")) {
            return response()->json(['error' => 'File not found'], 404);
        }

        return Storage::download("$request->folder/$request->fileName");
    }

    public function downloadFileFromDB(Request $request)
    {
        $model = $request->model::find($request->id);

        return response()->json([
            'fileData' => $model->file,
            'fileName' => $model->file_name,
        ], 200);
    }

    public function deleteFile($folder, $fileName)
    {
        if (Storage::exists("$folder/$fileName")) {
            Storage::delete("$folder/$fileName");
        }

        return 'file deleted';
    }

    private function getFileDataFromBase64($file)
    {
        $file_data = explode(',', $file['base64'])[1];

        return $file_data;
    }
}
