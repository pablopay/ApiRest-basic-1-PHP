<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/setup', function () {

    $credentials = [
        'email' => "admin@admin.com",
        'password' => 'password'
    ];

    if (!Auth::attempt($credentials)) {
        $user = new User();
        $user->name = 'Admin';
        $user->email = $credentials['email'];
        $user->password = Hash::make($credentials['password']);
        $user->save();
    }

    if (!Auth::attempt($credentials)) {
        return response()->json([
            'message' => 'Authentication failed for setup user.',
        ], 401);
    }

    /** @var User|null $user */
    $user = Auth::user();

    if (!$user instanceof User) {
        return response()->json([
            'message' => 'Authenticated user could not be resolved.',
        ], 500);
    }

    $adminToken = $user->createToken('admin-token', ['create', 'update', 'delete']);
    $updateToken = $user->createToken('update-token', ['create', 'update']);
    $basicToken = $user->createToken('basic-token');

    return [
        'admin' => $adminToken->plainTextToken,
        'update' => $updateToken->plainTextToken,
        'basic' => $basicToken->plainTextToken,
    ];
});
