<x-layouts::auth :title="__('Staff Login')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Staff Portal')" :description="__('Sign in with your staff or admin credentials')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input name="email" :label="__('Email address')" :value="old('email')" type="email" required autofocus
                autocomplete="email" placeholder="staff@plsp.edu.ph" />

            <div class="relative">
                <flux:input name="password" :label="__('Password')" type="password" required
                    autocomplete="current-password" :placeholder="__('Password')" viewable />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </flux:link>
                @endif
            </div>

            <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

            <flux:button variant="primary" type="submit" class="w-full">
                {{ __('Sign in as Staff') }}
            </flux:button>
        </form>

        <div class="space-x-1 text-sm text-center rtl:space-x-reverse text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Are you a student?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Sign in here') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>