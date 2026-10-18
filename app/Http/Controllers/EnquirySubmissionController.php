<?php

namespace App\Http\Controllers;

use App\Http\Requests\EnquirySubmissionRequest;
use App\Jobs\SendEnquiryNotification;
use App\Models\Enquiry;
use App\Models\PracticeArea;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class EnquirySubmissionController extends Controller
{
    public function __invoke(EnquirySubmissionRequest $request)
    {
        $data = $request->validated();
        $practice = ! empty($data['practice_area_id'])
            ? PracticeArea::published()->findOrFail($data['practice_area_id'])
            : null;
        $enquiry = Enquiry::withTrashed()->firstOrCreate(
            ['submission_key' => Str::upper($data['submission_key'])],
            [
                'first_name' => trim($data['first_name']),
                'last_name' => trim($data['last_name']),
                'email' => Str::lower(trim($data['email'])),
                'phone' => isset($data['phone']) ? trim($data['phone']) : null,
                'practice_area_id' => $practice?->id,
                'area_label' => $practice?->name ?? trim($data['area_label'] ?? $data['area'] ?? 'Lainnya'),
                'message' => trim($data['message']),
                'source' => $data['source'],
                'status' => 'new',
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 2000, ''),
            ],
        );

        if ($enquiry->wasRecentlyCreated) {
            $recipients = config('enquiries.notify_to', []);
            $mailer = config('mail.default');
            if ($recipients !== [] && ! in_array($mailer, ['log', 'array'], true)) {
                try {
                    SendEnquiryNotification::dispatch($enquiry->id)
                        ->onQueue(config('enquiries.queue', 'emails'));
                } catch (Throwable $exception) {
                    report($exception);
                    Log::warning('Enquiry saved, but its notification could not be queued or sent.', [
                        'enquiry_id' => $enquiry->id,
                        'exception' => $exception::class,
                    ]);
                }
            } else {
                Log::notice('Enquiry saved without an active email notification configuration.', [
                    'enquiry_id' => $enquiry->id,
                    'recipient_configured' => $recipients !== [],
                    'mailer' => $mailer,
                ]);
            }
        }

        return redirect()->back()->with('enquiry_saved', true);
    }
}
