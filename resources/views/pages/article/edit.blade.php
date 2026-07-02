@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    <div class="space-y-6">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <x-form.page-header :title="$title" :back-url="route('article.index')" />

            @include('pages.article.partials.form', [
                'item' => $item,
                'categories' => $categories,
                'agents' => $agents,
                'method' => 'PUT',
                'action' => route('article.update', $item),
            ])
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/components/wysiwyg-editor.js'])
@endpush
