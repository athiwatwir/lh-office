@extends('layouts.auth')

@section('auth-title', __('Reset Password'))
@section('auth-subtitle', __('Enter your new password below.'))

@section('auth-content')
    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="space-y-5">
            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Email') }}<span class="text-error-500">*</span>
                </label>
                <x-auth.input id="email" type="email" name="email" :value="old('email', $request->email)" required
                    autofocus autocomplete="username" />
                <x-auth.error :messages="$errors->get('email')" />
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Password') }}<span class="text-error-500">*</span>
                </label>
                <x-auth.input id="password" type="password" name="password" required autocomplete="new-password" />
                <x-auth.error :messages="$errors->get('password')" />
            </div>

            <div>
                <label for="password_confirmation"
                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Confirm Password') }}<span class="text-error-500">*</span>
                </label>
                <x-auth.input id="password_confirmation" type="password" name="password_confirmation" required
                    autocomplete="new-password" />
                <x-auth.error :messages="$errors->get('password_confirmation')" />
            </div>

            <button type="submit"
                class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                {{ __('Reset Password') }}
            </button>
        </div>
    </form>
@endsection

@section('auth-footer')
    <p class="text-center text-sm font-normal text-gray-700 sm:text-start dark:text-gray-300">
        <a href="{{ route('login') }}" class="text-brand-500 hover:text-brand-600">{{ __('Back to Sign In') }}</a>
    </p>
@endsection
