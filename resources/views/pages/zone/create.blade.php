@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    <div class="space-y-6">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <x-form.page-header :title="$title" :back-url="route('zone.index')" />

            @include('pages.zone.partials.form', [
                'item' => $item,
                'method' => 'POST',
                'action' => route('zone.store'),
            ])
        </div>
    </div>
@endsection
