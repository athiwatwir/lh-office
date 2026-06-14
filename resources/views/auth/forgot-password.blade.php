@extends('layouts.auth')

@section('auth-title', __('Forgot Password'))
@section('auth-subtitle', __('Enter your email and we will send you a password reset link.'))

@section('auth-content')
    @if (session('status'))
        <div class="mb-5 rounded-lg border border-success-200 bg-success-50 px-4 py-3 text-sm text-success-700">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="space-y-5">
            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Email') }}<span class="text-error-500">*</span>
                </label>
                <x-auth.input id="email" type="email" name="email" :value="old('email')" required autofocus
                    placeholder="info@gmail.com" />
                <x-auth.error :messages="$errors->get('email')" />
            </div>

            <button type="submit"
                class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                {{ __('Email Password Reset Link') }}
            </button>
        </div>
    </form>
@endsection

@section('auth-footer')
    <p class="text-center text-sm font-normal text-gray-700 sm:text-start dark:text-gray-300">
        <a href="{{ route('login') }}" class="text-brand-500 hover:text-brand-600">{{ __('Back to Sign In') }}</a>
    </p>
@endsection
