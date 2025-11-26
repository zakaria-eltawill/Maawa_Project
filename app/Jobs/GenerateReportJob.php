<?php

namespace App\Jobs;

use App\Models\Export;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

abstract class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Export $export
    ) {
        $this->onQueue('reports');
    }

    public function handle(): void
    {
        try {
            $this->export->update(['status' => 'QUEUED']);

            $filePath = $this->generateFile();

            $this->export->update([
                'status' => 'READY',
                'file_path' => $filePath,
                'completed_at' => now(),
            ]);

            Log::info('Report generated successfully', [
                'export_id' => $this->export->id,
                'type' => $this->export->type,
                'format' => $this->getFormat(),
                'file_path' => $filePath,
            ]);
        } catch (\Exception $e) {
            $this->export->update([
                'status' => 'FAILED',
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Report generation failed', [
                'export_id' => $this->export->id,
                'type' => $this->export->type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    abstract protected function generateFile(): string;

    protected function getFormat(): string
    {
        return $this->export->filters['format'] ?? 'csv';
    }

    protected function getStoragePath(): string
    {
        $format = $this->getFormat();
        $type = $this->export->type;
        $timestamp = now()->format('Y-m-d_His');
        $filename = "{$type}_report_{$timestamp}.{$format}";

        return "exports/{$filename}";
    }
}

