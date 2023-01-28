<?php

namespace App\Http\Controllers;

use App\Models\Code;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $pagination = json_decode($request->pagination);

        $codes = Code::with('link.category');

        if (!empty($request->title)) {
            $codes->whereHas('link', function ($query) use ($request) {
                $query->where('title', 'like', "%$request->title%")
                    ->orWhere('link', 'like', "%$request->title%");
            });
        }

        if (!empty($request->link)) {
            $codes->whereHas('link', function ($query) use ($request) {
                $query->where('title', 'like', "%$request->link%")
                    ->orWhere('link', 'like', "%$request->link%");
            });
        }

        if (!empty($request->category)) {
            $codes->whereHas('link.category', function ($query) use ($request) {
                $query->where('name', 'like', "%$request->category%");
            });
        }

        if (!empty($request->language)) {
            $codes->where('language', $request->language);
        }

        $total = $codes->count();

        if (@$pagination->rows != 0) {
            $codes = $codes->skip($pagination->rows * $pagination->currentPage)->take($pagination->rows);
        }

        $codes = $codes->get();

        return response()->json(['codes' => $codes, 'total' => $total]);
    }

    private function valid(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'link_id' => 'required|exists:links,id',
            'language' => 'required|string|min:1',
            'comment' => 'nullable|min:3',
            'code' => 'required|min:1',
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

            $code = new Code();

            $code->fill($request->only($code->getFillable()));

            $code->save();

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

            $code = Code::findOrFail($id);

            $code->fill($request->only($code->getFillable()));

            $code->save();

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

            $code = Code::findOrFail($id);

            $code->delete();

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

            $codes = Code::whereIn('id', $request->ids)->get();

            foreach ($codes as $code) {
                $code->delete();
            }

            DB::commit();

            return response()->json([
                'message' => 'Code deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
