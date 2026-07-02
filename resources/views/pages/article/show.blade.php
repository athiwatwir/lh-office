@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$title" />

    <div class="space-y-6">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <div class="flex flex-col gap-4 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">{{ $item->name }}</h3>
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-theme-xs text-gray-500">
                        <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5">{{ $item->category?->name ?? '-' }}</span>
                        @if ($item->isVisibleToAllAgents())
                            <span class="inline-flex rounded-full bg-brand-50 px-2 py-0.5 text-brand-600">ทุก Agent</span>
                        @else
                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5">{{ $item->agent?->name ?? '-' }}</span>
                        @endif
                        @if (($item->isactive ?? 'Y') === 'Y')
                            <span class="inline-flex rounded-full bg-success-50 px-2 py-0.5 text-success-600">เปิดใช้งาน</span>
                        @else
                            <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-gray-600">ปิดใช้งาน</span>
                        @endif
                        <span>ลำดับ {{ $item->seq ?? '-' }}</span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('article.edit', $item) }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50">
                        แก้ไข
                    </a>
                    <a href="{{ route('article.index') }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50">
                        กลับรายการ
                    </a>
                </div>
            </div>

            <div class="px-5 py-6 sm:px-6">
                @if ($item->cover_image_url)
                    <div class="mb-6 overflow-hidden rounded-2xl border border-gray-200">
                        <img src="{{ $item->cover_image_url }}" alt="{{ $item->name }}" class="max-h-96 w-full object-cover">
                    </div>
                @endif

                <article class="prose prose-sm max-w-none">
                    {!! $item->text ?: '<p class="text-gray-500">ยังไม่มีเนื้อหาบทความ</p>' !!}
                </article>
            </div>
        </div>
    </div>
@endsection
