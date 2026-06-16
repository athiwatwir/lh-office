@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    <div class="space-y-6">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <div class="border-b border-gray-200 px-5 py-4 sm:px-6">
                <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
            </div>

            @include('pages.article.partials.form', [
                'item' => $item,
                'categories' => $categories,
                'method' => 'POST',
                'action' => route('article.store'),
            ])
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/components/wysiwyg-editor.js'])
@endpush
