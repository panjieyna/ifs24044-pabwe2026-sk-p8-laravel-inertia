<?php

namespace App\Features\Post\Services;

use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PostExcelService
{
    /**
     * Header kolom berkas Excel (format ekspor = format impor).
     *
     * @var array<int, string>
     */
    public const HEADERS = ['No', 'Judul', 'Kategori', 'Ringkasan', 'Konten', 'Status', 'Tanggal Publikasi', 'Penulis', 'Dibuat Pada'];

    /**
     * Nilai kolom "Status" yang dianggap sudah dipublikasikan.
     *
     * @var array<int, string>
     */
    public const PUBLISHED_KEYWORDS = ['published', 'publish', 'terbit', 'dipublikasikan', 'ya', 'yes', 'true', '1'];

    public function __construct(
        protected PostService $postService
    ) {}

    /**
     * Bangun spreadsheet berisi daftar artikel.
     *
     * @param  Collection<int, Post>  $posts
     */
    public function buildSpreadsheet(Collection $posts, string $sheetTitle = 'Artikel'): Spreadsheet
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr($sheetTitle, 0, 31));
        $sheet->fromArray(self::HEADERS, null, 'A1');

        $headerStyle = $sheet->getStyle('A1:I1');
        $headerStyle->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $headerStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1D4ED8');
        $headerStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 2;
        foreach ($posts->values() as $index => $post) {
            $sheet->setCellValue("A{$row}", $index + 1);
            // Teks ditulis eksplisit sebagai string untuk mencegah formula injection.
            $sheet->setCellValueExplicit("B{$row}", $post->title, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("C{$row}", $post->category, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("D{$row}", (string) $post->excerpt, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("E{$row}", $post->content, DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("F{$row}", $post->is_published ? 'Published' : 'Draft', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("G{$row}", $post->published_at?->format('Y-m-d H:i') ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("H{$row}", $post->user?->name ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValueExplicit("I{$row}", $post->created_at?->format('Y-m-d H:i') ?? '-', DataType::TYPE_STRING);
            $row++;
        }

        $lastRow = max(2, $row - 1);
        $sheet->getStyle("A1:I{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (['A', 'B', 'C', 'F', 'G', 'H', 'I'] as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        $sheet->getColumnDimension('D')->setWidth(50);
        $sheet->getColumnDimension('E')->setWidth(80);

        return $spreadsheet;
    }

    /**
     * Unduh daftar artikel sebagai berkas .xlsx.
     *
     * @param  Collection<int, Post>  $posts
     */
    public function exportToResponse(Collection $posts, string $filename): StreamedResponse
    {
        $spreadsheet = $this->buildSpreadsheet($posts);

        return response()->streamDownload(function () use ($spreadsheet): void {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Impor artikel dari berkas Excel/CSV. Baris pertama dianggap header.
     * Kolom: B=Judul, C=Kategori, D=Ringkasan, E=Konten, F=Status.
     *
     * @return int Jumlah artikel yang berhasil diimpor.
     */
    public function import(string $filePath, int $userId): int
    {
        $rows = IOFactory::load($filePath)->getActiveSheet()->toArray(null, false, false, true);
        array_shift($rows);

        $imported = 0;
        foreach ($rows as $row) {
            $title = trim((string) ($row['B'] ?? ''));
            if ($title === '') {
                continue;
            }

            $this->postService->createPost($userId, [
                'title' => mb_substr($title, 0, 255),
                'category' => PostCategory::fromLoose((string) ($row['C'] ?? ''))->value,
                'excerpt' => trim((string) ($row['D'] ?? '')),
                'content' => trim((string) ($row['E'] ?? '')),
                'is_published' => $this->isPublishedStatus((string) ($row['F'] ?? '')),
            ]);
            $imported++;
        }

        return $imported;
    }

    /**
     * Tentukan apakah teks status berarti "dipublikasikan".
     */
    public function isPublishedStatus(string $status): bool
    {
        return in_array(mb_strtolower(trim($status)), self::PUBLISHED_KEYWORDS, true);
    }
}
