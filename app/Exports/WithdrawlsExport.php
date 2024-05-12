<?php

namespace App\Exports;

use App\Models\Withdrawl;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\ConditionalFormatting\Wizard;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat\Wizard\Accounting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat\Wizard\Currency;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat\Wizard\Number;
use PhpOffice\PhpSpreadsheet\Style\Style;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WithdrawlsExport implements WithMapping, WithHeadings, WithStyles, ShouldAutoSize, FromQuery, WithColumnWidths, WithEvents
{
    use Exportable;

    protected string $id;

    public function __construct(string $id)
    {
        $this->id = $id;
    }

    public function query()
    {
        return Withdrawl::with(['contribution', 'house', 'user'])->where('contribution_id', $this->id);
    }

    public function map($withdrawl): array
    {
        return [
            $withdrawl->house->name,
            $withdrawl->is_contribute === true ? 'Mengisi' : 'Kosong',
            $withdrawl->value,
            $withdrawl->user->name
        ];
    }

    public function headings(): array
    {
        return [
            'RUMAH',
            'STATUS',
            'JUMLAH',
            'DIPERIKSA OLEH',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'C' => 12.86,
        ];
    }

    public function registerEvents(): array
    {
        return [
            // Using a class with an __invoke method.
            AfterSheet::class => function (AfterSheet $event) {
                $count = $this->query()->count();
                if ($count > 0) {
                    $redStyle = new Style(false, true);
                    $redStyle->getFill()->setFillType(Fill::FILL_SOLID)->getEndColor()->setARGB("FFFFC7CE");
                    $redStyle->getFont()->getColor()->setARGB("FF9C0006");

                    $greenStyle = new Style(false, true);
                    $greenStyle->getFill()->setFillType(Fill::FILL_SOLID)->getEndColor()->setARGB("FFC6EfCE");
                    $greenStyle->getFont()->getColor()->setARGB("FF006100");

                    $cellRange = 'B2:B' . strval($count + 1);
                    $conditionalStyles = [];
                    $wizardFactory = new Wizard($cellRange);
                    /** @var Wizard\CellValue $cellWizard */
                    $cellWizard = $wizardFactory->newRule(Wizard::CELL_VALUE);

                    $cellWizard->equals('Kosong')->setStyle($redStyle);
                    $conditionalStyles[] = $cellWizard->getConditional();

                    $cellWizard->equals('Mengisi')->setStyle($greenStyle);
                    $conditionalStyles[] = $cellWizard->getConditional();

                    $event->sheet->getDelegate()->getStyle($cellWizard->getCellRange())->setConditionalStyles($conditionalStyles);
                }
            }
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $accounting = new Accounting(
            'Rp',
            0,
            Number::WITH_THOUSANDS_SEPARATOR,
            Currency::LEADING_SYMBOL,
            Currency::SYMBOL_WITH_SPACING,
            locale: 'id_ID'
        );

        $sheet->getStyle('1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('B')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C2:C100')->getNumberFormat()->setFormatCode($accounting);
    }
}
