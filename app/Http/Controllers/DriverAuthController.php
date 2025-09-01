<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class DriverAuthController extends Controller
{
    /**
     * Register a new driver
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:drivers',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'license_number' => 'required|string|unique:drivers',
        ]);

        $driver = Driver::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'license_number' => $request->license_number,
            'status' => 'active',
        ]);

        $token = $driver->createToken('driver-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Driver registered successfully',
            'driver' => $driver,
            'token' => $token,
        ], 201);
    }

    /**
     * Login driver
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $driver = Driver::where('email', $request->email)->first();

        if (!$driver || !Hash::check($request->password, $driver->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($driver->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Driver account is ' . $driver->status,
            ], 403);
        }

        // Revoke all existing tokens
        $driver->tokens()->delete();

        $token = $driver->createToken('driver-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Driver logged in successfully',
            'driver' => $driver,
            'token' => $token,
        ]);
    }

    /**
     * Logout driver
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Driver logged out successfully',
        ]);
    }

    /**
     * Logout from all devices
     */
    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Driver logged out from all devices successfully',
        ]);
    }

    /**
     * Get current driver profile
     */
    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'driver' => $request->user(),
        ]);
    }

    /**
     * Update driver profile
     */
    public function updateProfile(Request $request)
    {
        $driver = $request->user();

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:drivers,email,' . $driver->id,
            'phone' => 'nullable|string|max:20',
            'license_number' => 'sometimes|required|string|unique:drivers,license_number,' . $driver->id,
        ]);

        $driver->update($request->only(['name', 'email', 'phone', 'license_number']));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'driver' => $driver,
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $driver = $request->user();

        if (!Hash::check($request->current_password, $driver->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $driver->update([
            'password' => Hash::make($request->password),
        ]);

        // Revoke all tokens except current
        $driver->tokens()->where('id', '!=', $request->user()->currentAccessToken()->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
        ]);
    }
}
