@auth
    @unless ($subscriptionOk)
        <div class="flex items-center justify-between gap-3 bg-red-600 px-4 py-2 text-sm text-white">
            <div class="flex items-center gap-2">
                <x-heroicon-o-exclamation-triangle class="h-4 w-4 shrink-0" />
                <span>
                    This school's subscription is not active &mdash; bus tracking features are suspended.
                </span>
            </div>

            @if (auth()->user()?->hasAnyRole(['School Admin', 'Principal']))
                <a href="{{ route('principal.subscription') }}"
                    class="shrink-0 rounded-lg bg-white/15 px-3 py-1 text-xs font-semibold hover:bg-white/25">
                    View Subscription
                </a>
            @endif
        </div>
    @endunless
@endauth