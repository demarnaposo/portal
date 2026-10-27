<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Modules\Admin\Domain\AdminRole;
use Modules\Assessment\Domain\AssessmentAttempt;
use Tests\Feature\Identity\CreatesCustomers;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use CreatesAdmins, CreatesCustomers, RefreshDatabase;

    private function attempt(?string $customerId): void
    {
        $attempt = AssessmentAttempt::create([
            'token_hash' => hash('sha256', uniqid()), 'version' => 1, 'answers' => ['q1' => 'a'],
            'scores' => [['category' => ['slug' => 'sec', 'title' => 'Keamanan'], 'score' => 2]], 'recommended_category' => 'sec',
        ]);
        $attempt->forceFill(['customer_id' => $customerId])->save();
    }

    public function test_dashboard_shows_counts(): void
    {
        $customer = $this->customer();
        $this->customer();
        $this->attempt($customer->id);
        $this->attempt(null);

        $this->actingAs($this->admin(), 'admin')->get('/admin')
            ->assertInertia(fn (AssertableInertia $page) => $page->component('admin/Dashboard')
                ->where('stats', ['customers' => 2, 'attempts' => 2, 'claimed_attempts' => 1])
                ->has('latestCustomers', 2));
    }

    public function test_customer_list_and_detail_mask_npwp(): void
    {
        $customer = $this->customer(['name' => 'Rina Wati', 'company_name' => 'PT Maju', 'npwp' => '0123456789012345']);
        $this->customer(['name' => 'Budi']);
        $this->attempt($customer->id);
        $admin = $this->admin(AdminRole::Operator);

        $list = $this->actingAs($admin, 'admin')->get('/admin/customers?search=maju')->assertOk();
        $list->assertInertia(fn (AssertableInertia $page) => $page->component('admin/customers/Index')
            ->has('customers.data', 1)
            ->where('customers.data.0.name', 'Rina Wati')
            ->where('customers.data.0.masked_npwp', '************2345')
            ->where('filters.search', 'maju'));
        $this->assertStringNotContainsString('0123456789012345', $list->getContent());

        $detail = $this->actingAs($admin, 'admin')->get('/admin/customers/'.$customer->id)->assertOk();
        $detail->assertInertia(fn (AssertableInertia $page) => $page->component('admin/customers/Show')
            ->where('customer.masked_npwp', '************2345')
            ->has('assessments', 1)
            ->where('assessments.0.recommended_category.title', 'Keamanan'));
        $this->assertStringNotContainsString('0123456789012345', $detail->getContent());

        $this->actingAs($admin, 'admin')->get('/admin/customers/tidak-ada')->assertNotFound();
    }

    public function test_assessment_results_list_claimed_attempts_with_customer_name(): void
    {
        $customer = $this->customer(['name' => 'Rina Wati']);
        $this->attempt($customer->id);
        $this->attempt(null);

        $this->actingAs($this->admin(AdminRole::Operator), 'admin')->get('/admin/assessments')
            ->assertInertia(fn (AssertableInertia $page) => $page->component('admin/assessments/Index')
                ->has('results.data', 1)
                ->where('results.data.0.customer_name', 'Rina Wati')
                ->where('anonymousCount', 1));
    }

    public function test_only_owner_can_view_admin_list(): void
    {
        $this->actingAs($this->admin(AdminRole::Operator), 'admin')->get('/admin/admins')->assertForbidden();
        $this->actingAs($this->admin(AdminRole::Owner), 'admin')->get('/admin/admins')
            ->assertInertia(fn (AssertableInertia $page) => $page->component('admin/admins/Index')->has('admins', 2));
    }

    public function test_profile_and_password_update(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')->patch('/admin/settings/profile', ['name' => 'Nama Baru', 'email' => 'Baru@Sertifika.test'])
            ->assertRedirect('/admin/settings/profile');
        $this->assertSame('baru@sertifika.test', $admin->fresh()->email);

        $this->actingAs($admin, 'admin')->put('/admin/settings/password', [
            'current_password' => 'salah', 'password' => 'sandi-baru-yang-panjang', 'password_confirmation' => 'sandi-baru-yang-panjang',
        ])->assertSessionHasErrors('current_password');

        $this->actingAs($admin, 'admin')->put('/admin/settings/password', [
            'current_password' => 'sandi-admin-rahasia', 'password' => 'sandi-baru-yang-panjang', 'password_confirmation' => 'sandi-baru-yang-panjang',
        ])->assertSessionHasNoErrors();
    }
}
