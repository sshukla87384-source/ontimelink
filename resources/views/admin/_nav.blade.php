<div class="otl-admin-nav mb-4">
    <nav class="nav nav-pills flex-nowrap overflow-auto">
        @foreach ([
            'admin.dashboard' => 'Overview',
            'admin.users.index' => 'Users',
            'admin.links.index' => 'Links',
            'admin.referrals.index' => 'Referrals',
            'admin.payments.index' => 'Payments',
            'admin.audit.index' => 'Audit log',
            'admin.settings.index' => 'Settings',
        ] as $route => $label)
            <a class="nav-link text-nowrap @if (request()->routeIs($route.'*') || (request()->routeIs('admin.dashboard') && $route === 'admin.dashboard')) active @endif"
               href="{{ route($route) }}">{{ $label }}</a>
        @endforeach
    </nav>
</div>
