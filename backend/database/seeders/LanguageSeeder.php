<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $keys = [
            ['key' => 'website_name',       'zh_cn' => '湖北豪睿国际人才服务有限公司',        'en' => 'HorizonHR International Talent Service',                   'th' => 'บริการบุคลากรระหว่างประเทศ HorizonHR'],
            ['key' => 'home_banner_title',  'zh_cn' => '连接东南亚青年与中国高校',             'en' => 'Connect Southeast Asian Youth with Chinese Universities',   'th' => 'เชื่อมต่อเยาวชนเอเชียตะวันออกเฉียงใต้กับมหาวิทยาลัยจีน'],
            ['key' => 'study_in_china',     'zh_cn' => '留学中国',                           'en' => 'Study in China',                                            'th' => 'เรียนในประเทศจีน'],
            ['key' => 'talent_pool',        'zh_cn' => '人才库',                             'en' => 'Talent Pool',                                               'th' => 'กลุ่มบุคลากร'],
            ['key' => 'corporate',          'zh_cn' => '企业合作',                           'en' => 'Corporate Cooperation',                                     'th' => 'ความร่วมมือองค์กร'],
            ['key' => 'seminars',           'zh_cn' => '研讨会中心',                         'en' => 'Seminar Center',                                            'th' => 'ศูนย์สัมมนา'],
            ['key' => 'news',               'zh_cn' => '新闻资讯',                           'en' => 'News & Insights',                                           'th' => 'ข่าวสารและข้อมูลเชิงลึก'],
            ['key' => 'contact',            'zh_cn' => '联系我们',                           'en' => 'Contact Us',                                                'th' => 'ติดต่อเรา'],
            ['key' => 'about',              'zh_cn' => '关于我们',                           'en' => 'About Us',                                                  'th' => 'เกี่ยวกับเรา'],
        ];

        foreach ($keys as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        DB::table('languages')->insert($keys);
    }
}
