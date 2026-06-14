<?php

namespace App\View\Components\ui;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DateTimeDisplay extends Component
{
    public ?string $formattedDate = null;

    public ?string $relativeText = null;

    public function __construct(
        public mixed $datetime = null,
        public string $format = 'd/m/Y H:i',
        public bool $showRelative = true,
        public string $emptyText = '-',
        public string $layout = 'stacked',
        public ?string $prefix = null,
    ) {
        $date = $this->parseDate($datetime);

        $this->formattedDate = $date?->format($format);
        $this->relativeText = $date && $showRelative ? self::toThaiRelative($date) : null;
    }

    public static function toThaiRelative(CarbonInterface $date): string
    {
        $now = now();

        if ($date->greaterThan($now)) {
            return 'อีกไม่นาน';
        }

        $seconds = (int) $date->diffInSeconds($now, true);

        if ($seconds < 45) {
            return 'เมื่อสักครู่';
        }

        $minutes = (int) $date->diffInMinutes($now, true);

        if ($minutes < 60) {
            return $minutes.' นาทีที่แล้ว';
        }

        $hours = (int) $date->diffInHours($now, true);

        if ($hours < 24) {
            return $hours.' ชั่วโมงที่แล้ว';
        }

        $days = (int) $date->diffInDays($now, true);

        if ($days < 30) {
            return $days.' วันที่แล้ว';
        }

        $months = (int) $date->diffInMonths($now, true);

        if ($months < 12) {
            return $months.' เดือนที่แล้ว';
        }

        $years = (int) $date->diffInYears($now, true);

        return $years.' ปีที่แล้ว';
    }

    protected function parseDate(mixed $datetime): ?Carbon
    {
        if (blank($datetime)) {
            return null;
        }

        if ($datetime instanceof CarbonInterface) {
            return Carbon::instance($datetime);
        }

        try {
            return Carbon::parse($datetime);
        } catch (\Throwable) {
            return null;
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.ui.date-time-display');
    }
}
