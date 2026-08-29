<?php

namespace BeegoodIT\LaravelPwa\Console;

use BeegoodIT\LaravelPwa\Models\Notifications\Message;
use BeegoodIT\LaravelPwa\Notifications\Jobs\SendMessageJob;
use BeegoodIT\LaravelPwa\States\Messages\Failed;
use BeegoodIT\LaravelPwa\States\Messages\OnHold;
use BeegoodIT\LaravelPwa\States\Messages\Pending;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearNotificationsCommand extends Command
{
    protected $signature = 'pwa:notifications:clear
                            {--pending : Delete pending messages}
                            {--on-hold : Delete on-hold messages}
                            {--failed : Delete failed messages}
                            {--all : pending + on-hold + failed}
                            {--force : Skip confirmation}';

    protected $description = 'Delete PWA notification messages by delivery status (does not clear the Laravel job queue)';

    public function handle(): int
    {
        $statuses = $this->selectedStatuses();

        if ($statuses === []) {
            $this->error('Specify at least one of --pending, --on-hold, --failed, or --all.');

            return self::FAILURE;
        }

        $messageCount = Message::query()
            ->whereIn('delivery_status', $statuses)
            ->count();

        $shouldClearSendJobs = in_array(Pending::$name, $statuses, true);
        $queueIsDatabase = $this->queueDriverIsDatabase();

        if ($shouldClearSendJobs && ! $queueIsDatabase) {
            $this->warn('Queue driver is not database; SendMessageJob rows were not inspected. Use queue:clear on that connection if needed.');
        }

        $sendJobIds = ($shouldClearSendJobs && $queueIsDatabase) ? $this->sendMessageJobIds() : [];

        if ($messageCount === 0 && $sendJobIds === []) {
            $this->info('Nothing to delete.');

            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            $summary = "Delete {$messageCount} message(s)";
            if ($shouldClearSendJobs && $queueIsDatabase) {
                $summary .= ' and '.count($sendJobIds).' SendMessageJob(s)';
            }
            $summary .= '?';

            if (! $this->confirm($summary)) {
                return self::SUCCESS;
            }
        }

        if ($sendJobIds !== []) {
            $this->deleteSendMessageJobs($sendJobIds);
        }

        Message::query()
            ->whereIn('delivery_status', $statuses)
            ->delete();

        $this->info("Deleted {$messageCount} message(s).");

        if ($shouldClearSendJobs && $queueIsDatabase) {
            $this->info('Deleted '.count($sendJobIds).' SendMessageJob(s).');
        }

        return self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    protected function selectedStatuses(): array
    {
        if ($this->option('all')) {
            return [Pending::$name, OnHold::$name, Failed::$name];
        }

        $statuses = [];

        if ($this->option('pending')) {
            $statuses[] = Pending::$name;
        }

        if ($this->option('on-hold')) {
            $statuses[] = OnHold::$name;
        }

        if ($this->option('failed')) {
            $statuses[] = Failed::$name;
        }

        return $statuses;
    }

    protected function queueDriverIsDatabase(): bool
    {
        $connection = config('queue.default');

        return config("queue.connections.{$connection}.driver") === 'database';
    }

    /**
     * @return list<int|string>
     */
    protected function sendMessageJobIds(): array
    {
        $table = config('queue.connections.database.table', 'jobs');
        $queue = config('pwa.notifications.queue', 'default');
        $ids = [];

        foreach (DB::table($table)->where('queue', $queue)->cursor() as $job) {
            $payload = json_decode((string) $job->payload, true);
            $displayName = is_array($payload) ? ($payload['displayName'] ?? '') : '';

            if ($displayName === SendMessageJob::class) {
                $ids[] = $job->id;
            }
        }

        return $ids;
    }

    /**
     * @param  list<int|string>  $ids
     */
    protected function deleteSendMessageJobs(array $ids): void
    {
        $table = config('queue.connections.database.table', 'jobs');

        DB::table($table)->whereIn('id', $ids)->delete();
    }
}
