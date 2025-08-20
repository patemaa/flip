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
        $this->selectedDate = now();
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
        $this->datePomodoros = Pomodoro::whereDate('started_at', $this->selectedDate)
            ->orderBy('started_at', 'desc')
            ->get();

        $daySeconds = $this->datePomodoros->where('status', 'completed')->sum('duration_seconds');
        $this->totalStudyTime = sprintf('%02d:%02d:%02d',
            floor($daySeconds / 3600),
            floor(($daySeconds % 3600) / 60),
            $daySeconds % 60
        );

        $dailyTargetSeconds = 2 * 3600;
        $this->completionPercentage = $dailyTargetSeconds > 0 ? round(($daySeconds / $dailyTargetSeconds) * 100) : 0;



        $completedBreaks = $this->datePomodoros->where('status', 'completed')
            ->whereIn('type', ['short_break', 'long_break'])
            ->count();
        $this->gradeLevel = $this->calculateGrade($this->completionPercentage, $completedBreaks);

        // İlk pomodoro tarihi (genel)
        $firstPomodoro = Pomodoro::orderBy('started_at')->first();
        $this->firstPomodoroDate = $firstPomodoro ? $firstPomodoro->started_at->format('d.m.Y') : 'Henüz başlanmadı';
    }

    private function calculateGrade($percentage, $breakCount)
    {
        $adjustedPercentage = $percentage - ($breakCount * 5);
        if ($adjustedPercentage < 0) {
            $adjustedPercentage = 0;
        }

        if ($adjustedPercentage >= 100) return 'A';
        if ($adjustedPercentage >= 80) return 'B';
        if ($adjustedPercentage >= 60) return 'C';
        if ($adjustedPercentage >= 40) return 'D';
        if ($adjustedPercentage >= 20) return 'E';
        return 'F';
    }
    public function getGrade($pomodoro)
    {
        return $this->calculateGrade($pomodoro->percentage, $pomodoro->break_count);
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
