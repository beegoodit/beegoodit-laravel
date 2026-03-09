<?php

namespace BeegoodIT\FilamentSocialGraph\Support;

class FeedAttachmentUploadMode
{
    public static function useSinglePerRequest(): bool
    {
        $mode = config('filament-social-graph.attachments.multiple_upload_mode', 'auto');

        if ($mode === 'native') {
            return false;
        }

        if ($mode === 'single_per_request') {
            return true;
        }

        $disk = config('livewire.temporary_file_upload.disk') ?: config('filesystems.default');
        $driver = config('filesystems.disks.'.strtolower((string) $disk).'.driver');

        return $driver === 's3';
    }
}
