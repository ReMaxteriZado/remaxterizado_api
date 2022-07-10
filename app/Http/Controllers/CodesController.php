<?php

namespace App\Http\Controllers;

use App\Models\Code;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CodesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $codes = Code::whereHas('link', function ($query) use ($request) {
            $query->where('title', 'like', "%$request->search%");
        })
            ->orWhereHas('link.category', function ($query) use ($request) {
                $query->where('name', 'like', "%$request->search%");
            })
            ->with('link.category')->get();

        return $codes;
    }

    private function validateCode(Request $request)
    {
        $request->validate([
            'code' => 'required|min:3',
            'code_language' => 'required',
            'link_id' => ['required', Rule::exists('links', 'id')],
        ], [
            'link_id.required' => 'The link is required',
            'link_id.exists' => 'The link does not exists',
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
        $this->validateCode($request);

        try {
            DB::beginTransaction();

            $code = new Code();

            $code->link_id = $request->link_id;
            $code->code = $request->code;
            $code->language = $request->code_language;
            $code->comment = $request->comment;

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
        $this->validateCode($request);

        try {
            DB::beginTransaction();

            $code = Code::findOrFail($id);

            $code->link_id = $request->link_id;
            $code->code = $request->code;
            $code->language = $request->code_language;
            $code->comment = $request->comment;

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
}
