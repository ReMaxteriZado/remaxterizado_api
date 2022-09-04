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
        $pagination = json_decode($request->pagination);
        $links = Link::with('category.parent');

        if (!empty($request->title)) {
            $links->where('title', 'like', "%$request->title%");
        }

        if (!empty($request->tags)) {
            $links->where('tags', 'like', "%$request->tags%");
        }

        if (!empty($request->link)) {
            $links->where('link', 'like', "%$request->link%");
        }

        if (!empty($request->category)) {
            $links->whereHas('category', function ($query) use ($request) {
                $query->where('name', 'like', "%$request->category%");
            });
        }

        $total = $links->count();

        if ($pagination->rows != 0) {
            $links = $links->skip($pagination->rows * $pagination->currentPage)->take($pagination->rows);
        }

        $links = $links->get();

        return response()->json(['links' => $links, 'total' => $total]);
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
