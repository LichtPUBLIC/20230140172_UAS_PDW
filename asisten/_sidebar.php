<ul class="space-y-2 px-4">
    <?php
    // Definisikan item navigasi dalam bentuk array
    $navItems = [
        'dashboard' => ['label' => 'Dashboard', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" /></svg>'],
        'praktikum' => ['label' => 'Manajemen Praktikum', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd" /></svg>'],
        'modul' => ['label' => 'Manajemen Modul', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor"><path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10.392C2.057 15.71 3.245 16 4.5 16c1.255 0 2.443-.29 3.5-.804V4.804zM14.5 4c-1.255 0-2.443.29-3.5.804v10.392c1.057.514 2.245.804 3.5.804c1.255 0 2.443-.29 3.5-.804V4.804C16.943 4.29 15.755 4 14.5 4z" /></svg>'],
        'laporan' => ['label' => 'Laporan Masuk', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" /></svg>'],
        'users' => ['label' => 'Manajemen Pengguna', 'icon' => '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 20 20" fill="currentColor"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zm-2 5a5 5 0 00-4.546 2.916A5.986 5.986 0 005 16h1.732a4.006 4.006 0 000-1.822A5.002 5.002 0 007 11zm5 5a5.986 5.986 0 002.546-1.084A5.002 5.002 0 0013 11a4.006 4.006 0 000 1.822H15a5.986 5.986 0 002.546 1.084A5 5 0 0013 16h-2zm-8-4a3 3 0 116 0 3 3 0 01-6 0z" /></svg>']
    ];

    // Kelas untuk styling
    $activeClass = 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg';
    $inactiveClass = 'text-slate-300 hover:bg-slate-700 hover:text-white';
    ?>

    <?php foreach ($navItems as $page => $item): ?>
    <li>
        <a href="<?= $page ?>.php" class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200 <?= ($activePage == $page) ? $activeClass : $inactiveClass; ?>">
            <?= $item['icon'] ?>
            <span><?= $item['label'] ?></span>
        </a>
    </li>
    <?php endforeach; ?>
</ul>