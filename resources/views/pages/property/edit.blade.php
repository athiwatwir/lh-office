@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    <div class="space-y-6">
        @if (session('success'))
            <x-ui.alert variant="success" :title="session('success')" />
        @endif

        @if (session('error'))
            <x-ui.alert variant="error" :title="session('error')" />
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <div class="border-b border-gray-200 px-5 py-4 sm:px-6">
                <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
                <p class="mt-1 text-sm text-gray-500">รหัส: {{ $item->code }}</p>
            </div>

            <form method="POST" action="{{ route('property.update', $item) }}" class="px-5 py-6 sm:px-6">
                @include('pages.property.partials.form', ['item' => $item, 'method' => 'PUT'])
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/dist/jquery.Thailand.min.css">
    <style>
        .twitter-typeahead { width: 100%; }
        .tt-menu { z-index: 50 !important; }
    </style>
@endpush

@push('scripts')
    @vite(['resources/js/components/wysiwyg-editor.js', 'resources/js/components/property-location.js'])
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/dependencies/JQL.min.js"></script>
    <script src="https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/dependencies/typeahead.bundle.js"></script>
    <script src="https://earthchie.github.io/jquery.Thailand.js/jquery.Thailand.js/dist/jquery.Thailand.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof $.Thailand !== 'function') {
                return;
            }

            $.Thailand({
                $district: $('#address_district'),
                $amphoe: $('#address_amphur'),
                $province: $('#address_province'),
                $zipcode: $('#address_zipcode'),
            });
        });
    </script>
@endpush
