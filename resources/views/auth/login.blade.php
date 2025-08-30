@extends('layouts.app')

@section('title', 'Login - UBX System')

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
            <h2 class="text-lg font-semibold text-gray-700 mb-1">Log In</h2>
        </div>

        <!-- Login Form -->
        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

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
            <div class="relative">
                <input id="password" name="password" type="password" required
                    class="w-full px-4 py-4 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent outline-none transition-all duration-200 @error('password') border-red-300 @enderror"
                    placeholder="Password">
                <button type="button" onclick="togglePassword()"
                    class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400">
                    <svg id="eyeIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                        </path>
                    </svg>
                </button>
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input type="checkbox" name="remember"
                        class="w-5 h-5 text-green-500 border-gray-300 rounded focus:ring-green-500">
                    <span class="ml-2 text-sm text-gray-600">Stay Logged In</span>
                </label>
                <a href="#" class="text-sm text-green-600 hover:text-green-500 font-medium">
                    Forgot Password?
                </a>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                class="w-full bg-green-500 hover:bg-green-600 text-white font-medium py-4 rounded-2xl transition-colors duration-200 focus:ring-4 focus:ring-green-200 focus:outline-none">
                Log In
            </button>
        </form>

        <!-- Register Link -->
        <div class="mt-8 text-center">
            <p class="text-gray-600">
                New user?
                <a href="{{ route('register') }}" class="text-green-600 hover:text-green-500 font-medium">
                    Create an account
                </a>
            </p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                        `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        `;
            }
        }
    </script>
@endsection