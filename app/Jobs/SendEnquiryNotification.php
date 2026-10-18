<?php

namespace App\Jobs;

use App\Mail\EnquiryReceived;
use App\Models\Enquiry;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendEnquiryNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300];

    public function __construct(public string $enquiryId) {}

    public function handle(): void
    {
        $recipients = config('enquiries.notify_to', []);
        if ($recipients === [] || in_array(config('mail.default'), ['log', 'array'], true)) {
            return;
        }

        $enquiry = Enquiry::find($this->enquiryId);
        if (! $enquiry || $enquiry->notification_sent_at !== null) {
            return;
        }

        Mail::to($recipients)->send(new EnquiryReceived($enquiry));
        $enquiry->forceFill(['notification_sent_at' => now()])->save();
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Enquiry email notification failed after queue retries.', [
            'enquiry_id' => $this->enquiryId,
            'exception' => $exception::class,
        ]);
    }
}
