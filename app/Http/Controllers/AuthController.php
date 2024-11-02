<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // $validated = $request->validate([
        //     'name' => ['required','string','max:255'],
        //     'email' => ['required','string','email','max:255','unique:users'],
        //     'password' => ['required','string','min:8','confirmed'],
        // ]);

        //Using Validator for Validation
        $validated = Validator::make($request->all(),[
            'name' =>'required|string|max:255',
            'email' =>'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if($validated->fails()){
            return response()->json($validated->errors(), 403 );
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            //Generate user token
            $token = $user->createToken('auth_token')->plainTextToken;

            //Return user token
            return response()->json([
                'access_token' => $token,
               'message' => 'User registered successfully',
                'user' => $user,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
               'message' => 'Failed to register user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    //Login function for user
    public function login(Request $request)
    {
        $validated = Validator::make($request->all(),[
            'email' =>'required|string|email',
            'password' => 'required|string|min:8',
        ]);

        if($validated->fails()){
            return response()->json($validated->errors(), 403 );
        }
        $credentials = ['email' => $request->email, 'password' => $request->password];
        try {
            if (!auth()->attempt($credentials)){
                return response()->json([
                    'message' => 'Invalid credentials',
                 ], 401);
            }
            //Get the user data from the the database
            $user = User::where('email', $request->email)->firstOrFail();
            //Generate user token
            $token = $user->createToken('auth_token')->plainTextToken;

            //Return user token
            return response()->json([
                'access_token' => $token,
               'message' => 'User logged in successfully',
                'user' => $user,
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'Failed to login user',
                 'error' => $e->getMessage(),
             ], 500);
        }

    }
}
