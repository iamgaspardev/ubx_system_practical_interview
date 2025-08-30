@extends('layouts.app')

@section('title', 'Profile - UBX System')

@section('content')
    <div class="max-w-2xl mx-auto">
        <!-- Profile Header -->
        <div class="bg-white rounded-3xl p-8 mb-8 border border-gray-100">
            <div class="flex items-center">
                <div class="w-20 h-20 bg-green-500 rounded-2xl flex items-center justify-center mr-6">
                    <span class="text-white font-bold text-2xl">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $user->name }}</h1>
                    <p class="text-gray-600 text-lg">{{ $user->email }}</p>
                    <div class="mt-3 flex items-center space-x-4">
                        <div
                            class="inline-flex items-center px-3 py-1 bg-green-50 text-green-700 rounded-full text-sm font-medium">
                            <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                            Active User
                        </div>
                        <span class="text-sm text-gray-500">
                            Member since {{ $user->created_at->format('F Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Form -->
        <div class="bg-white rounded-3xl p-8 border border-gray-100">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Update Profile</h2>

            <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
                @csrf

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required
                        class="w-full px-4 py-4 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition-all duration-200 @error('name') border-red-300 @enderror"
                        placeholder="Enter your full name">
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required
                        class="w-full px-4 py-4 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition-all duration-200 @error('email') border-red-300 @enderror"
                        placeholder="Enter your email">
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Current Password -->
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-2">Current
                        Password</label>
                    <input id="current_password" name="current_password" type="password"
                        class="w-full px-4 py-4 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition-all duration-200 @error('current_password') border-red-300 @enderror"
                        placeholder="Enter current password (required to change password)">
                    @error('current_password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- New Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                    <input id="password" name="password" type="password"
                        class="w-full px-4 py-4 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition-all duration-200 @error('password') border-red-300 @enderror"
                        placeholder="Enter new password (leave blank to keep current)">
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm New Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm New
                        Password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password"
                        class="w-full px-4 py-4 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition-all duration-200"
                        placeholder="Confirm new password">
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('dashboard') }}"
                        class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-2xl transition-colors duration-200">
                        Cancel
                    </a>
                    <button type="submit"
                        class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-medium rounded-2xl transition-colors duration-200 focus:ring-4 focus:ring-green-200 focus:outline-none">
                        Update Profile
                    </button>
                </div>
            </form>
        </div>

        <!-- Account Information -->
        <div class="bg-white rounded-3xl p-8 mt-8 border border-gray-100">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Account Information</h2>

            <div class="space-y-4">
                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <span class="text-gray-600">Account Created</span>
                    <span class="font-medium text-gray-900">{{ $user->created_at->format('F j, Y g:i A') }}</span>
                </div>

                <div class="flex justify-between items-center py-3 border-b border-gray-100">
                    <span class="text-gray-600">Last Updated</span>
                    <span class="font-medium text-gray-900">{{ $user->updated_at->format('F j, Y g:i A') }}</span>
                </div>

                <div class="flex justify-between items-center py-3">
                    <span class="text-gray-600">Account Status</span>
                    <span
                        class="inline-flex items-center px-3 py-1 bg-green-50 text-green-700 rounded-full text-sm font-medium">
                        <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                        Active
                    </span>
                </div>
            </div>
        </div>
    </div>
@endsection