@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    <div class="space-y-6">
        @if ($item->assets_count > 0)
            <x-ui.alert
                variant="warning"
                title="แท็กนี้ถูกใช้งานอยู่"
                :message="'มีทรัพย์สิน '.number_format($item->assets_count).' รายการ — ไม่สามารถลบได้จนกว่าจะไม่มีการใช้งาน'"
            />
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <x-form.page-header :title="$title" :back-url="route('tag.index')" />

            @include('pages.tag.partials.form', [
                'item' => $item,
                'method' => 'PUT',
                'action' => route('tag.update', $item),
            ])
        </div>
    </div>
@endsection
