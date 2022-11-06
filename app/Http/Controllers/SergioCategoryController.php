<?php

namespace App\Http\Controllers;

use App\Models\SergioCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SergioCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $pagination = json_decode($request->pagination);

        $sergioCategories = SergioCategory::query();

        if (!empty($request->name)) {
            $sergioCategories->where('name', 'like', "%$request->name%");
        }

        $total = $sergioCategories->count();

        if ($pagination->rows != 0) {
            $sergioCategories = $sergioCategories->skip($pagination->rows * $pagination->currentPage)->take($pagination->rows);
        }

        $sergioCategories = $sergioCategories->get();

        return response()->json(['sergioCategories' => $sergioCategories, 'total' => $total]);
    }

    private function valid(Request $request, $id = null)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:255',
            'image' => $id == null ? 'required' : 'nullable',
        ]);

        return $validator;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->valid($request);

        if ($validator->errors()->any()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $sergioCategory = new SergioCategory();

            $sergioCategory->fill($request->only($sergioCategory->getFillable()));

            $fileController = new FileController();

            $fileName = $fileController->store(null, 'sergioCategories', $request->image[0], 'sergio_categories');

            $sergioCategory->image = $fileName;

            $sergioCategory->save();

            DB::commit();
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = $this->valid($request, $id);

        if ($validator->errors()->any()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $sergioCategory = SergioCategory::findOrFail($id);

            $fileController = new FileController();

            if ($request->image) {
                $fileController->deleteFile('sergioCategories', $sergioCategory->image);

                $fileName = $fileController->store($sergioCategory, 'sergioCategories', $request->image[0]);

                $sergioCategory->image = $fileName;
            }

            $sergioCategory->fill($request->only($sergioCategory->getFillable()));

            $sergioCategory->save();

            DB::commit();
            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $sergioCategory = SergioCategory::findOrFail($id);

            $fileController = new FileController();

            $fileController->deleteFile('sergioCategories', $sergioCategory->image);

            $sergioCategory->delete();

            DB::commit();

            return response()->json(['success' => true], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroyMultiple(Request $request)
    {
        try {
            DB::beginTransaction();

            $fileController = new FileController();

            $sergioCategories = SergioCategory::whereIn('id', $request->ids)->get();

            foreach ($sergioCategories as $sergioCategory) {
                $fileController->deleteFile('sergioCategories', $sergioCategory->image);
                
                $sergioCategory->delete();
            }

            DB::commit();

            return response()->json([
                'message' => 'SergioCategory deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
