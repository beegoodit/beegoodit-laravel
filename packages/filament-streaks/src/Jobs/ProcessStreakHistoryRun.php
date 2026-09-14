<?php

namespace BeegoodIT\FilamentStreaks\Jobs;

use BeegoodIT\FilamentStreaks\Actions\ReplayStreakHistory;
use BeegoodIT\FilamentStreaks\Enums\OperationRunStatus;
use BeegoodIT\FilamentStreaks\Filament\Resources\HistoryResource;
use BeegoodIT\FilamentStreaks\Models\OperationRun;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Throwable;

class ProcessStreakHistoryRun implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public OperationRun $run) {}

    public function handle(ReplayStreakHistory $replayStreakHistory): void
    {
        $this->run->refresh();

        $this->run->update([
            'status' => OperationRunStatus::Running,
            'started_at' => now(),
            'error' => null,
        ]);

        try {
            $from = Carbon::parse($this->run->params['from'] ?? null)->startOfDay();
            $to = Carbon::parse($this->run->params['to'] ?? null)->endOfDay();

            $result = $replayStreakHistory->handle($from, $to);

            $this->run->update([
                'status' => OperationRunStatus::Succeeded,
                'result' => $result,
                'finished_at' => now(),
                'error' => null,
            ]);

            $this->notifyRequester(succeeded: true);
        } catch (Throwable $exception) {
            $this->run->update([
                'status' => OperationRunStatus::Failed,
                'error' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            $this->notifyRequester(succeeded: false);
        }
    }

    private function notifyRequester(bool $succeeded): void
    {
        $recipient = $this->run->requestedBy;

        if ($recipient === null) {
            return;
        }

        $notification = Notification::make()
            ->title($succeeded
                ? __('filament-streaks::history.notifications.succeeded_title')
                : __('filament-streaks::history.notifications.failed_title'))
            ->body($succeeded
                ? __('filament-streaks::history.notifications.succeeded_body', [
                    'from' => $this->run->params['from'] ?? '',
                    'to' => $this->run->params['to'] ?? '',
                ])
                : __('filament-streaks::history.notifications.failed_body', [
                    'error' => $this->run->error ?? '',
                ]));

        if ($succeeded) {
            $notification->success();
        } else {
            $notification->danger();
        }

        $url = $this->viewUrl();

        if (filled($url)) {
            $notification->actions([
                Action::make('view')
                    ->label(__('filament-streaks::history.notifications.view_action'))
                    ->button()
                    ->url($url)
                    ->markAsRead(),
            ]);
        }

        $notification->sendToDatabase($recipient);
    }

    private function viewUrl(): ?string
    {
        try {
            return HistoryResource::getUrl('view', ['record' => $this->run]);
        } catch (Throwable) {
            return null;
        }
    }
}
