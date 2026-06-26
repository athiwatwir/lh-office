@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    <div class="space-y-6">
        @if ($item->articles_count > 0)
            <x-ui.alert
                variant="warning"
                title="ประเภทนี้ถูกใช้งานอยู่"
                message="มีบทความ {{ number_format($item->articles_count) }} รายการ — ไม่สามารถลบได้จนกว่าจะไม่มีการใช้งาน"
            />
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <x-form.page-header :title="$title" :back-url="route('category.index')" />

            @include('pages.category.partials.form', [
                'item' => $item,
                'method' => 'PUT',
                'action' => route('category.update', $item),
            ])
        </div>
    </div>
@endsection
