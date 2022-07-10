<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LinksController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $links = Link::where('title', 'like', "%$request->search%")
            ->orWhere('link', 'like', "%$request->search%")
            ->orWhere('tags', 'like', "%$request->search%")
            ->orWhereHas('category', function ($query) use ($request) {
                $query->where('name', 'like', "%$request->search%");
            })->with('category.parent')->get();

        return $links;
    }

    private function validateLink(Request $request)
    {
        $request->validate([
            'title' => 'required|min:3|max:255',
            'link' => 'required|min:3|max:255|url',
            'category_id' => ['required', Rule::exists('categories', 'id')],
        ], [
            'category_id.required' => 'The category is required',
            'category_id.exists' => 'The category does not exists',
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validateLink($request);

        try {
            DB::beginTransaction();

            $link = new Link();

            $link->title = $request->title;
            $link->category_id = $request->category_id;
            $link->link = $request->link;
            $link->tags = json_encode($request->tags);

            $link->save();

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
        $this->validateLink($request);
        
        try {
            DB::beginTransaction();

            $link = Link::findOrFail($id);

            $link->title = $request->title;
            $link->category_id = $request->category_id;
            $link->link = $request->link;
            $link->tags = json_encode($request->tags);

            $link->save();

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

            $link = Link::findOrFail($id);

            $link->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function incrementViews($id)
    {
        try {
            $link = Link::findOrFail($id);
    
            $link->views = $link->views + 1;
    
            $link->save();
    
            return response()->json(['success' => true], 200);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
