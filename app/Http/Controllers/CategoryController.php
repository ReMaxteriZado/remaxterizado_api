<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $categories = Category::where('name', 'like', "%$request->search%")
            ->orWhereHas('relatedCategories', function ($query) use ($request) {
                $query->where('name', 'like', "%$request->search%");
            })
            ->with('relatedCategories', 'links', 'parent')
            ->get();

        return response()->json(['categories' => $categories]);
    }

    private function validateCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3|max:255',
            'category_id' => $request->category_id != null ? Rule::exists('categories', 'id') : '',
        ], [
            'category_id.exists' => 'The parent category does not exists',
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validateCategory($request);

        try {
            DB::beginTransaction();

            // create category
            $category = new Category();

            $category->name = $request->name;
            $category->parent_id = $request->category_id;

            $category->save();

            DB::commit();

            return response()->json([
                'message' => 'Category created successfully',
                'category' => $category,
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
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
        $this->validateCategory($request);

        if ($request->category_id != null && Category::find($request->category_id)->parent_id == $id) {
            return response()->json([
                'name' => 'The parent category cannot be a child category',
            ], 400);
        }

        if ($request->category_id != null && $id == $request->category_id) {
            return response()->json([
                'name' => 'The parent category cannot be itself',
            ], 400);
        }

        try {
            DB::beginTransaction();

            $category = Category::find($id);

            $category->name = $request->name;
            $category->parent_id = $request->category_id;

            $category->save();

            DB::commit();

            return response()->json([
                'message' => 'Category updated successfully',
                'category' => $category,
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
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

            $category = Category::find($id);

            $links = $category->links;

            foreach ($links as $link) {
                if ($category->parent_id != null) {
                    $link->category_id = $category->parent_id;
                } else {
                    $link->category_id = null;
                }

                $link->save();
            }

            $category->delete();

            DB::commit();

            return response()->json([
                'message' => 'Category deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
