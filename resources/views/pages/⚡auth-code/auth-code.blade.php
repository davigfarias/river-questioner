<div class="flex flex-col items-center text-center gap-6 bg-surface-container rounded-xl border border-outline-variant p-8">
    <flux:icon.academic-cap class="size-10 text-primary" />

    <div>
        <flux:heading size="lg" class="font-heading text-headline-lg-mobile text-on-surface">Digite o código de acesso</flux:heading>
        <flux:subheading class="font-mono text-label-md text-on-surface-variant mt-1">Código de 4 dígitos fornecido para entrar.</flux:subheading>
    </div>

    <form wire:submit="joinSession" class="w-full flex flex-col gap-6">
        <flux:otp wire:model="code" length="4" submit="auto" class="w-full justify-between" />

        @error('code')
            <flux:text class="text-error -mt-4">{{ $message }}</flux:text>
        @enderror

        <flux:button type="submit" variant="primary" icon:trailing="arrow-right" class="w-full justify-center label-md">
            Entrar
        </flux:button>
    </form>
</div>
