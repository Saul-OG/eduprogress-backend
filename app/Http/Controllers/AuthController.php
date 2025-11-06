<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|max:120',
            'email'=> 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email'=> $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => false,
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json(['user'=>$user,'access_token'=>$token], 201);
    }

    public function login(Request $r)
    {
        $data = $r->validate([
            'username'=> 'required|email',
            'password' => 'required|string'
        ]);

        $user = User::where('email',$data['username'])->first();

        if(!$user || !Hash::check($data['password'], $user->password)){
            return response()->json(['message'=>'Credenciales inválidas'], 401);
        }
        $token = $user->createToken('api')->plainTextToken;

        return response()->json(['user'=>$user,'access_token'=>$token]);
    }

    public function logout(Request $r)
    {
        $r->user()->currentAccessToken()->delete();
        return response()->json(['message'=>'Sesión cerrada']);
    }

    public function me(Request $r)
    {
        return response()->json($r->user());
    }

    // placeholders
    public function forgotPassword() { return response()->json(['ok'=>true]); }
    public function resetPassword()  { return response()->json(['ok'=>true]); }
}
