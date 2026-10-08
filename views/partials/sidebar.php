<!-- =========================================================
     DESKTOP SIDEBAR
========================================================= -->

<aside
    class="
        fixed
        top-0 bottom-0 left-0
        z-30
        hidden lg:flex
        w-70
        flex-col
        bg-white
        border-r border-slate-200
        shadow-[2px_0_12px_rgba(15,23,42,0.03)]
    "
>

    <!-- =====================================================
         BRAND HEADER
    ====================================================== -->

    <header
        class="
            h-16 shrink-0
            flex items-center
            px-5
            border-b border-slate-200
            bg-white
        "
    >

        <div class="flex items-center gap-3">

            <!-- Logo -->
            <div
                class="
                    flex h-10 w-10
                    shrink-0
                    items-center justify-center
                    rounded-xl
                    bg-violet-50
                    border border-violet-100
                "
            >
                <img
                    src="<?= BASE_URL ?>assets/images/aite-logo.png"
                    alt="AITE Logo"
                    class="h-8 w-8 object-contain"
                >
            </div>


            <!-- Brand -->
            <div class="flex flex-col leading-tight">

                <span
                    class="
                        text-[16px]
                        font-bold
                        tracking-tight
                        text-slate-800
                    "
                >
                    Smart-Eval
                </span>

                <span
                    class="
                        mt-0.5
                        text-[9px]
                        font-semibold
                        tracking-[0.12em]
                        text-violet-600
                    "
                >
                    TEACHER EVALUATION
                </span>

            </div>

        </div>

    </header>


    <!-- =====================================================
         NAVIGATION
    ====================================================== -->

    <nav
        class="
            flex-1
            px-4 py-5
            overflow-y-auto

            scrollbar-thin
            scrollbar-thumb-slate-200
            scrollbar-track-transparent
        "
    >

        <!-- Section Label -->
        <div class="px-3 mb-3">

            <p
                class="
                    text-[10px]
                    font-bold
                    uppercase
                    tracking-[0.12em]
                    text-slate-400
                "
            >
                Main Menu
            </p>

        </div>


        <!-- Navigation Items -->
        <div class="space-y-1">

            <?php require __DIR__ . '/sidebar_nav.php'; ?>

        </div>

    </nav>


    <!-- =====================================================
         LOGOUT
    ====================================================== -->

    <div
        class="
            shrink-0
            px-4 py-4
            border-t border-slate-200
        "
    >

        <button
            id="logoutBtn"
            class="
                group
                flex items-center gap-3
                w-full
                rounded-xl
                px-3 py-2.5
                text-sm font-medium
                text-slate-500
                transition-all duration-200

                hover:bg-red-50
                hover:text-red-600
            "
        >

            <!-- Icon -->
            <span
                class="
                    flex h-8 w-8
                    items-center justify-center
                    rounded-lg
                    bg-slate-100
                    text-slate-500
                    transition-all duration-200

                    group-hover:bg-red-100
                    group-hover:text-red-600
                "
            >
                <i
                    class="
                        fas fa-sign-out-alt
                        text-xs
                        transition-transform duration-200
                        group-hover:translate-x-0.5
                    "
                ></i>
            </span>


            <!-- Text -->
            <span>
                Logout
            </span>

        </button>

    </div>

</aside>


<!-- Alpine Collapse Plugin -->
<script
    defer
    src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.15.0/dist/cdn.min.js">
</script>

<!-- Alpine Core -->
<script
    defer
    src="https://cdn.jsdelivr.net/npm/alpinejs@3.15.0/dist/cdn.min.js">
</script>