<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

use App\Models\Training;
use App\Models\IpRight;
use App\Models\SalesAchievement;
use App\Models\DailyTask;
use App\Models\DirectPoint;
use App\Schemas\ParamSchema;

/**
 * Sheet 2: Detail poin per user per kategori
 */
class ReportPointProductivityDetailSheet implements FromCollection, WithHeadings, WithStyles, WithTitle, WithEvents
{
    protected $reports;
    protected $startDate;
    protected $endDate;
    protected $rows = [];

    public function __construct($reports, $startDate, $endDate)
    {
        $this->reports   = $reports;
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
    }

    public function collection()
    {
        $rows = [];

        foreach ($this->reports as $report) {
            $userId    = $report['user_id'];
            $userName  = $report['name'];
            $startDate = $this->startDate;
            $endDate   = $this->endDate;

            // ── Training ──────────────────────────────────────────────────
            $trainings = Training::where('user_id', $userId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get(['name', 'point', 'created_at']);

            foreach ($trainings as $item) {
                $rows[] = [
                    'user'     => $userName,
                    'category' => 'Training',
                    'name'     => $item->name,
                    'date'     => $item->created_at->format('d M Y'),
                    'point'    => $item->point,
                ];
            }

            // ── IP Right ──────────────────────────────────────────────────
            $ipRights = IpRight::where('user_id', $userId)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get(['name', 'point', 'created_at']);

            foreach ($ipRights as $item) {
                $rows[] = [
                    'user'     => $userName,
                    'category' => 'Hak Cipta',
                    'name'     => $item->name,
                    'date'     => $item->created_at->format('d M Y'),
                    'point'    => $item->point,
                ];
            }

            // ── Sales Achievement ─────────────────────────────────────────
            $sales = SalesAchievement::where('user_id', $userId)
                ->whereBetween('attempt_point_date', [$startDate, $endDate])
                ->get(['period', 'points', 'attempt_point_date']);

            foreach ($sales as $item) {
                $rows[] = [
                    'user'     => $userName,
                    'category' => 'Pencapaian Penjualan',
                    'name'     => $item->period,
                    'date'     => $item->attempt_point_date->format('d M Y'),
                    'point'    => $item->points,
                ];
            }

            // ── Daily Task (non-punishment) ───────────────────────────────
            $dailyTasks = DailyTask::where('assignment_user_id', $userId)
                ->where('point','!=',0)
                ->whereHas('statusRecords', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                      ->whereHas('taskStatus', fn($q2) => $q2->where('name', ParamSchema::COMPLATE));
                })
                ->whereDoesntHave('punishmentUser')
                ->get(['name', 'point', 'id']);

            foreach ($dailyTasks as $item) {
                $completedDate = $item->statusRecords()
                    ->whereBetween('date', [$startDate, $endDate])
                    ->whereHas('taskStatus', fn($q) => $q->where('name', ParamSchema::COMPLATE))
                    ->first();

                $rows[] = [
                    'user'     => $userName,
                    'category' => 'Tugas Harian',
                    'name'     => $item->name,
                    'date'     => $completedDate ? Carbon::parse($completedDate->date)->format('d M Y') : '-',
                    'point'    => $item->point,
                ];
            }

            // ── Punishment Task ───────────────────────────────────────────
            $punishments = DailyTask::where('assignment_user_id', $userId)
                ->whereHas('statusRecords', function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('date', [$startDate, $endDate])
                      ->whereHas('taskStatus', fn($q2) => $q2->where('name', ParamSchema::COMPLATE));
                })
                ->whereHas('punishmentUser')
                ->get(['name', 'point', 'id']);

            foreach ($punishments as $item) {
                $completedDate = $item->statusRecords()
                    ->whereBetween('date', [$startDate, $endDate])
                    ->whereHas('taskStatus', fn($q) => $q->where('name', ParamSchema::COMPLATE))
                    ->first();

                $rows[] = [
                    'user'     => $userName,
                    'category' => 'Punishment',
                    'name'     => $item->name,
                    'date'     => $completedDate ? Carbon::parse($completedDate->date)->format('d M Y') : '-',
                    'point'    => $item->point,
                ];
            }

            // ── Direct Points ─────────────────────────────────────────────
            $directPoints = DirectPoint::where('to_user_id', $userId)
                ->where('status', DirectPoint::STATUS_APPROVED)
                ->whereBetween('approved_at', [$startDate, $endDate])
                ->with(['fromUser', 'division'])
                ->get();

            foreach ($directPoints as $item) {
                $rows[] = [
                    'user'     => $userName,
                    'category' => 'Direct Point',
                    'name'     => 'Dari ' . ($item->fromUser->name ?? '-') . ' (' . ($item->division?->name ?? '-') . ')',
                    'date'     => $item->approved_at->format('d M Y'),
                    'point'    => $item->approved_point ?? $item->point,
                ];
            }
        }

        $this->rows = $rows;
        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'Nama User',
            'Kategori',
            'Keterangan',
            'Tanggal',
            'Poin',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Detail';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Auto-size columns
                foreach (range('A', 'E') as $col) {
                    $event->sheet->getDelegate()->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}
