<?php

namespace App\Domains\TimeTracking\Support;

use App\Domains\Projects\Models\Project;
use App\Domains\TimeTracking\Models\TimeEntry;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

final class MonthlyTimeEntriesWorkbook
{
    // XLSX cannot read CSS variables, so the brand palette from CLAUDE.md
    // rule 4 is mirrored here as ARGB constants. This is the single place
    // in the app where brand hex values live outside resources/css/app.css.
    private const BRAND_DANUBE = 'FF5992C6';      // primary / header fill

    private const BRAND_TOREA_BAY = 'FF0A2A92';   // deep brand / titles

    private const BRAND_SHILO = 'FFE9B8C9';       // accent (totals underline)

    private const SURFACE_BAND = 'FFFAF6F2';      // warm-neutral zebra

    private const INK_PRIMARY = 'FF1F1A17';       // body text on light background

    private const INK_MUTED = 'FF6B6359';         // subtitle / muted text

    private const ON_PRIMARY = 'FFFFFFFF';        // text on Danube fill

    public static function build(Project $project, CarbonImmutable $month, string $scopeLabel, Collection $entries): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($month->translatedFormat('F Y'));

        self::writeTitleBlock($sheet, $project, $month, $scopeLabel, $entries->count());
        self::writeHeader($sheet);
        $lastBodyRow = self::writeBody($sheet, $entries);
        self::writeTotals($sheet, $lastBodyRow);
        self::applyColumnDimensions($sheet);
        $sheet->freezePane('A5');

        return self::renderToBytes($spreadsheet);
    }

    private static function writeTitleBlock($sheet, Project $project, CarbonImmutable $month, string $scopeLabel, int $entryCount): void
    {
        $title = sprintf('%s · %s', $project->name, $month->translatedFormat('F Y'));
        $subtitle = sprintf('%s · %d %s', $scopeLabel, $entryCount, __('Einträge'));

        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', $title);
        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => self::BRAND_TOREA_BAY]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue('A2', $subtitle);
        $sheet->getRowDimension(2)->setRowHeight(18);
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['size' => 11, 'color' => ['argb' => self::INK_MUTED]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getRowDimension(3)->setRowHeight(8);
    }

    private static function writeHeader($sheet): void
    {
        $headers = [
            'A4' => __('Datum'),
            'B4' => __('Mitarbeiter'),
            'C4' => __('Aufgabe'),
            'D4' => __('Notizen'),
            'E4' => __('Dauer'),
        ];
        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $sheet->getRowDimension(4)->setRowHeight(22);
        $sheet->getStyle('A4:E4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => self::ON_PRIMARY]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::BRAND_DANUBE]],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::BRAND_TOREA_BAY]]],
        ]);
        $sheet->getStyle('A4:D4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('E4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    /**
     * @param  Collection<int, TimeEntry>  $entries
     */
    private static function writeBody($sheet, Collection $entries): int
    {
        $now = Carbon::now();
        $row = 5;

        foreach ($entries as $index => $entry) {
            $isRunning = $entry->ended_at === null;
            $seconds = $isRunning
                ? max(1, (int) $now->diffInSeconds($entry->started_at, true))
                : (int) ($entry->duration_seconds ?? 0);

            $notes = trim((string) ($entry->notes ?? ''));
            if ($isRunning) {
                $notes = $notes === '' ? __('(läuft)') : $notes.' '.__('(läuft)');
            }

            $sheet->setCellValue("A{$row}", ExcelDate::PHPToExcel($entry->started_at));
            $sheet->setCellValue("B{$row}", $entry->user?->name ?? '—');
            $sheet->setCellValue("C{$row}", $entry->task?->name ?? '—');
            $sheet->setCellValueExplicit("D{$row}", $notes, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("E{$row}", $seconds / 86400);

            $isBand = $index % 2 === 1;
            $rowStyle = [
                'font' => ['color' => ['argb' => self::INK_PRIMARY]],
                'alignment' => ['vertical' => Alignment::VERTICAL_TOP],
            ];
            if ($isBand) {
                $rowStyle['fill'] = ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => self::SURFACE_BAND]];
            }
            $sheet->getStyle("A{$row}:E{$row}")->applyFromArray($rowStyle);
            $sheet->getStyle("D{$row}")->getAlignment()->setWrapText(true);
            $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $row++;
        }

        $lastBodyRow = $row - 1;
        if ($lastBodyRow >= 5) {
            $sheet->getStyle("A5:A{$lastBodyRow}")->getNumberFormat()->setFormatCode('dd.mm.yyyy');
            $sheet->getStyle("E5:E{$lastBodyRow}")->getNumberFormat()->setFormatCode('[h]:mm');
        }

        return $lastBodyRow;
    }

    private static function writeTotals($sheet, int $lastBodyRow): void
    {
        $totalsRow = max($lastBodyRow, 4) + 1;
        $hasBody = $lastBodyRow >= 5;

        $sheet->mergeCells("A{$totalsRow}:D{$totalsRow}");
        $sheet->setCellValue("A{$totalsRow}", __('Summe'));
        $sheet->setCellValue(
            "E{$totalsRow}",
            $hasBody ? "=SUM(E5:E{$lastBodyRow})" : 0,
        );

        $sheet->getStyle("A{$totalsRow}:E{$totalsRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['argb' => self::BRAND_TOREA_BAY]],
            'borders' => [
                'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => self::BRAND_TOREA_BAY]],
                'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['argb' => self::BRAND_SHILO]],
            ],
        ]);
        $sheet->getStyle("A{$totalsRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("E{$totalsRow}")
            ->getNumberFormat()
            ->setFormatCode('[h]:mm');
        $sheet->getStyle("E{$totalsRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    }

    private static function applyColumnDimensions($sheet): void
    {
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(22);
        $sheet->getColumnDimension('C')->setWidth(32);
        $sheet->getColumnDimension('D')->setWidth(50);
        $sheet->getColumnDimension('E')->setWidth(12);
    }

    private static function renderToBytes(Spreadsheet $spreadsheet): string
    {
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        ob_start();
        $writer->save('php://output');
        $bytes = ob_get_clean();
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return (string) $bytes;
    }
}
