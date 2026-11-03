
<?php
require_once __DIR__ . '/../../app/helpers/session.php';

$admin = getAdmin();

$displayName = $admin['username']
    ?? $student['full_name']
    ?? 'Administrator';

$displayRole = ucwords(
    str_replace('_', ' ', $role ?? 'Administrator')
);
?>

<!-- TOP HEADER -->
<header
    class="fixed inset-x-0 top-0 z-40 h-16
           border-b border-slate-200
           bg-white/95 backdrop-blur-md
           lg:left-70"
>
    <div
        class="flex h-full items-center justify-between
               gap-4 px-4 sm:px-6 lg:px-8"
    >

        <!-- LEFT SECTION -->
        <div class="flex min-w-0 items-center gap-3">

            <!-- MOBILE MENU -->
            <button
                id="hamburger-btn"
                type="button"
                aria-label="Open navigation menu"
                aria-controls="mobile-drawer"
                aria-expanded="false"
                class="flex h-10 w-10 shrink-0 items-center
                       justify-center rounded-xl
                       border border-slate-200 bg-white
                       text-slate-600 transition-colors
                       hover:border-violet-200
                       hover:bg-violet-50 hover:text-violet-700
                       focus-visible:outline-none
                       focus-visible:ring-2
                       focus-visible:ring-violet-500
                       lg:hidden"
            >
                <svg
                    id="hamburger-icon"
                    class="h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16"
                    />
                </svg>

                <svg
                    id="close-icon"
                    class="hidden h-5 w-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    aria-hidden="true"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>

            <!-- MOBILE BRAND -->
            <div class="flex min-w-0 items-center gap-2.5 lg:hidden">
                <div
                    class="flex h-9 w-9 shrink-0 items-center
                           justify-center rounded-lg
                           border border-violet-100 bg-violet-50"
                >
                    <img
                        src="<?= BASE_URL ?>assets/images/aite-logo.png"
                        alt="AITE Logo"
                        class="h-7 w-7 object-contain"
                    >
                </div>

                <div class="min-w-0">
                    <h1
                        class="truncate text-sm font-bold
                               tracking-tight text-slate-900"
                    >
                        Smart-Eval
                    </h1>
                    <p
                        class="truncate text-[10px] font-medium
                               text-slate-500"
                    >
                        Teacher Evaluation
                    </p>
                </div>
            </div>

            <!-- DESKTOP PAGE INFORMATION -->
            <div class="hidden min-w-0 items-center gap-3 lg:flex">
                <div
                    class="flex h-10 w-10 shrink-0 items-center
                           justify-center rounded-xl
                           bg-violet-50 text-violet-600"
                >
                    <i
                        class="fas fa-layer-group text-sm"
                        aria-hidden="true"
                    ></i>
                </div>

                <div class="min-w-0">
                    <h2
                        class="truncate text-sm font-semibold
                               text-slate-900"
                    >
                        <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?>
                    </h2>

                    <p
                        class="mt-0.5 flex items-center gap-1.5
                               text-xs text-slate-500"
                    >
                        <i
                            class="far fa-calendar"
                            aria-hidden="true"
                        ></i>
                        <?= date('l, F j, Y') ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- USER PROFILE -->
        <div class="flex shrink-0 items-center gap-3">

            <div class="hidden text-right leading-tight sm:block">
                <p class="max-w-44 truncate text-sm font-semibold text-slate-900">
                    <?= htmlspecialchars($displayName) ?>
                </p>

                <p class="mt-1 text-xs font-medium text-slate-500">
                    <?= htmlspecialchars($displayRole) ?>
                </p>
            </div>

            <div class="relative shrink-0">
                <div
                    class="flex h-10 w-10 items-center
                           justify-center rounded-xl
                           bg-violet-100 text-violet-700"
                >
                    <i
                        class="fas fa-user text-sm"
                        aria-hidden="true"
                    ></i>
                </div>

                <span
                    class="absolute -right-0.5 -bottom-0.5
                           h-3 w-3 rounded-full
                           border-2 border-white bg-emerald-500"
                    aria-hidden="true"
                ></span>
            </div>
        </div>

    </div>
</header>

<!-- MOBILE OVERLAY -->
<div
    id="mobile-overlay"
    class="fixed inset-0 z-40 hidden
           bg-slate-950/50 backdrop-blur-sm
           lg:hidden"
></div>

<!-- MOBILE DRAWER -->
<aside
    id="mobile-drawer"
    aria-label="Mobile navigation"
    aria-hidden="true"
    inert
    class="fixed inset-y-0 left-0 z-50
           flex w-70 max-w-[85vw] flex-col
           -translate-x-full border-r border-slate-200
           bg-white shadow-2xl
           transition-transform duration-300 ease-out
           lg:hidden"
