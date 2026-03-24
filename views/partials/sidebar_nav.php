<?php 
foreach ($nav as $item):
    $hasDropdown = is_array($item['url']);
    $isActive    = !$hasDropdown && str_contains($currentUrl, basename($item['url']));
?>
    <?php if ($hasDropdown): ?>
        <div x-data="{ open: false }">
            <button @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-2 rounded-lg
                           text-gray-300 hover:bg-gray-700 hover:text-white transition-colors duration-200 cursor-pointer">
                <span class="flex items-center gap-3">
                    <span><?= $item['icon'] ?></span>
                    <span><?= htmlspecialchars($item['label']) ?></span>
                </span>
                <span x-text="open ? '▲' : '▼'" class="text-xs text-gray-400"></span>
            </button>
            <div x-show="open" x-transition class="ml-4 mt-1 space-y-1">
                <?php foreach ($item['url'] as $sub):
                    $subActive = str_contains($currentUrl, $sub['url']);
                ?>
                    <a href="<?= htmlspecialchars($sub['url']) ?>"
                       class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-colors duration-200
                              <?= $subActive ? 'border-l-3 border-[#E94560] bg-[#16213E] text-white' : 'text-gray-400 hover:bg-gray-700 hover:text-white' ?>">
                        <span><?= $sub['icon'] ?></span>
                        <span><?= htmlspecialchars($sub['label']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <a href="<?= htmlspecialchars($item['url']) ?>"
           class="flex items-center gap-3 px-4 py-2 rounded-lg transition-colors duration-200 cursor-pointer
                  <?= $isActive ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white' ?>">
            <span><?= $item['icon'] ?></span>
            <span><?= htmlspecialchars($item['label']) ?></span>
        </a>
    <?php endif; ?>
<?php endforeach; ?>