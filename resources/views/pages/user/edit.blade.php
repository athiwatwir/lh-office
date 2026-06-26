@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    <div class="space-y-6">
        @if ($item->assets_count > 0)
            <x-ui.alert
                variant="warning"
                title="ตัวแทนนี้ถูกใช้งานอยู่"
                :message="'มีทรัพย์สิน '.number_format($item->assets_count).' รายการ — ไม่สามารถลบได้จนกว่าจะไม่มีการใช้งาน'"
            />
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <x-form.page-header :title="$title" :back-url="route('user.index')" />

            <form method="POST" action="{{ route('user.update', $item) }}" enctype="multipart/form-data" class="px-5 py-6 sm:px-6">
                @include('pages.user.partials.form', ['item' => $item, 'method' => 'PUT', 'showPassword' => false])
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <x-form.page-header title="ตั้งรหัสผ่านใหม่" :subtitle="'กำหนดรหัสผ่านใหม่สำหรับ '.$item->name" :back-url="route('user.index')" />

            <form method="POST" action="{{ route('user.password.update', $item) }}" class="px-5 py-6 sm:px-6">
                @include('pages.user.partials.password-form')
            </form>
        </div>
    </div>
@endsection
