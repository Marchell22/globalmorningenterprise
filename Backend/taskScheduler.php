<?php

/**
 * TaskScheduler Class
 * 
 * Kelas untuk mengelola dan menjadwalkan task dengan fitur prioritas berbeda
 */
class TaskScheduler
{
    private $tasks = [];
    private $dependencies = [];

    /**
     * Menambahkan task baru
     */
    public function addTask($taskId, $description, $priority, $scheduledTime)
    {
        if ($priority < 1 || $priority > 5) {
            throw new Exception("Priority harus antara 1-5");
        }

        $this->tasks[$taskId] = [
            'id' => $taskId,
            'description' => $description,
            'priority' => $priority,
            'scheduledTime' => $scheduledTime,
            'completed' => false
        ];

        return true;
    }

    /**
     * Mendapatkan task dengan prioritas tertinggi yang sudah waktunya dieksekusi
     */
    public function getNextExecutableTask($currentTime)
    {
        $executableTasks = [];

        foreach ($this->tasks as $taskId => $task) {
            if ($task['completed']) {
                continue;
            }

            if ($task['scheduledTime'] <= $currentTime) {
                if ($this->areDependenciesMet($taskId)) {
                    $executableTasks[] = $task;
                }
            }
        }

        // Manual sorting instead of usort
        $highestPriorityTask = null;
        $highestPriority = 6; // Higher than max priority

        foreach ($executableTasks as $task) {
            if ($task['priority'] < $highestPriority) {
                $highestPriority = $task['priority'];
                $highestPriorityTask = $task;
            }
        }

        return $highestPriorityTask;
    }

    /**
     * Menandai task sebagai selesai
     */
    public function markTaskComplete($taskId)
    {
        if (isset($this->tasks[$taskId])) {
            $this->tasks[$taskId]['completed'] = true;
            return true;
        }
        return false;
    }

    /**
     * Mengganti jadwal eksekusi task
     */
    public function rescheduleTask($taskId, $newScheduledTime)
    {
        if (isset($this->tasks[$taskId])) {
            $this->tasks[$taskId]['scheduledTime'] = $newScheduledTime;
            return true;
        }
        return false;
    }

    /**
     * Mendapatkan statistik task berdasarkan level prioritas
     */
    public function getTaskStats()
    {
        $stats = [
            1 => 0,
            2 => 0,
            3 => 0,
            4 => 0,
            5 => 0
        ];

        foreach ($this->tasks as $task) {
            if (!$task['completed']) {
                $stats[$task['priority']]++;
            }
        }

        return $stats;
    }

    /**
     * Menambahkan dependencies antar task
     */
    public function addDependency($taskId, $dependsOnTaskId)
    {
        if (!isset($this->dependencies[$taskId])) {
            $this->dependencies[$taskId] = [];
        }

        $this->dependencies[$taskId][] = $dependsOnTaskId;
        return true;
    }

    /**
     * Memeriksa apakah semua dependencies terpenuhi
     */
    private function areDependenciesMet($taskId)
    {
        if (!isset($this->dependencies[$taskId])) {
            return true;
        }

        foreach ($this->dependencies[$taskId] as $dependencyId) {
            if (
                !isset($this->tasks[$dependencyId]) ||
                !$this->tasks[$dependencyId]['completed']
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Menyimpan data ke file JSON
     */
    public function saveToFile($filename)
    {
        try {
            $filepath = '../' . $filename;
            $data = [
                'tasks' => $this->tasks,
                'dependencies' => $this->dependencies
            ];

            $jsonData = json_encode($data, JSON_PRETTY_PRINT);

            $fp = fopen($filename, 'w');
            if ($fp) {
                fwrite($fp, $jsonData);
                fclose($fp);
                return true;
            }
            return false;
        } catch (Exception $e) {
            echo "Error saving file: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Memuat data dari file JSON
     */
    public function loadFromFile($filename)
    {
        try {
            $filepath = '../' . $filename;
            $fp = @fopen($filepath, 'r');
            if ($fp) {
                $content = '';
                while (!feof($fp)) {
                    $content .= fread($fp, 8192);
                }
                fclose($fp);

                $data = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->tasks = $data['tasks'] ?? [];
                    $this->dependencies = $data['dependencies'] ?? [];
                    return true;
                }
            }
            return false;
        } catch (Exception $e) {
            echo "Error loading file: " . $e->getMessage();
            return false;
        }
    }
}
