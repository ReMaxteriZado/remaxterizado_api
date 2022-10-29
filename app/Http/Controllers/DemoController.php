<?php

namespace App\Http\Controllers;

use App\Models\Demo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DemoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $pagination = json_decode($request->pagination);

        $demos = Demo::query();

        if (!empty($request->title)) {
            $demos->where('title', 'like', "%$request->title%");
        }

        if (!empty($request->category)) {
            $demos->whereHas('category', function ($query) use ($request) {
                $query->where('name', 'like', "%$request->category%");
            });
        }

        if (!empty($request->date)) {
            $demos->whereDate('date', $request->date);
        }

        $total = $demos->count();

        if (@$pagination->rows != 0) {
            $demos = $demos->skip($pagination->rows * $pagination->currentPage)->take($pagination->rows);
        }

        $demos = $demos->get();

        return response()->json(['demos' => $demos, 'total' => $total]);
    }

    private function valid(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|min:3|max:255',
            'demo' => 'required|url',
            'category_id' => 'required|integer|exists:categories,id',
            'new_form' => 'required',
            'new_form.*.name' => 'required|min:3|max:255',
            'new_form.*.lastname' => 'required|min:3|max:255',
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

            $demo = new Demo();

            $demo->fill($request->only($demo->getFillable()));
            $demo->tags = json_encode($request->tags);

            $demo->save();

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

            $demo = Demo::findOrFail($id);

            $demo->fill($request->only($demo->getFillable()));
            $demo->tags = json_encode($request->tags);

            $demo->save();

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

            $demo = Demo::findOrFail($id);

            $demo->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function destroyMultiple(Request $request)
    {
        try {
            DB::beginTransaction();

            $demos = Demo::whereIn('id', $request->ids)->get();

            foreach ($demos as $demo) {
                $demo->delete();
            }

            DB::commit();

            return response()->json([
                'message' => 'Demo deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
