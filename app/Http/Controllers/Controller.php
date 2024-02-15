<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function validationMessages(Validator|array|string $messages)
    {
        $message = $messages;
        if ($messages instanceof Validator) {
            $message = '<div class="fw-bold mb-1">Proses gagal</div><ul class="mb-0 ps-3">';
            foreach ($messages->errors()->toArray() as $errors) {
                $message .= '<li>' . $errors[0] . '</li>';
            }
            $message .= '</ul>';
        } elseif (is_array($messages)) {
            $message = '<div class="fw-bold mb-1">Proses gagal</div><ul class="mb-0 ps-3">';
            foreach ($messages as $error) {
                $message .= '<li>' . $error . '</li>';
            }
            $message .= '</ul>';
        }

        return view('partials.alert', [
            'message' => $message,
            'messageClass' => 'danger'
        ])->render();
    }

    protected function dateFilter(): array
    {
        $dateRange = session('filter.dates', []);
        if (empty($dateRange)) {
            $dateRange = [
                'start' => Carbon::today()->subDays(7),
                'end' => Carbon::today()
            ];
        }

        if (is_string($dateRange['start'])) $dateRange['start'] = Carbon::today();
        if (is_string($dateRange['end'])) $dateRange['end'] = Carbon::today();
        $formatStart = $dateRange['start']->format('Y-m-d');
        $formatEnd = $dateRange['end']->format('Y-m-d');

        list($dateRange['start'], $dateRange['end']) = var1LowestEqualVar2($formatStart, $formatEnd, [$dateRange['start'], $dateRange['end']]);

        $today = date('Y-m-d');

        if ($formatStart > $today) $dateRange['start'] = Carbon::today();
        if ($formatEnd > $today) $dateRange['end'] = Carbon::today();

        return $dateRange;
    }

    protected function keepMessage(): void
    {
        if (session()->has('message')) {
            session()->keep(['message', 'messageClass']);
        }
    }
}
