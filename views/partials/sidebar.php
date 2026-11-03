
<?php
require_once __DIR__ . '/../../app/helpers/session.php';

$admin = getAdmin();

$sidebarName = $admin['username'] ?? 'Administrator';

$sidebarRole = ucwords(
    str_replace('_', ' ', $role ?? 'Administrator')
);
?>

<!-- DESKTOP SIDEBAR -->
<aside
    aria-label="Administrator sidebar"
    class="fixed inset-y-0 left-0 z-30
           hidden w-70 flex-col
           border-r border-slate-200
           bg-white lg:flex"
>

    <!-- BRAND HEADER -->
    <header
        class="flex h-16 shrink-0 items-center
               border-b border-slate-100 px-5"
    >
        <div class="flex min-w-0 items-center gap-3">

            <div
                class="flex h-10 w-10 shrink-0
                       items-center justify-center
                       rounded-xl border border-violet-100
                       bg-violet-50"
            >
                <img
                    src="<?= BASE_URL ?>assets/images/aite-logo.png"
                    alt="AITE Logo"
                    class="h-8 w-8 object-contain"
                >
            </div>

            <div class="min-w-0">
                <p
                    class="truncate text-base font-bold
                           tracking-tight text-slate-900"
                >
                    Smart-Eval
                </p>

                <p
                    class="mt-0.5 text-[10px] font-semibold
                           uppercase tracking-wider text-violet-600"
                >
                    Teacher Evaluation
                </p>
            </div>

        </div>
    </header>

    <!-- NAVIGATION -->
    <nav
        aria-label="Main navigation"
        class="min-h-0 flex-1 overflow-y-auto
               px-3 py-5"
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

    <!-- ACCOUNT FOOTER -->
    <div
        class="shrink-0 border-t border-slate-200
               bg-white px-3 py-4"
    >

        <!-- LOGOUT -->
        <button
            id="logoutBtn"
            type="button"
            onclick="logout()"
            class="group flex min-h-11 w-full
                   items-center gap-3 rounded-xl px-3
                   text-left text-sm font-medium
                   text-slate-600 transition-colors
                   hover:bg-red-50 hover:text-red-600
                   focus-visible:outline-none
                   focus-visible:ring-2
                   focus-visible:ring-red-400"
        >

            <span
                class="flex h-9 w-9 shrink-0
                       items-center justify-center
                       rounded-lg bg-slate-100 text-slate-500
                       transition-colors
                       group-hover:bg-red-100
                       group-hover:text-red-600"
            >
                <i
                    class="fas fa-sign-out-alt text-sm"
                    aria-hidden="true"
                ></i>
            </span>

            <span class="flex-1">Logout</span>

            <i
                class="fas fa-chevron-right text-xs
                       text-slate-300 transition-colors
                       group-hover:text-red-400"
                aria-hidden="true"
            ></i>
        </button>
    </div>
</aside>

<!-- ALPINE COLLAPSE PLUGIN -->
<script
    defer
    src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.15.0/dist/cdn.min.js"
></script>

<!-- ALPINE CORE -->
<script
    defer
    src="https://cdn.jsdelivr.net/npm/alpinejs@3.15.0/dist/cdn.min.js"
></script>
