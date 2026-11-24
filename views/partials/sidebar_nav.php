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

// Shared styles. Current page = filled violet; parent of the current page = violet tint.
$navBase  = 'group flex min-h-10 w-full items-center gap-3 rounded-xl px-3 py-2 text-left text-[15px] transition-colors duration-150 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-500';
$navIdle  = 'font-medium text-slate-700 hover:bg-slate-100 hover:text-slate-900';
$navOpen  = 'font-semibold bg-violet-50 text-violet-700';
$navHere  = 'font-semibold bg-violet-600 text-white';
$iconBase = 'flex w-5 shrink-0 justify-center text-[15px]';
?>

<p class="se-nav-section mb-2 px-3 font-mono text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">Menu</p>

<?php foreach ($navigation as $item):

    $hasDropdown = is_array($item['url']);

    $isActive = !$hasDropdown && $isSidebarLinkActive($item['url']);

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
    $navIcon  = htmlspecialchars($item['icon']);
    ?>

<?php if (!empty($item['section'])): ?>
    <p class="se-nav-section mb-2 mt-6 px-3 font-mono text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500"><?= htmlspecialchars($item['section']) ?></p>
<?php endif; ?>

<?php if ($hasDropdown): ?>

    <!-- DROPDOWN -->
    <div x-data="{ open: <?= $hasActiveSub ? 'true' : 'false' ?> }">

        <button
            type="button"
            @click="open = !open"
            aria-expanded="<?= $hasActiveSub ? 'true' : 'false' ?>"
            :aria-expanded="open.toString()"
            class="<?= $navBase ?> justify-between <?= $hasActiveSub ? $navOpen : $navIdle ?>"
        >
            <span class="flex min-w-0 items-center gap-3">
                <span class="<?= $iconBase ?> <?= $hasActiveSub ? 'text-violet-600' : 'text-slate-500 group-hover:text-slate-700' ?>">
                    <i class="<?= $navIcon ?>" aria-hidden="true"></i>
                </span>
                <span class="truncate"><?= $navLabel ?></span>
            </span>

            <i
                class="fas fa-chevron-down shrink-0 text-[11px] transition-transform duration-200 <?= $hasActiveSub ? 'text-violet-600' : 'text-slate-400' ?>"
                :class="open ? 'rotate-180' : ''"
                aria-hidden="true"
            ></i>
        </button>

        <!-- SUBMENU: indented under the parent icon, with a guide line -->
        <div
            <?= $hasActiveSub ? '' : 'hidden' ?>
            :hidden="!open"
            class="ml-[22px] mt-1 space-y-1 border-l border-slate-200 pl-3"
        >
            <?php foreach ($item['url'] as $sub):
                $subActive = $isSidebarLinkActive($sub['url']);
                ?>
                <a
                    href="<?= htmlspecialchars($sub['url']) ?>"
                    <?= $subActive ? 'aria-current="page"' : '' ?>
                    class="<?= $navBase ?> <?= $subActive ? $navHere : $navIdle ?>"
                >
                    <span class="flex w-4 shrink-0 justify-center text-[13px] <?= $subActive ? 'text-current' : 'text-slate-500 group-hover:text-slate-700' ?>">
                        <i class="<?= htmlspecialchars($sub['icon']) ?>" aria-hidden="true"></i>
                    </span>
                    <span class="min-w-0 truncate"><?= htmlspecialchars($sub['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

<?php else: ?>

    <!-- LINK -->
    <a
        href="<?= htmlspecialchars($item['url']) ?>"
        <?= $isActive ? 'aria-current="page"' : '' ?>
        class="<?= $navBase ?> <?= $isActive ? $navHere : $navIdle ?>"
    >
        <span class="<?= $iconBase ?> <?= $isActive ? 'text-current' : 'text-slate-500 group-hover:text-slate-700' ?>">
            <i class="<?= $navIcon ?>" aria-hidden="true"></i>
        </span>
        <span class="min-w-0 truncate"><?= $navLabel ?></span>
    </a>

<?php endif; ?>

<?php endforeach; ?>
