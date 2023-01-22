<?php

namespace App\Http\Controllers;

use App\Models\Code;
use App\Models\Link;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

        if (!empty($request->category)) {
            $links->whereHas('category', function ($query) use ($request) {
                $query->where('name', 'like', "%$request->category%");
            });
        }

        if (!empty($request->link)) {
            $links->where('link', 'like', "%$request->link%");
        }

        if (!empty($request->tags)) {
            $links->where('tags', 'like', "%$request->tags%");
        }

        $total = $links->count();

        if (@$pagination->rows != 0) {
            $links = $links->skip($pagination->rows * $pagination->currentPage)->take($pagination->rows);
        }

        $links = $links->get();

        return response()->json(['links' => $links, 'total' => $total]);
    }

    private function valid(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:3|max:255',
            'link' => 'required|url',
            'tags' => 'nullable|array|min:1',
            'category_id' => 'required|exists:categories,id',
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

            $link = new Link();

            $link->fill($request->only($link->getFillable()));
            $link->tags = $request->tags != null ? json_encode($request->tags) : null;

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
        $validator = $this->valid($request);

        if ($validator->errors()->any()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $link = Link::findOrFail($id);

            $link->fill($request->only($link->getFillable()));
            $link->tags = $request->tags != null ? json_encode($request->tags) : null;

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

            Code::where('link_id', $link->id)->delete();

            $link->delete();

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

            $links = Link::whereIn('id', $request->ids)->get();

            foreach ($links as $link) {
                $link->delete();
            }

            DB::commit();

            return response()->json([
                'message' => 'Link deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function incrementViews($id)
    {
        try {
            $link = Link::findOrFail($id);

            $link->views++;

            $link->save();

            return response()->json(['success' => true], 200);
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
