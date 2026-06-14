@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="{{ __('Profile') }}" />

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-gray-900">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-gray-900">
            @include('profile.partials.update-password-form')
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 lg:p-6 dark:border-gray-800 dark:bg-gray-900">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
@endsection
