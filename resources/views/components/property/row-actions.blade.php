@props([
    'property',
])

<div class="flex justify-end">
    <x-common.table-dropdown>
        <x-slot name="button">
            <button
                type="button"
                aria-haspopup="true"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-300 bg-white text-gray-500 transition hover:bg-gray-50 hover:text-gray-700"
            >
                <svg class="size-5 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M5.99902 10.245C6.96552 10.245 7.74902 11.0285 7.74902 11.995V12.005C7.74902 12.9715 6.96552 13.755 5.99902 13.755C5.03253 13.755 4.24902 12.9715 4.24902 12.005V11.995C4.24902 11.0285 5.03253 10.245 5.99902 10.245ZM17.999 10.245C18.9655 10.245 19.749 11.0285 19.749 11.995V12.005C19.749 12.9715 18.9655 13.755 17.999 13.755C17.0325 13.755 16.249 12.9715 16.249 12.005V11.995C16.249 11.0285 17.0325 10.245 17.999 10.245ZM13.749 11.995C13.749 11.0285 12.9655 10.245 11.999 10.245C11.0325 10.245 10.249 11.0285 10.249 11.995V12.005C10.249 12.9715 11.0325 13.755 11.999 13.755C12.9655 13.755 13.749 12.9715 13.749 12.005V11.995Z" />
                </svg>
            </button>
        </x-slot>

        <x-slot name="content">
            <a
                href="{{ route('property.edit', $property) }}"
                class="flex w-full rounded-lg px-3 py-2 text-left text-theme-xs font-medium text-gray-700 hover:bg-gray-100"
                role="menuitem"
            >
                แก้ไข
            </a>

            <form
                method="POST"
                action="{{ route('property.destroy', $property) }}"
                onsubmit="return confirm('ยืนยันการลบทรัพย์สินนี้?')"
            >
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="flex w-full rounded-lg px-3 py-2 text-left text-theme-xs font-medium text-error-600 hover:bg-error-50"
                    role="menuitem"
                >
                    ลบ
                </button>
            </form>
        </x-slot>
    </x-common.table-dropdown>
</div>
