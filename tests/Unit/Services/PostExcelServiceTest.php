<?php

namespace Tests\Unit\Services;

use App\Features\Post\Models\Post;
use App\Features\Post\Repositories\PostRepository;
use App\Features\Post\Services\PostExcelService;
use App\Features\Post\Services\PostService;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class PostExcelServiceTest extends TestCase
{
    use RefreshDatabase;

    private PostExcelService $excel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->excel = new PostExcelService(new PostService(new PostRepository));
    }

    public function test_build_spreadsheet_writes_headers_rows_and_escapes_formulas(): void
    {
        $author = User::factory()->create(['name' => 'Penulis Uji']);
        Post::factory()->for($author)->published()->create(['title' => '=SUM(1,2)', 'category' => 'Opini']);
        Post::factory()->for($author)->draft()->create(['excerpt' => null]);

        $sheet = $this->excel->buildSpreadsheet(Post::with('user')->orderBy('id')->get(), 'Judul Sheet Yang Sangat Panjang Sekali Melebihi Batas')->getActiveSheet();

        $this->assertSame(PostExcelService::HEADERS, $sheet->rangeToArray('A1:I1')[0]);
        $this->assertSame('=SUM(1,2)', $sheet->getCell('B2')->getValue());
        $this->assertSame('Published', $sheet->getCell('F2')->getValue());
        $this->assertSame('Penulis Uji', $sheet->getCell('H2')->getValue());
        $this->assertSame('Draft', $sheet->getCell('F3')->getValue());
        $this->assertSame('-', $sheet->getCell('G3')->getValue());
        $this->assertSame(31, mb_strlen($sheet->getTitle()));
    }

    public function test_build_spreadsheet_handles_missing_author_and_dates(): void
    {
        $post = Post::factory()->make(['title' => 'Tanpa Relasi', 'published_at' => null]);
        $post->setRelation('user', null);

        $sheet = $this->excel->buildSpreadsheet(new Collection([$post]))->getActiveSheet();

        $this->assertSame('-', $sheet->getCell('H2')->getValue());
        $this->assertSame('-', $sheet->getCell('I2')->getValue());
    }

    public function test_export_to_response_streams_xlsx_file(): void
    {
        Post::factory()->count(2)->create();

        $response = $this->excel->exportToResponse(Post::with('user')->get(), 'artikel.xlsx');
        ob_start();
        $response->sendContent();
        $content = (string) ob_get_clean();

        $this->assertStringContainsString('attachment; filename=artikel.xlsx', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('PK', $content);
    }

    public function test_import_creates_posts_from_rows_and_skips_empty_titles(): void
    {
        $user = User::factory()->create();
        $spreadsheet = new Spreadsheet;
        $spreadsheet->getActiveSheet()->fromArray([
            PostExcelService::HEADERS,
            [1, 'Artikel Impor Satu', 'Pendidikan', 'Ringkasan satu', '## Isi satu', 'Published'],
            [2, '', 'Opini', 'Tanpa judul', 'Diabaikan', 'Draft'],
            [3, 'Artikel Impor Dua', 'Kategori Asing', '', '', 'draft'],
        ]);
        $path = sys_get_temp_dir().'/'.uniqid('imp', true).'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        $imported = $this->excel->import($path, $user->id);
        @unlink($path);

        $this->assertSame(2, $imported);
        $first = $user->posts()->where('title', 'Artikel Impor Satu')->first();
        $this->assertSame('Pendidikan', $first->category);
        $this->assertTrue($first->is_published);
        $second = $user->posts()->where('title', 'Artikel Impor Dua')->first();
        $this->assertSame('Umum', $second->category);
        $this->assertFalse($second->is_published);
    }

    public function test_export_then_import_round_trip(): void
    {
        $user = User::factory()->create();
        Post::factory()->for($user)->published()->create(['title' => 'Round Trip']);
        $path = sys_get_temp_dir().'/'.uniqid('rt', true).'.xlsx';
        (new Xlsx($this->excel->buildSpreadsheet($user->posts()->with('user')->get())))->save($path);

        $this->assertSame(1, $this->excel->import($path, $user->id));
        $this->assertSame('Round Trip', IOFactory::load($path)->getActiveSheet()->getCell('B2')->getValue());
        @unlink($path);
        $this->assertSame(2, $user->posts()->where('title', 'Round Trip')->count());
    }

    public function test_is_published_status(): void
    {
        $this->assertTrue($this->excel->isPublishedStatus(' Terbit '));
        $this->assertTrue($this->excel->isPublishedStatus('PUBLISHED'));
        $this->assertFalse($this->excel->isPublishedStatus('Draft'));
        $this->assertFalse($this->excel->isPublishedStatus(''));
    }
}
