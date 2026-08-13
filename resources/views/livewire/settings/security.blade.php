<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Security settings') }}</flux:heading>

    <x-settings.layout
        :heading="__('Update password')"
        :subheading="__('Ensure your account is using a long, random password to stay secure')"
    >
        <form method="POST" wire:submit="updatePassword" class="mt-6 space-y-6">
            <flux:input
                wire:model="current_password"
                :label="__('Current password')"
                type="password"
                required
                autocomplete="current-password"
                viewable
            />
            <flux:input
                wire:model="password"
                :label="__('New password')"
                type="password"
                required
                autocomplete="new-password"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />
            <flux:input
                wire:model="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <div class="flex items-center gap-4">
                <flux:button
                    variant="primary"
                    type="submit"
                    data-test="update-password-button"
                >{{ __('Save') }}</flux:button>
            </div>
        </form>

        @if ($canManageTwoFactor)
            <section class="mt-12">
                <flux:heading>{{ __('Two-factor authentication') }}</flux:heading>
                <flux:subheading>{{ __('Manage your two-factor authentication settings') }}</flux:subheading>

                <div class="mx-auto flex w-full flex-col space-y-6 text-sm" wire:cloak>
                    @if ($twoFactorEnabled)
                        <div class="space-y-4">
                            <flux:text>
                                {{ __('You will be prompted for a secure, random pin during login, which you can retrieve from the TOTP-supported application on your phone.') }}
                            </flux:text>

                            <div class="flex justify-start">
                                <flux:button variant="danger" wire:click="disable">
                                    {{ __('Disable 2FA') }}
                                </flux:button>
                            </div>

                            <livewire:settings.two-factor.recovery-codes :$requiresConfirmation />
                        </div>
                    @else
                        <div class="space-y-4">
                            <flux:text variant="subtle">
                                {{ __('When you enable two-factor authentication, you will be prompted for a secure pin during login. This pin can be retrieved from a TOTP-supported application on your phone.') }}
                            </flux:text>

                            <flux:button variant="primary" wire:click="enable"> {{ __('Enable 2FA') }} </flux:button>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        @if ($canManageTwoFactor)
            <flux:modal
                name="two-factor-setup-modal"
                class="max-w-md md:min-w-md"
                @close="closeModal"
                wire:model="showModal"
            >
                <div class="space-y-6">
                    <div class="flex flex-col items-center space-y-4">
                        <div class="w-auto rounded-full border border-stone-100 bg-white p-0.5 shadow-sm dark:border-stone-600 dark:bg-stone-800">
                            <div class="relative overflow-hidden rounded-full border border-stone-200 bg-stone-100 p-2.5 dark:border-stone-600 dark:bg-stone-200">
                                <div class="[&>div]:flex-1 absolute inset-0 flex h-full w-full items-stretch justify-around divide-x divide-stone-200 opacity-50 dark:divide-stone-300">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <div></div>
                                    @endfor
                                </div>

                                <div class="[&>div]:flex-1 absolute inset-0 flex h-full w-full flex-col items-stretch justify-around divide-y divide-stone-200 opacity-50 dark:divide-stone-300">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <div></div>
                                    @endfor
                                </div>

                                <flux:icon.qr-code class="dark:text-accent-foreground relative z-20" />
                            </div>
                        </div>

                        <div class="space-y-2 text-center">
                            <flux:heading size="lg">{{ $this->modalConfig['title'] }}</flux:heading>
                            <flux:text>{{ $this->modalConfig['description'] }}</flux:text>
                        </div>
                    </div>

                    @if ($showVerificationStep)
                        <div class="space-y-6">
                            <div
                                class="flex flex-col items-center justify-center space-y-3"
                                x-data
                                x-init="$nextTick(() => $el.querySelector('input')?.focus())"
                            >
                                <flux:otp
                                    name="code"
                                    wire:model="code"
                                    length="6"
                                    label="OTP Code"
                                    label:sr-only
                                    class="mx-auto"
                                />
                            </div>

                            <div class="flex items-center space-x-3">
                                <flux:button variant="outline" class="flex-1" wire:click="resetVerification">
                                    {{ __('Back') }}
                                </flux:button>

                                <flux:button
                                    variant="primary"
                                    class="flex-1"
                                    wire:click="confirmTwoFactor"
                                    x-bind:disabled="$wire.code.length < 6"
                                >
                                    {{ __('Confirm') }}
                                </flux:button>
                            </div>
                        </div>
                    @else
                        @error('setupData')
                            <flux:callout variant="danger" icon="x-circle" heading="{{ $message }}" />
                        @enderror

                        <div class="flex justify-center">
                            <div class="relative aspect-square w-64 overflow-hidden rounded-lg border border-stone-200 dark:border-stone-700">
                                @empty($qrCodeSvg)
                                    <div class="absolute inset-0 flex animate-pulse items-center justify-center bg-white dark:bg-stone-700">
                                        <flux:icon.loading />
                                    </div>
                                @else
                                    <div x-data class="flex h-full items-center justify-center p-4">
                                        <div
                                            class="rounded bg-white p-3"
                                            :style="$flux.appearance === 'dark' ||
                                            ($flux.appearance === 'system' && $flux.dark)
                                                ? 'filter: invert(1) brightness(1.5)'
                                                : ''"
                                        >
                                            {!! $qrCodeSvg !!}
                                        </div>
                                    </div>
                                @endempty
                            </div>
                        </div>

                        <div>
                            <flux:button
                                :disabled="$errors->has('setupData')"
                                variant="primary"
                                class="w-full"
                                wire:click="showVerificationIfNecessary"
                            >
                                {{ $this->modalConfig['buttonText'] }}
                            </flux:button>
                        </div>

                        <div class="space-y-4">
                            <div class="relative flex w-full items-center justify-center">
                                <div class="absolute inset-0 top-1/2 h-px w-full bg-stone-200 dark:bg-stone-600"></div>
                                <span class="relative bg-white px-2 text-sm text-stone-600 dark:bg-stone-800 dark:text-stone-400">
                                    {{ __('or, enter the code manually') }}
                                </span>
                            </div>

                            <div
                                class="flex items-center space-x-2"
                                x-data="{
                                    copied: false,
                                    async copy() {
                                        try {
                                            await navigator.clipboard.writeText('{{ $manualSetupKey }}');
                                            this.copied = true;
                                            setTimeout(() => this.copied = false, 1500);
                                        } catch (e) {
                                            console.warn('Could not copy to clipboard');
                                        }
                                    }
                                }"
                            >
                                <div class="flex w-full items-stretch rounded-xl border dark:border-stone-700">
                                    @empty($manualSetupKey)
                                        <div class="flex w-full items-center justify-center bg-stone-100 p-3 dark:bg-stone-700">
                                            <flux:icon.loading variant="mini" />
                                        </div>
                                    @else
                                        <input
                                            type="text"
                                            readonly
                                            value="{{ $manualSetupKey }}"
                                            class="w-full bg-transparent p-3 text-stone-900 outline-none dark:text-stone-100"
                                        />

                                        <button
                                            @click="copy()"
                                            class="cursor-pointer border-l border-stone-200 px-3 transition-colors dark:border-stone-600"
                                        >
                                            <flux:icon.document-duplicate x-show="! copied" variant="outline">
                                                </flux:icon>
                                                <flux:icon.check x-show="copied" variant="solid" class="text-green-500">
                                                    </flux:icon>
                                        </button>
                                    @endempty
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </flux:modal>
        @endif

        @if ($canManagePasskeys)
            <section class="mt-12">
                <flux:heading>{{ __('Passkeys') }}</flux:heading>
                <flux:subheading>{{ __('Manage your passkeys for passwordless sign-in') }}</flux:subheading>

                <div class="mx-auto mt-6 flex w-full flex-col space-y-6 text-sm" wire:cloak>
                    <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                        @forelse ($passkeys as $passkey)
                            <div class="flex items-center justify-between p-4 {{ ! $loop->last ? 'border-b border-zinc-200 dark:border-zinc-700' : '' }}">
                                <div class="flex items-center gap-4">
                                    <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-zinc-100 dark:bg-zinc-800">
                                        <flux:icon.key class="size-5 text-zinc-500 dark:text-zinc-400" />
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2.5">
                                            <p class="font-medium tracking-tight">{{ $passkey['name'] }}</p>
                                            @if ($passkey['authenticator'])
                                                <flux:badge size="sm">{{ $passkey['authenticator'] }}</flux:badge>
                                            @endif
                                        </div>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ __('Added :time', ['time' => $passkey['created_at_diff']]) }}
                                            @if ($passkey['last_used_at_diff'])
                                                <span class="mx-1 opacity-50">/</span>
                                                {{ __('Last used :time', ['time' => $passkey['last_used_at_diff']]) }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <flux:button
                                    variant="ghost"
                                    size="sm"
                                    icon="trash"
                                    icon:variant="outline"
                                    wire:click="confirmDelete({{ $passkey['id'] }})"
                                    class="text-red-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/50"
                                />
                            </div>
                        @empty
                            <div class="p-8 text-center">
                                <div class="mx-auto mb-4 flex size-14 items-center justify-center rounded-2xl bg-zinc-100 dark:bg-zinc-800">
                                    <flux:icon.key class="size-7 text-zinc-400 dark:text-zinc-500" />
                                </div>
                                <p class="font-medium">{{ __('No passkeys yet') }}</p>
                                <flux:text class="mt-1">{{ __('Add a passkey to sign in without a password') }}</flux:text>
                            </div>
                        @endforelse
                    </div>

                    <x-passkey-registration />
                </div>
            </section>
        @endif
    </x-settings.layout>

    <flux:modal
        name="delete-passkey-modal"
        class="max-w-md md:min-w-md"
        @close="closeDeleteModal"
        wire:model="showDeleteModal"
    >
        <div class="space-y-6">
            <div class="space-y-2">
                <flux:heading size="lg">{{ __('Remove passkey') }}</flux:heading>
                <flux:text>
                    {{ __('Are you sure you want to remove the passkey ":name"? You will no longer be able to use it to sign in.', ['name' => $deletingPasskeyName]) }}
                </flux:text>
            </div>

            <div class="flex justify-end gap-3">
                <flux:button variant="outline" wire:click="closeDeleteModal"> {{ __('Cancel') }} </flux:button>
                <flux:button variant="danger" wire:click="deletePasskey"> {{ __('Remove passkey') }} </flux:button>
            </div>
        </div>
    </flux:modal>
</section>
