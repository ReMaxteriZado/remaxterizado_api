<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $pagination = json_decode($request->pagination);

        $roles = Role::with('permissions');

        if (!empty($request->name)) {
            $roles->where('name', 'like', "%$request->name%");
        }

        if (!empty($request->slug)) {
            $roles->where('slug', 'like', "%$request->slug%");
        }

        $total = $roles->count();

        if (@$pagination->rows != 0) {
            $roles = $roles->skip($pagination->rows * $pagination->currentPage)->take($pagination->rows);
        }

        $roles = $roles->get();

        return response()->json(['roles' => $roles, 'total' => $total]);
    }

    private function valid(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:255',
            'slug' => 'required|min:3|max:255',
            'description' => 'nullable|min:3',
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

            $role = new Role();

            $role->fill($request->only($role->getFillable()));

            $role->save();

            foreach ($request->permissions as $permission) {
                $rolePermission = new RolePermission();

                $rolePermission->role_id = $role->id;
                $rolePermission->permission_id = $permission['id'];
                $rolePermission->create = @$permission['create'] ? $permission['create'] : false;
                $rolePermission->read = @$permission['read'] ? $permission['read'] : false;
                $rolePermission->update = @$permission['update'] ? $permission['update'] : false;
                $rolePermission->delete = @$permission['delete'] ? $permission['delete'] : false;

                $rolePermission->save();
            }

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

            $role = Role::findOrFail($id);

            $role->fill($request->only($role->getFillable()));

            $role->save();

            RolePermission::where('role_id', $role->id)->delete();

            foreach ($request->permissions as $permission) {
                $rolePermission = new RolePermission();

                $rolePermission->role_id = $role->id;
                $rolePermission->permission_id = $permission['id'];
                $rolePermission->create = @$permission['create'] ? $permission['create'] : false;
                $rolePermission->read = @$permission['read'] ? $permission['read'] : false;
                $rolePermission->update = @$permission['update'] ? $permission['update'] : false;
                $rolePermission->delete = @$permission['delete'] ? $permission['delete'] : false;

                $rolePermission->save();
            }

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

            $role = Role::findOrFail($id);

            $role->delete();

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

            $roles = Role::whereIn('id', $request->ids)->get();

            foreach ($roles as $role) {
                $role->delete();
            }

            DB::commit();

            return response()->json([
                'message' => 'Role deleted successfully',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
