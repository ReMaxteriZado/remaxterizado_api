<?php

namespace App\Http\Controllers;

use App\Models\SergioSubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SergioSubcategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $pagination = json_decode($request->pagination);

        $sergioSubCategories = SergioSubCategory::query();

        if (!empty($request->name)) {
            $sergioSubCategories->where('name', 'like', "%$request->name%");
        }

        $total = $sergioSubCategories->count();

        if ($pagination->rows != 0) {
            $sergioSubCategories = $sergioSubCategories->skip($pagination->rows * $pagination->currentPage)->take($pagination->rows);
        }

        $sergioSubCategories = $sergioSubCategories->get();

        return response()->json(['sergioSubCategories' => $sergioSubCategories, 'total' => $total]);
    }

    private function valid(Request $request, $id = null)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:255',
            'category_id' => 'required|exists:sergio_categories,id',
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

            $sergioSubCategory = new SergioSubCategory();

            $sergioSubCategory->fill($request->only($sergioSubCategory->getFillable()));

            $fileController = new FileController();

            $fileName = $fileController->store(null, 'sergioSubCategories', $request->image[0], 'sergio_categories');

            $sergioSubCategory->image = $fileName;

            $sergioSubCategory->save();

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

            $sergioSubCategory = SergioSubCategory::findOrFail($id);

            $fileController = new FileController();

            if ($request->image) {
                $fileController->deleteFile('sergioSubCategories', $sergioSubCategory->image);

                $fileName = $fileController->store($sergioSubCategory, 'sergioSubCategories', $request->image[0]);

                $sergioSubCategory->image = $fileName;
            }

            $sergioSubCategory->fill($request->only($sergioSubCategory->getFillable()));

            $sergioSubCategory->save();

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

            $sergioSubCategory = SergioSubCategory::findOrFail($id);

            $fileController = new FileController();

            $fileController->deleteFile('sergioSubCategories', $sergioSubCategory->image);

            $sergioSubCategory->delete();

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

            $sergioSubCategories = SergioSubCategory::whereIn('id', $request->ids)->get();

            foreach ($sergioSubCategories as $sergioSubCategory) {
                $fileController->deleteFile('sergioSubCategories', $sergioSubCategory->image);
                
                $sergioSubCategory->delete();
            }

            DB::commit();

            return response()->json([
                'message' => 'SergioSubCategory deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
