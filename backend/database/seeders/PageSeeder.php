<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $pages = [
            [
                'slug'           => 'home',
                'title_zh_cn'    => '首页',
                'title_en'       => 'Home',
                'title_th'       => 'หน้าแรก',
                'content_zh_cn'  => null,
                'content_en'     => null,
                'content_th'     => null,
                'meta_title_zh_cn' => '湖北豪睿国际人才服务有限公司',
                'meta_title_en'  => 'HorizonHR International Talent Service',
                'meta_title_th'  => 'บริการบุคลากรระหว่างประเทศ HorizonHR',
                'meta_desc_zh_cn' => '连接东南亚与中国的国际人才服务平台',
                'meta_desc_en'   => 'Connecting Southeast Asia and China — international talent service platform',
                'meta_desc_th'   => 'เชื่อมต่อเอเชียตะวันออกเฉียงใต้และจีน — แพลตฟอร์มบริการบุคลากรระหว่างประเทศ',
                'status'         => 'published',
                'type'           => 'page',
                'order_num'      => 0,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'slug'           => 'about',
                'title_zh_cn'    => '关于我们',
                'title_en'       => 'About Us',
                'title_th'       => 'เกี่ยวกับเรา',
                'content_zh_cn'  => null,
                'content_en'     => null,
                'content_th'     => null,
                'meta_title_zh_cn' => '关于湖北豪睿国际人才服务',
                'meta_title_en'  => 'About HorizonHR International Talent Service',
                'meta_title_th'  => 'เกี่ยวกับ HorizonHR',
                'meta_desc_zh_cn' => null,
                'meta_desc_en'   => null,
                'meta_desc_th'   => null,
                'status'         => 'published',
                'type'           => 'page',
                'order_num'      => 1,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'slug'           => 'study-in-china',
                'title_zh_cn'    => '留学中国',
                'title_en'       => 'Study in China',
                'title_th'       => 'เรียนในจีน',
                'content_zh_cn'  => null,
                'content_en'     => null,
                'content_th'     => null,
                'meta_title_zh_cn' => '留学中国 — 豪睿国际',
                'meta_title_en'  => 'Study in China — HorizonHR',
                'meta_title_th'  => 'เรียนในจีน — HorizonHR',
                'meta_desc_zh_cn' => null,
                'meta_desc_en'   => null,
                'meta_desc_th'   => null,
                'status'         => 'published',
                'type'           => 'page',
                'order_num'      => 2,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'slug'           => 'corporate',
                'title_zh_cn'    => '企业服务',
                'title_en'       => 'Corporate Services',
                'title_th'       => 'บริการองค์กร',
                'content_zh_cn'  => null,
                'content_en'     => null,
                'content_th'     => null,
                'meta_title_zh_cn' => '企业服务 — 豪睿国际',
                'meta_title_en'  => 'Corporate Services — HorizonHR',
                'meta_title_th'  => 'บริการองค์กร — HorizonHR',
                'meta_desc_zh_cn' => null,
                'meta_desc_en'   => null,
                'meta_desc_th'   => null,
                'status'         => 'published',
                'type'           => 'page',
                'order_num'      => 3,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
            [
                'slug'           => 'contact',
                'title_zh_cn'    => '联系我们',
                'title_en'       => 'Contact Us',
                'title_th'       => 'ติดต่อเรา',
                'content_zh_cn'  => null,
                'content_en'     => null,
                'content_th'     => null,
                'meta_title_zh_cn' => '联系我们 — 豪睿国际',
                'meta_title_en'  => 'Contact Us — HorizonHR',
                'meta_title_th'  => 'ติดต่อเรา — HorizonHR',
                'meta_desc_zh_cn' => null,
                'meta_desc_en'   => null,
                'meta_desc_th'   => null,
                'status'         => 'published',
                'type'           => 'page',
                'order_num'      => 4,
                'created_at'     => $now,
                'updated_at'     => $now,
            ],
        ];

        foreach ($pages as $page) {
            DB::table('pages')->updateOrInsert(
                ['slug' => $page['slug']],
                $page
            );
        }
    }
}
