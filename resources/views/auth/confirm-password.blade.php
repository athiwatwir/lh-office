@extends('layouts.auth')

@section('auth-title', __('Confirm Password'))
@section('auth-subtitle', __('This is a secure area. Please confirm your password before continuing.'))

@section('auth-content')
    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div class="space-y-5">
            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Password') }}<span class="text-error-500">*</span>
                </label>
                <x-auth.input id="password" type="password" name="password" required autocomplete="current-password"
                    placeholder="{{ __('Enter your password') }}" />
                <x-auth.error :messages="$errors->get('password')" />
            </div>

            <button type="submit"
                class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 flex w-full items-center justify-center rounded-lg px-4 py-3 text-sm font-medium text-white transition">
                {{ __('Confirm') }}
            </button>
        </div>
    </form>
@endsection
