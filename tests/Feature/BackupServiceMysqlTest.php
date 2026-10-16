<?php

namespace Tests\Feature;

use App\Models\Flower;
use App\Models\Order;
use App\Services\BackupService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

/**
 * Covers BackupService's MySQL/MariaDB path specifically — a pure-PHP
 * dump/restore (dumpMysqlToFile() / restoreMysqlDatabase()), not a call
 * out to the mysqldump/mysql CLI binaries, because those often aren't
 * installed on shared hosting (this is exactly the bug a real deployment
 * hit: "mysqldump: not found"). Needs a real MySQL-protocol server to
 * mean anything — see setUp() for how the connection is pointed at one
 * instead of the sqlite default the rest of the suite uses.
 */
class BackupServiceMysqlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! $this->mysqlAvailable()) {
            $this->markTestSkipped('No local MySQL/MariaDB server available for this test.');
        }

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => '127.0.0.1',
            'database.connections.mysql.port' => '3306',
            'database.connections.mysql.database' => 'aurelia_test',
            'database.connections.mysql.username' => 'aurelia',
            'database.connections.mysql.password' => 'aurelia',
        ]);

        DB::purge('mysql');
        $this->artisan('migrate:fresh', ['--force' => true]);

        Storage::fake('backups');
        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        if ($this->mysqlAvailable()) {
            DB::purge('mysql');
        }

        parent::tearDown();
    }

    protected function mysqlAvailable(): bool
    {
        try {
            new \PDO('mysql:host=127.0.0.1;port=3306', 'aurelia', 'aurelia');

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function service(): BackupService
    {
        return app(BackupService::class);
    }

    public function test_create_builds_a_zip_with_a_plain_sql_dump_and_public_files(): void
    {
        Flower::create(['name' => 'Rose', 'price_per_stem' => 35, 'stock_quantity' => 10, 'is_active' => true]);
        Storage::disk('public')->put('flowers/sample.png', 'fake-image-bytes');

        $backup = $this->service()->create('manual');

        Storage::disk('backups')->assertExists($backup->filename);

        $zip = new ZipArchive;
        $zip->open(Storage::disk('backups')->path($backup->filename));
        $this->assertNotFalse($zip->locateName('database.sql'));
        $this->assertNotFalse($zip->locateName('public/flowers/sample.png'));

        $manifest = json_decode($zip->getFromName('manifest.json'), true);
        $this->assertSame('mysql', $manifest['driver']);

        $sql = $zip->getFromName('database.sql');
        $this->assertStringContainsString('DROP TABLE IF EXISTS `flowers`', $sql);
        $this->assertStringContainsString('INSERT INTO `flowers`', $sql);
        $this->assertStringContainsString('Rose', $sql);
        $zip->close();
    }

    /**
     * The exact risk that makes naive "split SQL on every semicolon"
     * restoration wrong: a real text field containing a semicolon
     * immediately followed by a newline, which is indistinguishable from
     * a statement terminator unless the splitter tracks quote state.
     */
    public function test_restore_round_trips_data_containing_semicolons_quotes_and_newlines(): void
    {
        $tricky = "Ring the bell;\nSay \"hello\" and don't forget — 'quoted' text.";

        $order = Order::create([
            'order_number' => Order::generateOrderNumber(),
            'name' => 'Jane Doe',
            'phone' => '960 777-1234',
            'delivery_address' => '123 Test Street',
            'delivery_date' => now()->addDay()->toDateString(),
            'delivery_time_slot' => '09:00 - 11:00',
            'special_notes' => $tricky,
            'payment_method' => 'cod',
            'status' => 'pending',
            'subtotal' => 100,
            'delivery_fee' => 0,
            'total' => 100,
            'currency' => 'MVR',
        ]);

        $backup = $this->service()->create('manual');

        $order->update(['special_notes' => 'overwritten']);

        $this->service()->restore($backup->filename);

        $this->assertSame($tricky, Order::find($order->id)->special_notes);
    }

    public function test_restore_reverts_the_database_and_public_files_to_the_backups_state(): void
    {
        Storage::disk('public')->put('flowers/keep.png', 'original-bytes');
        $rose = Flower::create(['name' => 'Rose', 'price_per_stem' => 35, 'stock_quantity' => 10, 'is_active' => true]);

        $backup = $this->service()->create('manual');

        $rose->update(['name' => 'Renamed Rose']);
        Flower::create(['name' => 'Added After Backup', 'price_per_stem' => 10, 'stock_quantity' => 5, 'is_active' => true]);
        Storage::disk('public')->put('flowers/keep.png', 'changed-bytes');
        Storage::disk('public')->put('flowers/new-file.png', 'new-bytes');

        $this->service()->restore($backup->filename);

        $this->assertSame('Rose', Flower::find($rose->id)->name);
        $this->assertDatabaseMissing('flowers', ['name' => 'Added After Backup']);
        $this->assertSame('original-bytes', Storage::disk('public')->get('flowers/keep.png'));
        Storage::disk('public')->assertMissing('flowers/new-file.png');
    }

    public function test_restore_from_an_uploaded_file_works_the_same_way(): void
    {
        $rose = Flower::create(['name' => 'Rose', 'price_per_stem' => 35, 'stock_quantity' => 10, 'is_active' => true]);
        $backup = $this->service()->create('manual');
        $zipPath = Storage::disk('backups')->path($backup->filename);

        $rose->delete();
        $this->assertDatabaseMissing('flowers', ['id' => $rose->id]);

        $upload = new UploadedFile($zipPath, $backup->filename, 'application/zip', null, true);
        $this->service()->restoreFromUpload($upload);

        $this->assertDatabaseHas('flowers', ['id' => $rose->id, 'name' => 'Rose']);
    }

    public function test_restore_handles_more_rows_than_one_chunk(): void
    {
        // dumpMysqlToFile() batches 500 rows per INSERT — prove a table
        // spanning more than one chunk round-trips correctly too.
        for ($i = 0; $i < 650; $i++) {
            Flower::create(['name' => "Flower {$i}", 'price_per_stem' => 10, 'stock_quantity' => 5, 'is_active' => true]);
        }

        $backup = $this->service()->create('manual');
        Flower::query()->delete();

        $this->service()->restore($backup->filename);

        $this->assertSame(650, Flower::count());
        $this->assertDatabaseHas('flowers', ['name' => 'Flower 649']);
    }
}
