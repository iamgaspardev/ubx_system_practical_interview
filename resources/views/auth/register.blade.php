@extends('layouts.app')

@section('title', 'Register - UBX System')

@section('content')
    <div class="max-w-md w-full bg-white rounded-3xl p-8 border border-gray-100">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-green-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z">
                    </path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">UBX System</h1>
            <h2 class="text-lg font-semibold text-gray-700 mb-1">Create Account</h2>
        </div>

        <!-- Register Form -->
        <form method="POST" action="{{ route('register') }}" class="space-y-6">
            @csrf

            <!-- Name -->
            <div>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required
                    class="w-full px-4 py-4 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition-all duration-200 @error('name') border-red-300 @enderror"
                    placeholder="Full Name">
                @error('name')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                    class="w-full px-4 py-4 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition-all duration-200 @error('email') border-red-300 @enderror"
                    placeholder="Email Address">
                @error('email')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <input id="password" name="password" type="password" required
                    class="w-full px-4 py-4 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition-all duration-200 @error('password') border-red-300 @enderror"
                    placeholder="Password">
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                    class="w-full px-4 py-4 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition-all duration-200"
                    placeholder="Confirm Password">
            </div>

            <!-- Submit Button -->
            <button type="submit"
                class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-4 rounded-2xl transition-colors duration-200 focus:ring-4 focus:ring-green-200 focus:outline-none">
                Create Account
            </button>
        </form>

        <!-- Login Link -->
        <div class="mt-8 text-center">
            <p class="text-gray-600">
                Already have an account?
                <a href="{{ route('login') }}" class="text-green-600 hover:text-green-500 font-medium">
                    Log in here
                </a>
            </p>
        </div>
    </div>
@endsection