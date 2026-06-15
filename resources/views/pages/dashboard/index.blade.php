@extends('layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 sm:text-2xl">ยินดีต้อนรับ</h2>
            <p class="mt-1 text-sm text-gray-500">
                @if ($activeAgent)
                    กำลังใช้งาน: <span class="font-medium text-gray-700">{{ $activeAgent->name }}</span>
                @else
                    กรุณาเลือกเอเจนต์ก่อนเริ่มใช้งานระบบ
                @endif
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">ทรัพย์สินทั้งหมด</p>
                <p class="mt-2 text-2xl font-semibold text-gray-800">{{ number_format($stats['total']) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">เปิดใช้งาน</p>
                <p class="mt-2 text-2xl font-semibold text-success-600">{{ number_format($stats['active']) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5">
                <p class="text-sm text-gray-500">ปิดใช้งาน</p>
                <p class="mt-2 text-2xl font-semibold text-gray-500">{{ number_format($stats['inactive']) }}</p>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6">
            <h3 class="mb-4 text-base font-semibold text-gray-800">เมนูลัด</h3>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <a href="{{ route('property.index') }}" class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 transition hover:border-brand-300 hover:bg-brand-50/40">
                    <i class="lni lni-apartment text-xl text-brand-500"></i>
                    <span class="text-sm font-medium text-gray-800">รายการทรัพย์สิน</span>
                </a>
                <a href="{{ route('property.create') }}" class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 transition hover:border-brand-300 hover:bg-brand-50/40">
                    <i class="lni lni-circle-plus text-xl text-brand-500"></i>
                    <span class="text-sm font-medium text-gray-800">เพิ่มทรัพย์สิน</span>
                </a>
                <a href="{{ route('propertyRequest.index', ['type' => 'sell']) }}" class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 transition hover:border-brand-300 hover:bg-brand-50/40">
                    <i class="lni lni-home text-xl text-brand-500"></i>
                    <span class="text-sm font-medium text-gray-800">ฝากขายบ้าน-ที่ดิน</span>
                </a>
                <a href="{{ route('propertyRequest.index', ['type' => 'buy']) }}" class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 transition hover:border-brand-300 hover:bg-brand-50/40">
                    <i class="lni lni-search text-xl text-brand-500"></i>
                    <span class="text-sm font-medium text-gray-800">ฝากหาบ้าน-ที่ดิน</span>
                </a>
                <a href="{{ route('propertyType.index') }}" class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 transition hover:border-brand-300 hover:bg-brand-50/40">
                    <i class="lni lni-layers text-xl text-brand-500"></i>
                    <span class="text-sm font-medium text-gray-800">ประเภททรัพย์สิน</span>
                </a>
                <a href="{{ route('user.index') }}" class="flex items-center gap-3 rounded-xl border border-gray-200 px-4 py-3 transition hover:border-brand-300 hover:bg-brand-50/40">
                    <i class="lni lni-users text-xl text-brand-500"></i>
                    <span class="text-sm font-medium text-gray-800">รายชื่อตัวแทนขาย</span>
                </a>
            </div>
        </div>
    </div>
@endsection
