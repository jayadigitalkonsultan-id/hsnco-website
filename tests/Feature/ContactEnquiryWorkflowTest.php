<?php

namespace Tests\Feature;

use App\Jobs\SendEnquiryNotification;
use App\Models\Enquiry;
use App\Models\User;
use App\Models\UserTwoFactorAuthentication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class ContactEnquiryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_and_contact_submissions_reach_admin_inbox_with_status_notes_and_notifications(): void
    {
        Queue::fake();
        config([
            'mail.default' => 'smtp',
            'enquiries.notify_to' => ['intake@example.test'],
            'enquiries.queue' => 'emails',
        ]);

        foreach (['home', 'contact'] as $source) {
            $response = $this->from($source === 'home' ? '/' : '/contact')->post(route('enquiries.store'), [
                'submission_key' => (string) Str::ulid(),
                'first_name' => 'Ayu',
                'last_name' => 'Pratama',
                'email' => 'ayu@example.test',
                'phone' => '0211234567',
                'practice_area_id' => '',
                'area_label' => 'Lainnya',
                'message' => '<script>alert(1)</script> Permintaan konsultasi awal.',
                'source' => $source,
            ]);

            $response->assertRedirect($source === 'home' ? '/' : '/contact')
                ->assertSessionHas('enquiry_saved', true)
                ->assertSessionHasNoErrors();
        }

        $this->assertDatabaseCount('enquiries', 2);
        $this->assertDatabaseHas('enquiries', ['source' => 'home', 'status' => 'new']);
        $this->assertDatabaseHas('enquiries', ['source' => 'contact', 'status' => 'new']);
        Queue::assertPushedOn('emails', SendEnquiryNotification::class, 2);

        $user = User::factory()->active('admin')->create();
        UserTwoFactorAuthentication::factory()->create([
            'user_id' => $user->id,
            'method' => 'email',
            'secret' => null,
            'confirmed_at' => now(),
        ]);
        $this->actingAs($user)->withSession(['auth.two_factor' => [
            'user_id' => $user->id,
            'auth_version' => $user->auth_version,
            'verified_at' => time(),
        ]]);

        $enquiry = Enquiry::query()->where('source', 'contact')->sole();
        $this->get(route('admin.enquiries.show', $enquiry))
            ->assertOk()
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertDontSee('<script>alert(1)</script>', false);
        $this->assertSame('read', $enquiry->fresh()->status);

        $this->put(route('admin.enquiries.update', $enquiry), [
            'status' => 'in_progress',
            'assigned_to' => $user->id,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->post(route('admin.enquiries.notes.store', $enquiry), ['body' => 'Follow up untuk jadwal konsultasi.'])
            ->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame('in_progress', $enquiry->fresh()->status);
        $this->assertSame('Follow up untuk jadwal konsultasi.', $enquiry->notes()->sole()->body);
        $this->assertSame($user->id, $enquiry->notes()->sole()->user_id);
    }

    public function test_contact_form_rejects_whitespace_only_required_fields_and_keeps_safe_old_input(): void
    {
        $response = $this->from('/contact')->post(route('enquiries.store'), [
            'submission_key' => (string) Str::ulid(),
            'first_name' => '  ',
            'last_name' => 'Test',
            'email' => 'valid@example.test',
            'area_label' => 'Lainnya',
            'message' => '  ',
            'source' => 'contact',
        ]);

        $response->assertRedirect('/contact')
            ->assertSessionHasErrors(['first_name', 'message'])
            ->assertSessionHasInput('last_name', 'Test');
        $this->assertDatabaseCount('enquiries', 0);
    }
}