>

    <!-- DRAWER BRAND -->
    <div
        class="flex h-16 shrink-0 items-center
               justify-between border-b border-slate-100 px-4"
    >
        <div class="flex min-w-0 items-center gap-3">
            <div
                class="flex h-10 w-10 shrink-0 items-center
                       justify-center rounded-xl
                       border border-violet-100 bg-violet-50"
            >
                <img
                    src="<?= BASE_URL ?>assets/images/aite-logo.png"
                    alt="AITE Logo"
                    class="h-8 w-8 object-contain"
                >
            </div>

            <div class="min-w-0">
                <p class="text-base font-bold tracking-tight text-slate-900">
                    Smart-Eval
                </p>
                <p class="text-[10px] font-semibold uppercase
                          tracking-wider text-violet-600">
                    Teacher Evaluation
                </p>
            </div>
        </div>

        <button
            id="mobile-close-btn"
            type="button"
            aria-label="Close navigation menu"
            class="flex h-9 w-9 shrink-0 items-center
                   justify-center rounded-lg text-slate-500
                   transition-colors hover:bg-slate-100
                   hover:text-slate-900
                   focus-visible:outline-none
                   focus-visible:ring-2 focus-visible:ring-violet-500"
        >
            <i class="fas fa-times" aria-hidden="true"></i>
        </button>
    </div>

    <!-- MOBILE NAVIGATION -->
    <nav
        id="mobile-nav"
        aria-label="Mobile main navigation"
        class="min-h-0 flex-1 overflow-y-auto px-3 py-5"
    >
        <div class="mb-3 flex items-center gap-3 px-3">
            <span
                class="text-[10px] font-bold uppercase
                       tracking-widest text-slate-400"
            >
                Main Menu
            </span>

            <div class="h-px flex-1 bg-slate-100"></div>
        </div>

        <div class="space-y-1">
            <?php require __DIR__ . '/sidebar_nav.php'; ?>
        </div>
    </nav>

    <!-- MOBILE ACCOUNT FOOTER -->
    <div
        class="shrink-0 border-t border-slate-200
               bg-white px-3 py-4"
    >
        <div
            class="mb-2 flex items-center gap-3
                   rounded-xl border border-slate-200
                   bg-slate-50 px-3 py-3"
        >
            <div
                class="flex h-10 w-10 shrink-0 items-center
                       justify-center rounded-xl
                       bg-violet-100 text-violet-700"
            >
                <i class="fas fa-user text-sm" aria-hidden="true"></i>
            </div>

            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-slate-900">
                    <?= htmlspecialchars($displayName) ?>
                </p>
                <p class="mt-0.5 truncate text-xs text-slate-500">
                    <?= htmlspecialchars($displayRole) ?>
                </p>
            </div>
        </div>

        <button
            id="mobileLogoutBtn"
            type="button"
            onclick="logout()"
            class="group flex min-h-11 w-full items-center
                   gap-3 rounded-xl px-3 text-left
                   text-sm font-medium text-slate-600
                   transition-colors hover:bg-red-50
                   hover:text-red-600
                   focus-visible:outline-none
                   focus-visible:ring-2 focus-visible:ring-red-400"
        >
            <span
                class="flex h-9 w-9 items-center justify-center
                       rounded-lg bg-slate-100 text-slate-500
                       group-hover:bg-red-100
                       group-hover:text-red-600"
            >
                <i
                    class="fas fa-sign-out-alt text-sm"
                    aria-hidden="true"
                ></i>
            </span>

            <span>Logout</span>
        </button>
    </div>
</aside>

<!-- MOBILE DRAWER SCRIPT -->
<script>
(() => {
    const button = document.getElementById('hamburger-btn');
    const drawer = document.getElementById('mobile-drawer');
    const overlay = document.getElementById('mobile-overlay');
    const closeButton = document.getElementById('mobile-close-btn');
    const hamburger = document.getElementById('hamburger-icon');
    const closeIcon = document.getElementById('close-icon');

    if (!button || !drawer || !overlay) return;

    let isOpen = false;

    function setDrawerOpen(open, restoreFocus = false) {
        isOpen = open;

        if (open) {
            drawer.removeAttribute('inert');
            drawer.setAttribute('aria-hidden', 'false');
        } else {
            if (restoreFocus) button.focus();
            drawer.setAttribute('inert', '');
            drawer.setAttribute('aria-hidden', 'true');
        }

        drawer.classList.toggle('-translate-x-full', !open);
        overlay.classList.toggle('hidden', !open);

        hamburger?.classList.toggle('hidden', open);
        closeIcon?.classList.toggle('hidden', !open);

        document.body.classList.toggle('overflow-hidden', open);

        button.setAttribute('aria-expanded', String(open));
        button.setAttribute(
            'aria-label',
            open ? 'Close navigation menu' : 'Open navigation menu'
        );

        if (open) {
            closeButton?.focus();
        }
    }

    button.addEventListener('click', () => {
        setDrawerOpen(!isOpen);
    });

    closeButton?.addEventListener('click', () => {
        setDrawerOpen(false, true);
    });

    overlay.addEventListener('click', () => {
        setDrawerOpen(false, true);
    });

    drawer.querySelectorAll('a[href]').forEach(link => {
        link.addEventListener('click', () => {
            setDrawerOpen(false);
        });
    });

    drawer.addEventListener('keydown', event => {
        if (event.key !== 'Tab' || !isOpen) return;

        const focusable = Array.from(
            drawer.querySelectorAll(
                'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])'
            )
        ).filter(element => element.getClientRects().length > 0);

        if (!focusable.length) return;

        const first = focusable[0];
        const last = focusable[focusable.length - 1];

        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && isOpen) {
            setDrawerOpen(false, true);
        }
    });

    window.addEventListener('resize', () => {
        if (isOpen && window.innerWidth >= 1024) {
            setDrawerOpen(false);
        }
    });
})();
</script>
