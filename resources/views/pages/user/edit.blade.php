@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    <div class="space-y-6">
        @if (session('success'))
            <x-ui.alert variant="success" :title="session('success')" />
        @endif

        @if ($item->assets_count > 0)
            <x-ui.alert
                variant="warning"
                title="ตัวแทนนี้ถูกใช้งานอยู่"
                :message="'มีทรัพย์สิน '.number_format($item->assets_count).' รายการ — ไม่สามารถลบได้จนกว่าจะไม่มีการใช้งาน'"
            />
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <div class="border-b border-gray-200 px-5 py-4 sm:px-6">
                <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
            </div>

            <form method="POST" action="{{ route('user.update', $item) }}" enctype="multipart/form-data" class="px-5 py-6 sm:px-6">
                @include('pages.user.partials.form', ['item' => $item, 'method' => 'PUT', 'showPassword' => false])
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <div class="border-b border-gray-200 px-5 py-4 sm:px-6">
                <h3 class="text-lg font-semibold text-gray-800">ตั้งรหัสผ่านใหม่</h3>
                <p class="mt-1 text-sm text-gray-500">กำหนดรหัสผ่านใหม่สำหรับ {{ $item->name }}</p>
            </div>

            <form method="POST" action="{{ route('user.password.update', $item) }}" class="px-5 py-6 sm:px-6">
                @include('pages.user.partials.password-form')
            </form>
        </div>
    </div>
@endsection
