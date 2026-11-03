<?php
// Match the route and only the query parameters required by the navigation link.
// Extra page filters (for example, period_id) do not change the active section.
$sidebarRequestUri = $_SERVER['REQUEST_URI'] ?? ($currentUrl ?? '');
$sidebarRoute = basename(rtrim(parse_url($sidebarRequestUri, PHP_URL_PATH) ?? '', '/'));
parse_str(parse_url($sidebarRequestUri, PHP_URL_QUERY) ?? '', $sidebarQuery);

$isSidebarLinkActive = static function (string $url) use ($sidebarRoute, $sidebarQuery): bool {
    $linkRoute = basename(rtrim(parse_url($url, PHP_URL_PATH) ?? '', '/'));
    if ($linkRoute === '' || $linkRoute !== $sidebarRoute) {
        return false;
    }

    parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $linkQuery);
    foreach ($linkQuery as $key => $value) {
        if (!array_key_exists($key, $sidebarQuery) || $sidebarQuery[$key] !== $value) {
            return false;
        }
    }

    return true;
};
?>

<?php foreach ($navigation as $item):

    $hasDropdown = is_array($item['url']);

    $isActive = !$hasDropdown &&
        $isSidebarLinkActive($item['url']);

    $hasActiveSub = false;

    if ($hasDropdown) {
        foreach ($item['url'] as $sub) {
            if ($isSidebarLinkActive($sub['url'])) {
                $hasActiveSub = true;
                break;
            }
        }
    }

    $navLabel = htmlspecialchars($item['label']);
    $navIcon = htmlspecialchars($item['icon']);
    ?>

<?php if ($hasDropdown): ?>

    <!-- DROPDOWN NAVIGATION -->
    <div
        x-data="{ open: <?= $hasActiveSub ? 'true' : 'false' ?> }"
        class="space-y-1"
    >

        <!-- PARENT BUTTON -->
        <button
            type="button"
            @click="open = !open"
            aria-expanded="<?= $hasActiveSub ? 'true' : 'false' ?>"
            :aria-expanded="open.toString()"
            class="
                group flex min-h-11 w-full
                items-center justify-between gap-2
                rounded-xl px-3 py-2
                text-left text-sm font-medium
                transition-colors duration-200
                focus-visible:outline-none
                focus-visible:ring-2 focus-visible:ring-violet-500

                <?= $hasActiveSub
                        ? 'bg-violet-50 text-violet-700'
                        : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
    ?>
            "
        >
            <span class="flex min-w-0 items-center gap-3">

                <span
                    class="
                        flex h-8 w-8 shrink-0
                        items-center justify-center
                        rounded-lg text-sm
                        transition-colors

                        <?= $hasActiveSub
                ? 'bg-violet-100 text-violet-700'
                : 'bg-slate-100 text-slate-500 group-hover:bg-violet-50 group-hover:text-violet-600'
    ?>
                    "
                >
                    <i
                        class="<?= $navIcon ?>"
                        aria-hidden="true"
                    ></i>
                </span>

                <span class="truncate">
                    <?= $navLabel ?>
                </span>
            </span>

            <i
                class="fas fa-chevron-down shrink-0
                       text-[10px] text-slate-400
                       transition-transform duration-200"
                :class="open ? 'rotate-180 text-violet-600' : ''"
                aria-hidden="true"
            ></i>
        </button>

        <!-- SUBMENU -->
        <div
            <?= $hasActiveSub ? '' : 'hidden' ?>
            :hidden="!open"
            class="ml-7 space-y-1 border-l
                   border-slate-200 pl-3"
        >

            <?php foreach ($item['url'] as $sub):

                $subActive = $isSidebarLinkActive($sub['url']);

                $subUrl = htmlspecialchars($sub['url']);
                $subLabel = htmlspecialchars($sub['label']);
                $subIcon = htmlspecialchars($sub['icon']);
                ?>

                <a
                    href="<?= $subUrl ?>"
                    <?= $subActive ? 'aria-current="page"' : '' ?>
                    class="
                        group relative flex min-h-10
                        items-center gap-3
                        rounded-lg px-3 py-2
                        text-sm transition-colors duration-200
                        focus-visible:outline-none
                        focus-visible:ring-2
                        focus-visible:ring-violet-500

                        <?= $subActive
                                ? 'bg-violet-50 font-semibold text-violet-700'
                                : 'text-slate-500 hover:bg-slate-50 hover:text-slate-800'
                ?>
                    "
                >

                    <?php if ($subActive): ?>
                        <span
                            class="absolute -left-3 top-1/2
                                   h-5 w-0.5 -translate-y-1/2
                                   rounded-full bg-violet-600"
                        ></span>
                    <?php endif; ?>

                    <span
                        class="
                            flex h-6 w-6 shrink-0
                            items-center justify-center text-xs

                            <?= $subActive
                        ? 'text-violet-600'
                        : 'text-slate-400 group-hover:text-violet-600'
                ?>
                        "
                    >
                        <i
                            class="<?= $subIcon ?>"
                            aria-hidden="true"
                        ></i>
                    </span>

                    <span class="min-w-0 truncate">
                        <?= $subLabel ?>
                    </span>
                </a>

            <?php endforeach; ?>

        </div>
    </div>

<?php else: ?>

    <!-- NORMAL NAVIGATION -->
    <a
        href="<?= htmlspecialchars($item['url']) ?>"
        <?= $isActive ? 'aria-current="page"' : '' ?>
        class="
            group relative flex min-h-11
            items-center gap-3
            rounded-xl px-3 py-2
            text-sm transition-colors duration-200
            focus-visible:outline-none
            focus-visible:ring-2
            focus-visible:ring-violet-500

            <?= $isActive
                ? 'bg-violet-50 font-semibold text-violet-700'
                : 'font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900'
    ?>
        "
    >

        <!-- ACTIVE INDICATOR -->
        <?php if ($isActive): ?>
            <span
                class="absolute inset-y-2 left-0
                       w-1 rounded-r-full bg-violet-600"
            ></span>
        <?php endif; ?>

        <!-- ICON -->
        <span
            class="
                flex h-8 w-8 shrink-0
                items-center justify-center
                rounded-lg text-sm
                transition-colors

                <?= $isActive
            ? 'bg-violet-100 text-violet-700'
            : 'bg-slate-100 text-slate-500 group-hover:bg-violet-50 group-hover:text-violet-600'
    ?>
            "
        >
            <i
                class="<?= $navIcon ?>"
                aria-hidden="true"
            ></i>
        </span>

        <!-- LABEL -->
        <span class="min-w-0 truncate">
            <?= $navLabel ?>
        </span>

    </a>

<?php endif; ?>

<?php endforeach; ?>
