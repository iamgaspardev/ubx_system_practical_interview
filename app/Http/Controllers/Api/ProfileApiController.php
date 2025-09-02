<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileApiController extends Controller
{
    // Show user profile
    public function show(Request $request)
    {
        try {
            $user = $request->user();

            return response()->json([
                'success' => true,
                'message' => 'Profile retrieved successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'profile_image' => $user->profile_image ? asset('storage/' . $user->profile_image) : null,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Update user profile 

    public function update(Request $request)
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'device_id' => 'nullable|string|max:255',
                'current_password' => 'nullable|required_with:password',
                'password' => 'nullable|string|min:6|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Handle device ID tracking
            $deviceIds = $user->device_ids ?? [];
            $deviceIdCaptured = false;

            if ($request->filled('device_id')) {
                $newDeviceId = $request->device_id;
                if (!in_array($newDeviceId, $deviceIds)) {
                    $deviceIds[] = $newDeviceId;
                    $deviceIdCaptured = true;
                }
            }

            // Check for device ID from header (X-Device-ID)
            $headerDeviceId = $request->header('X-Device-ID');
            if ($headerDeviceId && !in_array($headerDeviceId, $deviceIds)) {
                $deviceIds[] = $headerDeviceId;
                $deviceIdCaptured = true;
            }

            // Verify current password if trying to change password
            if ($request->filled('password')) {
                if (!$request->filled('current_password')) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Current password is required to change password'
                    ], 422);
                }

                if (!Hash::check($request->current_password, $user->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Current password is incorrect'
                    ], 422);
                }
            }

            // Update user data (name only, email is immutable for security)
            $updateData = [
                'name' => $request->name,
                'device_ids' => $deviceIds,
                'last_active_at' => now(),
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            return response()->json([
                'success' => true,
                'message' => $deviceIdCaptured ?
                    'Profile updated successfully. Device registered.' :
                    'Profile updated successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'profile_image' => $user->profile_image ? asset('storage/' . $user->profile_image) : null,
                        'device_count' => count($deviceIds),
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Profile update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    //Update profile image 
    public function updateImage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
                'device_id' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();

            // Handle device ID tracking
            $deviceIds = $user->device_ids ?? [];
            $deviceIdCaptured = false;
            
            if ($request->filled('device_id')) {
                $newDeviceId = $request->device_id;
                if (!in_array($newDeviceId, $deviceIds)) {
                    $deviceIds[] = $newDeviceId;
                    $deviceIdCaptured = true;
                }
            }

            // Check for device ID from header
            $headerDeviceId = $request->header('X-Device-ID');
            if ($headerDeviceId && !in_array($headerDeviceId, $deviceIds)) {
                $deviceIds[] = $headerDeviceId;
                $deviceIdCaptured = true;
            }

            // Delete old profile image if exists
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // Store new image
            $imagePath = $request->file('image')->store('profile-images', 'public');

            // Update user profile
            $user->update([
                'profile_image' => $imagePath,
                'device_ids' => $deviceIds,
                'last_active_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => $deviceIdCaptured ? 
                    'Profile image updated successfully. Device registered.' : 
                    'Profile image updated successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'profile_image' => asset('storage/' . $user->profile_image),
                        'device_count' => count($deviceIds),
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Image upload failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Delete profile image

    public function deleteImage(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->profile_image) {
                return response()->json([
                    'success' => false,
                    'message' => 'No profile image to delete'
                ], 404);
            }

            // Delete image file
            if (Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // Update user profile image path to null
            $user->update(['profile_image' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Profile image deleted successfully',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'profile_image' => null,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete profile image',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}