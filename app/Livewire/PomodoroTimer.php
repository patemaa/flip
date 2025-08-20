<?php

namespace App\Livewire;

use App\Models\Pomodoro;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Livewire\Component;

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
    public $pomodoro;
    public $secondsToCountdown = 1500; // örn. 25 dakika
    public $seconds = 0;
    public $isPaused = false;
    public $isFinished = false;
    public $showDialog = false;
    public $countdown = 15; // pause dialog countdown
    public $dailyEmergencyBreaks = 3;


    protected $listeners = [
        'incrementBreakCount'
    ];

    public function mount($pomodoro_id)
    {
        $this->selectedDate = now();
        $this->loadDateData();
        $this->pomodoro = Pomodoro::findOrFail($pomodoro_id);
        $this->seconds = $this->secondsToCountdown;
        $this->dailyEmergencyBreaks = Session::get('daily_emergency_breaks', 3);
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

    public function incrementBreakCount()
    {
        $this->pomodoro->increment('break_count');
        $this->pomodoro->refresh();
    }

    public function getGrade($pomodoro = null)
    {
        $pomodoro = $pomodoro ?? $this->pomodoro;
        $breaks = $pomodoro->break_count ?? 0;
        return $this->calculateGrade($breaks, 0); // İkinci parametre kullanılmıyor
    }
    private function calculateGrade($breakCount)
    {
        $grades = ['A', 'B', 'C', 'D', 'E', 'F'];
        $index = $breakCount;
        return $grades[$index] ?? 'F';
    }


    public function pausePomodoro($pomodoroId)
    {
        $pomodoro = Pomodoro::find($pomodoroId);
        if ($pomodoro) {
            $pomodoro->pauseSession();
            $this->loadDateData();
        }
    }

    public function pause()
    {
        if ($this->seconds < 15) {
            // kısa süre kaydedilmez
            $this->resetTimer();
            session()->flash('message', 'Çok kısa süre kaydedilmiyor.');
            return;
        }

        $this->isPaused = true;
        if ($this->seconds >= 15) {
            $this->showDialog = true;
            $this->countdown = 15;
        }
    }

    public function resume()
    {
        $this->isPaused = false;
        $this->showDialog = false;
    }

    public function earlyFinish()
    {
        $this->isPaused = false;
        $this->showDialog = false;
        $this->applyBreak();
    }

    public function complete()
    {
        $this->isFinished = true;
        $this->showDialog = false;
        // Görevi tamamla
        session()->flash('message', 'Görev tamamlandı.');
    }

    public function emergencyBreak()
    {
        if ($this->dailyEmergencyBreaks <= 0) return;

        $this->dailyEmergencyBreaks--;
        Session::put('daily_emergency_breaks', $this->dailyEmergencyBreaks);
        $this->earlyFinish();
    }

    private function applyBreak()
    {
        // Kullanıcının seviyesini düşür
        session()->flash('message', 'Mola verildi, seviyen düştü.');
    }

    private function resetTimer()
    {
        $this->seconds = $this->secondsToCountdown;
        $this->isPaused = false;
        $this->isFinished = false;
        $this->showDialog = false;
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
