<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserUpdateRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{

    const PHOTO_PROFILE_PATH = '/storage/uploads/users/profile-photo/';
    const DEFAULT_PHOTO_NAME = 'default-profile-picture.svg';

    public function index(Request $request)
    {
        $searchQuery = $request->query('search');
        $query = User::where('email', 'LIKE', '%' . $searchQuery . '%')
            ->orWhere('phone', 'LIKE', '%' . $searchQuery . '%')
            ->withCount(['project' => function($query) {
                $query->whereHas('order', function ($query) {
                    $query->where('expired_at', '>', now());
                });
            }]);
        if ($request->sort === 'project_count') {
            $query->orderBy('project_count', 'desc');
        }
        $query->orderBy('last_login', 'desc');
        $users = $query->paginate($request->query('limit', 15));
        return response()->json([
            'status' => 'success',
            'data' => [
                'users' => $users->items(),
                'pagination_attribute' => [
                    'total_page' => $users->lastPage(),
                    'total_data' => $users->total()
                ]
            ]
        ]);
    }

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
                if ($user->photo !== self::DEFAULT_PHOTO_NAME) {
                    if (File::exists(public_path(self::PHOTO_PROFILE_PATH) . $user->photo)) File::delete(public_path(self::PHOTO_PROFILE_PATH) . $user->photo);
                }

                $user->photo = $randomName;
                $user->save();
            }

            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
                $user->update();
            }

            $user->update($request->only([
                'first_name',
                'last_name',
                'phone',
                'email',
                'job',
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
