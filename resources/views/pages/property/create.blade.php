@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    <div class="space-y-6">
        <div class="rounded-2xl border border-gray-200 bg-white">
            <x-form.page-header :title="$title" :back-url="route('property.index')" />

            <div x-data="propertyFormGuard(@js($siteFeatures))" @property-code-status="onCodeStatus($event)">
                <form
                    id="property-create-form"
                    method="POST"
                    action="{{ route('property.store') }}"
                    class="px-5 py-6 sm:px-6"
                    @submit="handleSubmit($event)"
                >
                    @include('pages.property.partials.form', ['item' => $item, 'method' => 'POST', 'guard' => true])
                </form>
            </div>
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
