<?php

namespace App\Livewire;

use App\Models\Pomodoro;
use Livewire\Component;
use Carbon\Carbon;

class PomodoroTimer extends Component
{
    public $selectedDate;
    public $showArchive = true;
    public $datePomodoros;
    public $totalStudyTime;
    public $firstPomodoroDate;
    public $dailyTargetTime = '02:00:00';
    public $completionPercentage = 0;
    public $gradeLevel = 'F';

    public function mount()
    {
        $this->selectedDate = today();
        $this->loadDateData();
    }

    public function previousDay()
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->subDay();
        $this->loadDateData();
    }

    public function nextDay()
    {
        $this->selectedDate = Carbon::parse($this->selectedDate)->addDay();
        $this->loadDateData();
    }

    public function toggleArchive()
    {
        $this->showArchive = !$this->showArchive;
    }

    public function loadDateData()
    {
        $this->datePomodoros = Pomodoro::whereDate('created_at', $this->selectedDate)
            ->orderBy('created_at', 'desc')
            ->get();

        $daySeconds = $this->datePomodoros->where('status', 'completed')->sum('duration_seconds');
        $this->totalStudyTime = sprintf('%02d:%02d:%02d',
            floor($daySeconds / 3600),
            floor(($daySeconds % 3600) / 60),
            $daySeconds % 60
        );

        $dailyTargetSeconds = 2 * 3600;
        $this->completionPercentage = $dailyTargetSeconds > 0 ? round(($daySeconds / $dailyTargetSeconds) * 100) : 0;

        $this->gradeLevel = $this->calculateGrade($this->completionPercentage);

        // İlk pomodoro tarihi (genel)
        $firstPomodoro = Pomodoro::orderBy('created_at')->first();
        $this->firstPomodoroDate = $firstPomodoro ? $firstPomodoro->created_at->format('d.m.Y') : 'Henüz başlanmadı';
    }

    private function calculateGrade($percentage)
    {
        if ($percentage >= 100) return 'A';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 60) return 'C';
        if ($percentage >= 40) return 'D';
        if ($percentage >= 20) return 'E';
        return 'F';
    }

    public function pausePomodoro($pomodoroId)
    {
        $pomodoro = Pomodoro::find($pomodoroId);
        if ($pomodoro) {
            $pomodoro->pauseSession();
            $this->loadDateData();
        }
    }

    public function resumePomodoro($pomodoroId)
    {
        $pomodoro = Pomodoro::find($pomodoroId);
        if ($pomodoro) {
            $pomodoro->resumeSession();
            $this->loadDateData();
        }
    }

    public function completePomodoro($pomodoroId)
    {
        $pomodoro = Pomodoro::find($pomodoroId);
        if ($pomodoro) {
            $pomodoro->completeSession();
            $this->loadDateData();
        }
    }

    public function deletePomodoro($pomodoroId)
    {
        $pomodoro = Pomodoro::find($pomodoroId);
        if ($pomodoro) {
            $pomodoro->delete();
            $this->loadDateData();
        }
    }

    public function render()
    {
        return view('livewire.pomodoro-timer');
    }
}
