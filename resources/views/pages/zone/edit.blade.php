@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    <div class="space-y-6">
        @if ($item->assets_count + $item->customer_assets_count > 0)
            <x-ui.alert
                variant="warning"
                title="โซนนี้ถูกใช้งานอยู่"
                message="มีทรัพย์สิน {{ number_format($item->assets_count) }} รายการ และคำขอ {{ number_format($item->customer_assets_count) }} รายการ — ไม่สามารถลบได้จนกว่าจะไม่มีการใช้งาน"
            />
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <div class="border-b border-gray-200 px-5 py-4 sm:px-6">
                <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
            </div>

            @include('pages.zone.partials.form', [
                'item' => $item,
                'method' => 'PUT',
                'action' => route('zone.update', $item),
            ])
        </div>
    </div>
@endsection
