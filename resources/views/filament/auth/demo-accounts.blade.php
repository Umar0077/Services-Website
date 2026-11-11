{{-- Shown under the admin sign-in form only when local demo login is enabled. --}}
@php($accounts = \App\Filament\Auth\Login::demoAccounts())
@if (count($accounts))
    <div style="margin-top:1.25rem;border:1px dashed rgba(255,87,34,.55);border-radius:12px;padding:14px 16px;font-size:13px;line-height:1.5">
        <p style="font-weight:600;margin:0 0 4px">Demo login (local copy only)</p>
        <p style="margin:0 0 10px;opacity:.75">The form is already filled in. Click <strong>Sign in</strong>, or pick another role to try:</p>
        <div style="display:flex;flex-wrap:wrap;gap:8px">
            @foreach ($accounts as $key => $account)
                <x-filament::button type="button" size="sm" color="gray" outlined wire:click="fillDemo('{{ $key }}')">
                    {{ $account['label'] }}
                </x-filament::button>
            @endforeach
        </div>
        <table style="margin-top:10px;width:100%;font-size:12px;opacity:.8">
            @foreach ($accounts as $account)
                <tr><td style="padding:2px 8px 2px 0">{{ $account['label'] }}</td><td style="padding:2px 8px 2px 0">{{ $account['email'] }}</td><td><code>{{ $account['password'] }}</code></td></tr>
            @endforeach
        </table>
    </div>
@endif
