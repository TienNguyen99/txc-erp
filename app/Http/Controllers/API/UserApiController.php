<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use Illuminate\Validation\Rule;

class UserApiController extends Controller
{
    public function index(Request $request)
    {
        $perPage = min((int) $request->integer('per_page', 50), 200);

        return UserResource::collection(User::query()->latest()->paginate($perPage));
    }

    public function show($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return new UserResource($user);
    }

    public function store(Request $request)
    {
        $user = User::create($this->validatedData($request));
        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->update($this->validatedData($request, $user->id));
        return new UserResource($user);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }

    private function validatedData(Request $request, ?int $userId = null): array
    {
        return $request->validate([
            'name' => [$userId ? 'sometimes' : 'required', 'string', 'max:255'],
            'email' => [
                $userId ? 'sometimes' : 'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => [$userId ? 'sometimes' : 'required', 'string', 'min:8'],
        ]);
    }
}
