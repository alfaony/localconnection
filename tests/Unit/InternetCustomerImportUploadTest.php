<?php

namespace Tests\Unit;

use App\Http\Livewire\InternetCustomer\Admin\InternetCustomerIndex;
use Illuminate\Support\Facades\Storage;
use Livewire\TemporaryUploadedFile;
use Tests\TestCase;

class InternetCustomerImportUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        config()->set('livewire.temporary_file_upload.disk', 'local');
    }

    /** @test */
    public function installation_import_rejects_a_temporary_upload_that_points_to_a_directory(): void
    {
        Storage::disk('local')->makeDirectory('livewire-tmp/livewire-tmp');

        $component = new InternetCustomerIndex();
        $component->csvFile = TemporaryUploadedFile::createFromLivewire('livewire-tmp');

        $component->updatedCsvFile();

        $this->assertNull($component->csvFile);
        $this->assertFalse($component->isFileReady);
        $this->assertTrue($component->getErrorBag()->has('csvFile'));
    }

    /** @test */
    public function installation_import_accepts_a_readable_temporary_csv_file(): void
    {
        $filename = 'temporary-meta' . base64_encode('customers.csv') . '-.csv';
        Storage::disk('local')->put('livewire-tmp/' . $filename, "email,code\nuser@example.com,IC-001\n");

        $component = new InternetCustomerIndex();
        $component->csvFile = TemporaryUploadedFile::createFromLivewire($filename);

        $component->updatedCsvFile();

        $this->assertInstanceOf(TemporaryUploadedFile::class, $component->csvFile);
        $this->assertTrue($component->isFileReady);
        $this->assertFalse($component->getErrorBag()->has('csvFile'));
    }
}
