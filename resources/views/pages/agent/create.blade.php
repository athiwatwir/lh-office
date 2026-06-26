@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    <div class="space-y-6">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <x-form.page-header :title="$title" :back-url="route('agent.index')" />

            <form method="POST" action="{{ route('agent.store') }}" enctype="multipart/form-data" class="px-5 py-6 sm:px-6">
                @include('pages.agent.partials.form', ['item' => $item, 'method' => 'POST'])
            </form>
        </div>
    </div>
@endsection
