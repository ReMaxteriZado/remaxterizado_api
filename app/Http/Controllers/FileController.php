<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function store($model, $folder, $file)
    {
        $prependFileName = $folder . "_" . $model->id . "_";
        $base64 = $this->getFileDataFromBase64($file);

        $fileName = $prependFileName . $file['name'];

        Storage::put("$folder/$fileName", base64_decode($base64));

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

    private function getFileDataFromBase64($file)
    {
        $file_data = explode(',', $file['base64'])[1];

        return $file_data;
    }
}
