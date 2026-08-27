<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Contact;
use App\Models\Project;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThtMediaPortfolioTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_capability_flow_connects_services_projects_clients_and_consultation(): void
    {
        $this->seed();
        $outputBufferLevel = ob_get_level();

        $service = Service::query()->orderBy('sort_order')->firstOrFail();
        $project = Project::query()->firstOrFail();
        $client = Client::query()->firstOrFail();
        $serviceCategory = $service->category()->firstOrFail();
        $projectCategory = $project->category()->firstOrFail();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Dịch vụ truyền thông - media toàn diện')
            ->assertSee('home-service-bento__card--featured', false)
            ->assertSee('home-service-bento__visual', false)
            ->assertSee('Gửi yêu cầu tư vấn');
        $this->assertSame($outputBufferLevel, ob_get_level(), 'Trang chủ để lại output buffer chưa đóng.');

        $this->get(route('services.index'))->assertOk()->assertSee($service->getTranslation('name', 'vi'));
        $this->assertSame($outputBufferLevel, ob_get_level(), 'Trang danh sách dịch vụ để lại output buffer chưa đóng.');
        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('services.show', $serviceCategory->getSlug('vi')), false)
            ->assertSee($serviceCategory->getTranslation('name', 'vi'));
        $this->get(route('services.show', $serviceCategory->getSlug('vi')))
            ->assertOk()
            ->assertSee($serviceCategory->getTranslation('name', 'vi'))
            ->assertSee($service->getTranslation('name', 'vi'));
        $this->get(route('services.show', $service->getSlug('vi')))
            ->assertOk()
            ->assertSee('Dịch vụ này giải quyết điều gì?')
            ->assertSee('Dự án liên quan');
        $this->assertSame($outputBufferLevel, ob_get_level(), 'Trang chi tiết dịch vụ để lại output buffer chưa đóng.');

        $this->get(route('projects.index'))->assertOk()->assertSee($project->getTranslation('name', 'vi'));
        $this->assertSame($outputBufferLevel, ob_get_level(), 'Trang danh sách dự án để lại output buffer chưa đóng.');
        $this->get(route('projects.show', $projectCategory->getSlug('vi')))
            ->assertOk()
            ->assertSee($projectCategory->getTranslation('name', 'vi'))
            ->assertSee($project->getTranslation('name', 'vi'));
        $this->get(route('projects.show', $project->getSlug('vi')))
            ->assertOk()
            ->assertSee('Bối cảnh và yêu cầu')
            ->assertSee('Giải pháp của THT Media')
            ->assertSee($client->getTranslation('name', 'vi'));
        $this->assertSame($outputBufferLevel, ob_get_level(), 'Trang chi tiết dự án để lại output buffer chưa đóng.');

        $this->get(route('clients.index'))->assertOk()->assertSee($client->getTranslation('name', 'vi'));
        $this->assertSame($outputBufferLevel, ob_get_level(), 'Trang khách hàng để lại output buffer chưa đóng.');
        $this->get(route('contact'))->assertOk()->assertSee('Cùng làm rõ nhu cầu trước khi bắt đầu');
        $this->assertSame($outputBufferLevel, ob_get_level(), 'Trang liên hệ để lại output buffer chưa đóng.');

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('services.show', $service->getSlug('vi')), false)
            ->assertSee(route('projects.show', $project->getSlug('vi')), false);
        $this->get(route('robots'))->assertOk()->assertSee('Sitemap: '.route('sitemap'));
    }

    public function test_consultation_form_creates_a_lead_with_project_context(): void
    {
        $this->seed();
        $service = Service::query()->firstOrFail();

        $this->from(route('contact'))->post(route('contact.submit'), [
            'service_id' => $service->id,
            'name' => 'Nguyễn Minh Anh',
            'phone' => '0901234567',
            'email' => 'minhanh@example.test',
            'company' => 'Công ty Minh Anh',
            'budget' => '30–80 triệu',
            'timeline' => 'Tháng 10/2026',
            'message' => 'Cần tư vấn kế hoạch sản xuất nội dung cho chiến dịch mới.',
            'website' => '',
        ])->assertRedirect(route('contact'))->assertSessionHas('success');

        $lead = Contact::query()->latest('id')->firstOrFail();
        $this->assertSame($service->id, $lead->service_id);
        $this->assertSame('Công ty Minh Anh', $lead->company);
        $this->assertSame('30–80 triệu', $lead->budget);
        $this->assertSame('new', $lead->status);
    }

    public function test_homepage_only_uses_active_service_categories_marked_for_homepage(): void
    {
        $this->seed();

        $homeCategory = ServiceCategory::query()->where('is_active', true)->where('is_home', true)->firstOrFail();
        $activeOnlyCategory = ServiceCategory::query()->create([
            'name' => ['vi' => 'Danh mục chỉ hiển thị trong trang dịch vụ'],
            'is_active' => true,
            'is_home' => false,
        ]);
        $inactiveHomeCategory = ServiceCategory::query()->create([
            'name' => ['vi' => 'Danh mục đã tắt trên toàn website'],
            'is_active' => false,
            'is_home' => true,
        ]);

        $response = $this->get(route('home'))->assertOk();
        $homeCategoryIds = $response->viewData('homeServiceCategories')->modelKeys();

        $this->assertContains($homeCategory->id, $homeCategoryIds);
        $this->assertNotContains($activeOnlyCategory->id, $homeCategoryIds);
        $this->assertNotContains($inactiveHomeCategory->id, $homeCategoryIds);
    }

}
