<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    // GET /api/users?q=nama
    public function index(Request $request)
    {
        $query = User::withCount(['posts', 'comments']);

        if ($request->has('q')) {
            $keyword = $request->input('q');
            $query->where('name', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%");
        }

        return response()->json($query->orderBy('id', 'DESC')->paginate(10), 200);
    }

    // GET /api/users/{id}
    public function show($id)
    {
        $user = User::with('posts:id,user_id,title,status')->withCount('comments')->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user, 200);
    }

    // POST /api/users
    public function store(Request $request)
    {
        $this->validate($request, [
            'name'     => 'required|max:100',
            'email'    => 'required|email|max:100|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => app('hash')->make($request->input('password')),
        ]);

        return response()->json(['message' => 'User created', 'result' => $user], 201);
    }

    // PUT /api/users/{id}
    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $this->validate($request, [
            'name'     => 'max:100',
            'email'    => 'email|max:100|unique:users,email,' . $id,
            'password' => 'min:6',
        ]);

        $data = $request->only(['name', 'email']);

        if ($request->has('password')) {
            $data['password'] = app('hash')->make($request->input('password'));
        }

        $user->update($data);

        return response()->json(['message' => 'User updated', 'result' => $user], 200);
    }

    // DELETE /api/users/{id}
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User deleted'], 200);
    }
}
