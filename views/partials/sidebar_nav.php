<?php 
foreach ($navigation as $item):
    $hasDropdown = is_array($item['url']);
    $isActive    = !$hasDropdown && str_contains($currentUrl, basename($item['url']));
?>
    <?php if ($hasDropdown): ?>
        <div x-data="{ open: false }">
            <button @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-2.5 rounded-lg
                           text-[#2D3748] hover:bg-[#6010ff]/8 hover:text-[#6010ff]
                           transition-colors duration-200 cursor-pointer group">
                <span class="flex items-center gap-3">
                    <i class="<?= $item['icon'] ?> w-4 text-[#2D3748] group-hover:text-[#6010ff] transition-colors duration-200"></i>
                    <span class="text-sm font-medium"><?= htmlspecialchars($item['label']) ?></span>
                </span>
                <span x-text="open ? '▲' : '▼'" class="text-[10px] text-gray-400 group-hover:text-[#6010ff]"></span>
            </button>
            <div x-show="open" x-transition class="ml-4 mt-1 space-y-1 border-l border-gray-100 pl-2">
                <?php foreach ($item['url'] as $sub):
                    $subActive = str_contains($currentUrl, $sub['url']);
                ?>
                    <a href="<?= htmlspecialchars($sub['url']) ?>"
                       class="flex items-center gap-3 px-4 py-2 rounded-lg text-sm transition-colors duration-200
                              <?= $subActive
                                    ? 'bg-[#6010ff]/10 text-[#6010ff] font-medium'
                                    : 'text-gray-500 hover:bg-[#6010ff]/8 hover:text-[#6010ff]' ?>">
                        <i class="<?= $sub['icon'] ?> w-4 text-xs text-[#2D1B69]"></i>
                        <span><?= htmlspecialchars($sub['label']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <a href="<?= htmlspecialchars($item['url']) ?>"
           class="flex items-center gap-3 px-4 py-2.5 rounded-lg transition-colors duration-200 cursor-pointer group
                  <?= $isActive
                        ? 'bg-[#6010ff]/10 text-[#6010ff] font-semibold border-l-3 border-[#6010ff]'
                        : 'text-gray-600 hover:bg-[#6010ff]/8 hover:text-[#6010ff]' ?>">
            <i class="<?= $item['icon'] ?> w-4 transition-transform duration-200 group-hover:scale-110
                       <?= $isActive ? 'text-[#6010ff]' : 'text-[#2D3748] group-hover:text-[#6010ff]' ?>"></i>
            <span class="text-sm font-medium"><?= htmlspecialchars($item['label']) ?></span>
        </a>
    <?php endif; ?>
<?php endforeach; ?>