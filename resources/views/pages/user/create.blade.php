@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    <div class="space-y-6">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <x-form.page-header :title="$title" :back-url="route('user.index')" />

            <form method="POST" action="{{ route('user.store') }}" enctype="multipart/form-data" class="px-5 py-6 sm:px-6">
                @include('pages.user.partials.form', ['item' => $item, 'method' => 'POST', 'showPassword' => true])
            </form>
        </div>
    </div>
@endsection
