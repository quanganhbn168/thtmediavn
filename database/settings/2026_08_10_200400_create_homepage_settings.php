<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('homepage.homepage_banner_type', 'slider');
        $this->migrator->add('homepage.homepage_sections', [
            'intro', 'services', 'projects', 'featured_case', 'reasons', 'process',
            'clients', 'capacity', 'testimonials', 'posts', 'consultation',
        ]);
        $this->migrator->add('homepage.homepage_section_titles', [
            'intro' => ['vi' => 'Một đội ngũ đồng hành từ ý tưởng đến sản phẩm truyền thông'],
            'services' => ['vi' => 'Dịch vụ của THT Media'],
            'projects' => ['vi' => 'Dự án đã thực hiện'],
            'featured_case' => ['vi' => 'Case study nổi bật'],
            'reasons' => ['vi' => 'Vì sao doanh nghiệp chọn THT Media'],
            'process' => ['vi' => 'Quy trình hợp tác'],
            'clients' => ['vi' => 'Khách hàng và đối tác'],
            'capacity' => ['vi' => 'Năng lực triển khai'],
            'testimonials' => ['vi' => 'Khách hàng nói về chúng tôi'],
            'posts' => ['vi' => 'Tin tức và góc nhìn'],
            'consultation' => ['vi' => 'Trao đổi về dự án của anh/chị'],
        ]);
        $this->migrator->add('homepage.homepage_intro_title', [
            'vi' => 'THT Media giúp doanh nghiệp biến mục tiêu truyền thông thành nội dung có thể triển khai.',
        ]);
        $this->migrator->add('homepage.homepage_intro_text', [
            'vi' => 'Chúng tôi tập trung vào ba nhóm năng lực: sản xuất hình ảnh, truyền thông và marketing, sự kiện và thương hiệu. Mỗi dự án bắt đầu từ việc làm rõ mục tiêu, đối tượng và đầu ra cần bàn giao.',
        ]);
        $this->migrator->add('homepage.homepage_reasons', [
            'vi' => "Giải pháp bám sát mục tiêu và bối cảnh thực tế\nMột đầu mối phối hợp xuyên suốt quá trình triển khai\nPhạm vi công việc và đầu ra được thống nhất rõ ràng\nCó dự án thực tế để đối chiếu năng lực",
        ]);
        $this->migrator->add('homepage.homepage_process', [
            'vi' => "Tiếp nhận brief và làm rõ mục tiêu\nĐề xuất giải pháp, phạm vi và kế hoạch\nTổ chức sản xuất và phối hợp triển khai\nNghiệm thu, bàn giao và đồng hành sau dự án",
        ]);
        $this->migrator->add('homepage.homepage_capacity', [
            'vi' => "Tư vấn định hướng nội dung và hình thức triển khai\nTổ chức sản xuất hình ảnh, video và nội dung truyền thông\nPhối hợp các hạng mục sự kiện và nhận diện thương hiệu\nQuản lý đầu việc, tiến độ và tài sản bàn giao",
        ]);
        $this->migrator->add('homepage.homepage_consultation_title', [
            'vi' => 'Anh/chị đang chuẩn bị một dự án truyền thông?',
        ]);
        $this->migrator->add('homepage.homepage_consultation_text', [
            'vi' => 'Hãy gửi mục tiêu, phạm vi và thời gian dự kiến. THT Media sẽ liên hệ để cùng làm rõ hướng triển khai phù hợp.',
        ]);
    }
};
