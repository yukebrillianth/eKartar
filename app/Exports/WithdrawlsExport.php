<?php

namespace App\Exports;

use App\Models\Balance;
use App\Models\Withdrawl;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
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
    protected string $date;
    protected bool $encrypt;
    protected string $password;

    public function __construct(string $id, string $date, bool $encrypt, string $password = null)
    {
        $this->id = $id;
        $this->date = $date;
        $this->encrypt = $encrypt;
        if ($encrypt) {
            $this->password = $password;
        }
    }

    public function query()
    {
        return Withdrawl::with(['contribution', 'house', 'user'])->join('houses', 'withdrawls.house_id', '=', 'houses.id')->where('contribution_id', $this->id)->orderBy('houses.name', 'ASC');
    }

    public function map($withdrawl): array
    {
        return [
            [
                $withdrawl->house->name,
                $withdrawl->is_rapel ? 'Sudah Rapel' : ($withdrawl->is_contribute ? 'Mengisi' : 'Kosong'),
                $withdrawl->value,
                $withdrawl->user->name
            ],

        ];
    }

    public function headings(): array
    {
        $headers1 = ["LAPORAN PENGAMBILAN JIMPITAN"];
        $headers2 = ["eKartar 2024 © yukebrillianth.my.id"];
        $headers3 = [Carbon::parse($this->date)->isoFormat('dddd, D MMMM Y')];
        $headers4 = [
            'Total Saldo',
            Balance::latest()->pluck('value')->first()
        ];
        $headers5 = [
            'Perolehan',
            $this->query()->sum('value')
        ];
        $headers6 = [
            'Rumah Terisi',
            $this->query()->where('is_contribute', true)->count()
        ];
        $headers7 = [
            'Rumah Kosong',
            $this->query()->where('is_contribute', false)->count()
        ];
        $headers8 = [
            'RUMAH',
            'STATUS',
            'JUMLAH',
            'DIPERIKSA OLEH',
        ];
        return [$headers1, $headers2, $headers3, [""], $headers4, $headers5, $headers6, $headers7, [""], $headers8];
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

                    $yellowStyle = new Style(false, true);
                    $yellowStyle->getFill()->setFillType(Fill::FILL_SOLID)->getEndColor()->setARGB("FFFFEB9C");
                    $yellowStyle->getFont()->getColor()->setARGB("FF9C5700");

                    $cellRange = 'B11:B' . strval($count + 10);
                    $conditionalStyles = [];
                    $wizardFactory = new Wizard($cellRange);
                    /** @var Wizard\CellValue $cellWizard */
                    $cellWizard = $wizardFactory->newRule(Wizard::CELL_VALUE);

                    $cellWizard->equals('Kosong')->setStyle($redStyle);
                    $conditionalStyles[] = $cellWizard->getConditional();

                    $cellWizard->equals('Mengisi')->setStyle($greenStyle);
                    $conditionalStyles[] = $cellWizard->getConditional();

                    $cellWizard->equals('Sudah Rapel')->setStyle($yellowStyle);
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
        $sheet->getStyle('1')->getFont()->setBold(true);
        $sheet->getStyle('5')->getFont()->setBold(true);
        $sheet->getStyle('6')->getFont()->setBold(true);
        $sheet->getStyle('7')->getFont()->setBold(true);
        $sheet->getStyle('8')->getFont()->setBold(true);
        $sheet->getStyle('2')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('3')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('5')->getAlignment()->setHorizontal(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('6')->getAlignment()->setHorizontal(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('7')->getAlignment()->setHorizontal(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('8')->getAlignment()->setHorizontal(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('9')->getAlignment()->setHorizontal(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('10')->getAlignment()->setHorizontal(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('B')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C2:C100')->getNumberFormat()->setFormatCode($accounting);
        $sheet->getStyle('B5')->getNumberFormat()->setFormatCode($accounting);
        $sheet->getStyle('B6')->getNumberFormat()->setFormatCode($accounting);
        $sheet->mergeCells('A1:D1');
        $sheet->mergeCells('A2:D2');
        $sheet->mergeCells('A3:D3');

        if ($this->encrypt) {
            $sheet->getProtection()->setPassword($this->password);
            $sheet->getProtection()->setSheet(true);
        }
    }
}
