<section>
    <header class="mb-6">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            {{ __('Update Password') }}
        </h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password"
                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('Current Password') }}
            </label>
            <x-auth.input id="update_password_current_password" name="current_password" type="password"
                autocomplete="current-password" />
            <x-auth.error :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div>
            <label for="update_password_password"
                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('New Password') }}
            </label>
            <x-auth.input id="update_password_password" name="password" type="password"
                autocomplete="new-password" />
            <x-auth.error :messages="$errors->updatePassword->get('password')" />
        </div>

        <div>
            <label for="update_password_password_confirmation"
                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ __('Confirm Password') }}
            </label>
            <x-auth.input id="update_password_password_confirmation" name="password_confirmation" type="password"
                autocomplete="new-password" />
            <x-auth.error :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        <div class="flex items-center gap-4">
            <button type="submit"
                class="bg-brand-500 shadow-theme-xs hover:bg-brand-600 inline-flex items-center justify-center rounded-lg px-5 py-3 text-sm font-medium text-white transition">
                {{ __('Save') }}
            </button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-success-600">
                    {{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</section>
