<?php

namespace App\Http\Controllers;

use App\Actions\UploadImage;
use App\Enums\UserType;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::where('id', '!=', 1)
            ->orderByRaw("CONCAT(firstname, ' ', lastname) ASC")
            ->get();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        $user = new User(['type' => UserType::STAFF->value]);

        return view('users.manage', compact('user'));
    }

    public function edit(User $user)
    {
        return view('users.manage', compact('user'));
    }

    public function store(StoreUserRequest $request, UploadImage $uploadImage): JsonResponse
    {
        $validated = $request->validated();
        $id = $validated['id'] ?? null;

        $usernameExists = User::where('username', $validated['username'])
            ->when($id, fn ($query) => $query->where('id', '!=', $id))
            ->exists();

        if ($usernameExists) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Username already exist.',
            ], 422);
        }

        $userData = collect($validated)->except(['id', 'password', 'img', 'oldpassword'])->all();
        $userData['type'] = (int) ($userData['type'] ?? UserType::STAFF->value);

        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        if ($request->hasFile('img')) {
            $file = $request->file('img');
            $ext = $file->getClientOriginalExtension() ?: 'png';
            $filename = 'avatar-' . ($id ?? time()) . '.' . $ext;
            $userData['avatar'] = $uploadImage->execute($file, $filename);
        }

        $user = User::updateOrCreate(['id' => $id], $userData);

        $message = empty($id)
            ? 'New User successfully saved.'
            : 'User Details successfully updated.';

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'id' => $user->id,
        ]);
    }

    public function update(StoreUserRequest $request, User $user, UploadImage $uploadImage): JsonResponse
    {
        $validated = $request->validated();
        $validated['id'] = $user->id;

        $request->merge(['id' => $user->id]);

        $usernameExists = User::where('username', $validated['username'])
            ->where('id', '!=', $user->id)
            ->exists();

        if ($usernameExists) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Username already exist.',
            ], 422);
        }

        $userData = collect($validated)->except(['id', 'password', 'img', 'oldpassword'])->all();
        $userData['type'] = (int) ($userData['type'] ?? UserType::STAFF->value);

        if (!empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        if ($request->hasFile('img')) {
            $file = $request->file('img');
            $ext = $file->getClientOriginalExtension() ?: 'png';
            $filename = 'avatar-' . $user->id . '-' . time() . '.' . $ext;
            $userData['avatar'] = $uploadImage->execute($file, $filename);
            $uploadImage->delete($user->avatar);
        }

        $user->fill($userData);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'User Details successfully updated.',
            'id' => $user->id,
        ]);
    }

    public function destroy(User $user, UploadImage $uploadImage): JsonResponse
    {
        if ($user->id == 1) {
            return response()->json([
                'status' => 'failed',
                'message' => 'The primary administrator account cannot be deleted.',
            ], 422);
        }

        $avatar = $user->avatar;

        $user->delete();

        $uploadImage->delete($avatar);

        return response()->json([
            'status' => 'success',
            'message' => 'User Details successfully deleted.',
        ]);
    }

}
