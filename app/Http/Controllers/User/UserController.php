<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class UserController extends Controller
{

    const PHOTO_PROFILE_PATH = '/storage/uploads/users/profile-photo/';

    public function show(User $user)
    {
        return response()->json([
            'status' => 'success',
            'data' => compact('user')
        ]);
    }

    public function update(User $user, UserUpdateRequest $request)
    {
        try {

            // Check if request contains file (for updating user image)
            if ($request->hasFile('photo-update')) {

                $randomName = 'photo-' . Str::random(10) . '.' . $request->file('photo-update')->getClientOriginalExtension();
                $request->file('photo-update')->move(public_path(self::PHOTO_PROFILE_PATH), $randomName);

                // Delete old photo
                if ($user->photo !== 'default-user-photo.png') {
                    if (File::exists(public_path(self::PHOTO_PROFILE_PATH) . $user->photo)) File::delete(public_path(self::PHOTO_PROFILE_PATH) . $user->photo);
                }

                $request->merge(['photo', $randomName]);
            }

            $user->update($request->only([
                'first_name',
                'last_name',
                'phone',
                'job',
                'photo'
            ]));

            $user->getAllPermissions();

            return response()->json([
                'status' => 'success',
                'data' => compact('user')
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage()
            ]);
        }
    }
}
