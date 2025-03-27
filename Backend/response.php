<?php
// Impor kelas TaskScheduler
require_once 'taskScheduler.php';

// Membuat instance dari TaskScheduler
$scheduler = new TaskScheduler();

// Contoh penggunaan: Menambahkan task baru
echo "=== Menambahkan task baru ===\n";
$scheduler->addTask('task1', 'Task prioritas tinggi', 1, time() + 60); // 1 menit dari sekarang
$scheduler->addTask('task2', 'Task prioritas sedang', 3, time() + 120); // 2 menit dari sekarang
$scheduler->addTask('task3', 'Task prioritas rendah', 5, time() + 180); // 3 menit dari sekarang
$scheduler->addTask('task4', 'Task prioritas tinggi tapi dijadwalkan nanti', 1, time() + 300); // 5 menit dari sekarang

// Menambahkan dependencies
echo "=== Menambahkan dependencies ===\n";
$scheduler->addDependency('task2', 'task1'); // task2 bergantung pada task1

// Mendapatkan dan menjalankan task berikutnya
echo "=== Simulasi eksekusi task ===\n";
$currentTime = time();
$nextTask = $scheduler->getNextExecutableTask($currentTime);

if ($nextTask) {
    echo "Task berikutnya: {$nextTask['id']} - {$nextTask['description']} (Prioritas: {$nextTask['priority']})\n";

    // Menandai task sebagai selesai
    echo "Menyelesaikan task: {$nextTask['id']}\n";
    $scheduler->markTaskComplete($nextTask['id']);
} else {
    echo "Tidak ada task yang siap dieksekusi saat ini\n";
}

// Mendapatkan task berikutnya setelah satu selesai
$nextTask = $scheduler->getNextExecutableTask($currentTime);
if ($nextTask) {
    echo "Task berikutnya: {$nextTask['id']} - {$nextTask['description']} (Prioritas: {$nextTask['priority']})\n";
}

// Menjadwalkan ulang suatu task
echo "=== Menjadwalkan ulang task ===\n";
$scheduler->rescheduleTask('task3', time()); // Mengubah jadi sekarang
echo "Task3 dijadwalkan ulang\n";

// Mendapatkan statistik task
echo "=== Statistik task ===\n";
$stats = $scheduler->getTaskStats();
foreach ($stats as $priority => $count) {
    echo "Prioritas $priority: $count task\n";
}

// Menyimpan ke file
echo "=== Menyimpan data ke file ===\n";
if ($scheduler->saveToFile(__DIR__ . '/tasks.json')) {
    echo "Data berhasil disimpan\n";
} else {
    echo "Gagal menyimpan data\n";
}

// Memuat dari file
echo "=== Memuat data dari file ===\n";
$newScheduler = new TaskScheduler();
if ($newScheduler->loadFromFile(__DIR__ . '/tasks.json')) {
    echo "Data berhasil dimuat\n";
} else {
    echo "Gagal memuat data\n";
}

echo "=== Selesai ===\n";
