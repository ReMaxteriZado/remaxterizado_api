<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $pagination = json_decode($request->pagination);

        $users = User::with('role.permissions.permission');

        if (!empty($request->name)) {
            $users->where('name', 'like', "%$request->name%");
        }

        if (!empty($request->email)) {
            $users->where('email', 'like', "%$request->email%");
        }

        if (!empty($request->role)) {
            $users->whereHas('role', function ($query) use ($request) {
                $query->where('slug', 'like', "%$request->role%");
            });
        }

        $total = $users->count();

        if (@$pagination->rows != 0) {
            $users = $users->skip($pagination->rows * $pagination->currentPage)->take($pagination->rows);
        }

        $users = $users->get();

        return response()->json(['users' => $users, 'total' => $total]);
    }

    private function valid(Request $request, $id = null)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($id),
            ],
            'password' => $id == null ? 'required|min:6' : 'nullable|min:6',
            'role_id' => 'required|exists:roles,id',
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
        if (!$this->checkPermission($request->user()->id, "users", "create")) {
            return response()->json(['success' => false, 'message' => $this->noPermissionMessage], 403);
        }

        $validator = $this->valid($request);

        if ($validator->errors()->any()) {
            return response()->json($validator->errors(), 400);
        }

        try {
            DB::beginTransaction();

            $user = new User();

            $user->fill($request->only($user->getFillable()));

            $user->password = Hash::make($request->password);

            $user->save();

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

            $user = User::findOrFail($id);

            $user->fill($request->only($user->getFillable()));

            $user->password = Hash::make($request->password);

            $user->save();

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
    public function destroy(Request $request, $id)
    {
        if ($request->user()->id == $id) {
            return response()->json(['success' => false, 'message' => 'No puedes borrarte a ti mismo'], 500);
        }

        try {
            DB::beginTransaction();

            $user = User::findOrFail($id);

            $user->delete();

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

            $users = User::whereIn('id', $request->ids)->get();

            foreach ($users as $user) {
                if ($request->user()->id == $user->id) {
                    return response()->json(['success' => false, 'message' => 'No puedes borrarte a ti mismo'], 500);
                }

                $user->delete();
            }

            DB::commit();

            return response()->json([
                'message' => 'User deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
