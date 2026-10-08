<?php
foreach ($navigation as $item):

    $hasDropdown = is_array($item['url']);

    $isActive = !$hasDropdown &&
        str_contains($currentUrl, basename($item['url']));

    /*
     * Check whether one of the dropdown items is currently active.
     * This allows the dropdown to automatically stay open
     * when the user is inside a submenu.
     */
    $hasActiveSub = false;

    if ($hasDropdown) {
        foreach ($item['url'] as $sub) {
            if (str_contains($currentUrl, basename($sub['url']))) {
                $hasActiveSub = true;
                break;
            }
        }
    }
    ?>

    <?php if ($hasDropdown): ?>

        <!-- =========================================
             DROPDOWN NAVIGATION
        ========================================== -->

        <div
            x-data="{ open: <?= $hasActiveSub ? 'true' : 'false' ?> }"
            class="space-y-1"
        >

            <!-- Dropdown Button -->
            <button
                type="button"
                @click="open = !open"
                class="
                    group relative w-full
                    flex items-center justify-between
                    rounded-xl
                    px-3 py-2.5
                    text-sm font-medium
                    transition-all duration-200
                    cursor-pointer
                    focus:outline-none
                    <?= $hasActiveSub
                            ? 'bg-violet-50 text-violet-700'
                            : 'text-slate-600 hover:bg-slate-50 hover:text-violet-600'
        ?>
                "
            >

                <span class="flex items-center gap-3 min-w-0">

                    <!-- Icon -->
                    <span
                        class="
                            flex h-8 w-8 shrink-0
                            items-center justify-center
                            rounded-lg
                            transition-all duration-200
                            <?= $hasActiveSub
                    ? 'bg-violet-100 text-violet-600'
                    : 'bg-slate-100 text-slate-500 group-hover:bg-violet-50 group-hover:text-violet-600'
        ?>
                        "
                    >
                        <i class="<?= htmlspecialchars($item['icon']) ?> text-xs"></i>
                    </span>

                    <!-- Label -->
                    <span class="truncate">
                        <?= htmlspecialchars($item['label']) ?>
                    </span>

                </span>


                <!-- Arrow -->
                <span
                    class="
                        flex h-6 w-6 shrink-0
                        items-center justify-center
                        rounded-md
                        text-slate-400
                        transition-all duration-200
                        group-hover:text-violet-600
                    "
                    :class="open ? 'rotate-180 text-violet-600' : ''"
                >
                    <i class="fas fa-chevron-down text-[9px]"></i>
                </span>

            </button>


            <!-- Dropdown Content -->
            <div
                x-show="open"
                x-collapse
                class="ml-4 pl-3 border-l border-slate-200 space-y-1"
            >

                <?php foreach ($item['url'] as $sub):

                    $subActive = str_contains(
                        $currentUrl,
                        basename($sub['url'])
                    );
                    ?>

                    <a
                        href="<?= htmlspecialchars($sub['url']) ?>"
                        class="
                            group relative
                            flex items-center gap-3
                            rounded-md
                            px-3 py-2
                            text-sm
                            transition-all duration-200
                            <?= $subActive
                                    ? 'bg-violet-50 text-violet-700 font-semibold'
                                    : 'text-slate-500 hover:bg-slate-50 hover:text-violet-600'
                    ?>
                        "
                    >

                        <!-- Active Indicator -->
                        <?php if ($subActive): ?>

                            <span
                                class="
                                    absolute -left-[17px]
                                    h-5 w-0.5
                                    rounded-full
                                    bg-violet-600
                                "
                            ></span>

                        <?php endif; ?>


                        <!-- Sub Icon -->
                        <span
                            class="
                                flex h-6 w-6 shrink-0
                                items-center justify-center
                                rounded-md
                                <?= $subActive
                            ? 'text-violet-600'
                            : 'text-slate-400 group-hover:text-violet-600'
                    ?>
                            "
                        >
                            <i class="<?= htmlspecialchars($sub['icon']) ?> text-[10px]"></i>
                        </span>

                        <span class="truncate">
                            <?= htmlspecialchars($sub['label']) ?>
                        </span>

                    </a>

                <?php endforeach; ?>

            </div>

        </div>


    <?php else: ?>

        <!-- =========================================
             NORMAL NAVIGATION ITEM
        ========================================== -->

        <a
            href="<?= htmlspecialchars($item['url']) ?>"
            class="
                group relative
                flex items-center gap-3
                rounded-lg
                px-3 py-2.5
                text-sm
                transition-all duration-200
                <?= $isActive
                    ? 'bg-violet-50 text-violet-700 font-semibold'
                    : 'text-slate-600 hover:bg-slate-50 hover:text-violet-600'
        ?>
            "
        >

            <!-- Active Indicator -->
            <?php if ($isActive): ?>

                <span
                    class="
                        absolute left-0 top-1/2
                        -translate-y-1/2
                        h-6 w-1
                        rounded-r-full
                        bg-violet-600
                    "
                ></span>

            <?php endif; ?>


            <!-- Icon -->
            <span
                class="
                    flex h-8 w-8 shrink-0
                    items-center justify-center
                    rounded-lg
                    transition-all duration-200
                    <?= $isActive
                ? 'bg-violet-100 text-violet-600'
                : 'bg-slate-100 text-slate-500 group-hover:bg-violet-50 group-hover:text-violet-600'
    ?>
                "
            >
                <i
                    class="<?= htmlspecialchars($item['icon']) ?> text-xs
                    transition-transform duration-200
                    group-hover:scale-110"
                ></i>
            </span>


            <!-- Label -->
            <span class="truncate">
                <?= htmlspecialchars($item['label']) ?>
            </span>

        </a>

    <?php endif; ?>

<?php endforeach; ?>