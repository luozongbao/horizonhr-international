<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * DummyDataSeeder
 *
 * Seeds realistic dummy data for manual QA / TASK-049 testing.
 * Safe to run multiple times — uses updateOrInsert / firstOrCreate patterns.
 *
 * Data created:
 *  - 6  Partner Universities
 *  - 4  Seminars  (3 scheduled, 1 ended)
 *  - 1  Enterprise user + company
 *  - 6  Jobs (published, linked to the dummy enterprise)
 *  - 5  News/Posts  (3 categories)
 *  - 4  Students (user + student + talent_card)
 */
class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ─────────────────────────────────────────────────────────────────
        // 1. UNIVERSITIES
        // ─────────────────────────────────────────────────────────────────
        $universities = [
            [
                'name_zh_cn'      => '武汉大学',
                'name_en'         => 'Wuhan University',
                'name_th'         => 'มหาวิทยาลัยอู่ฮั่น',
                'location'        => 'Wuhan, Hubei',
                'location_city'   => 'Wuhan',
                'location_region' => 'Hubei',
                'website'         => 'https://www.whu.edu.cn',
                'description'     => 'One of the top comprehensive universities in China, located in Wuhan, Hubei.',
                'majors'          => json_encode(['Business Administration', 'Computer Science', 'International Trade', 'Law', 'Medicine']),
                'program_types'   => json_encode(['bachelor', 'master', 'language']),
                'established_year'=> 1893,
                'ranking'         => 6,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'name_zh_cn'      => '华中科技大学',
                'name_en'         => 'Huazhong University of Science and Technology',
                'name_th'         => 'มหาวิทยาลัยวิทยาศาสตร์และเทคโนโลยีหัวจง',
                'location'        => 'Wuhan, Hubei',
                'location_city'   => 'Wuhan',
                'location_region' => 'Hubei',
                'website'         => 'https://www.hust.edu.cn',
                'description'     => 'A leading engineering and science university in central China.',
                'majors'          => json_encode(['Mechanical Engineering', 'Electrical Engineering', 'Computer Science', 'Finance']),
                'program_types'   => json_encode(['bachelor', 'master']),
                'established_year'=> 1952,
                'ranking'         => 9,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'name_zh_cn'      => '中南财经政法大学',
                'name_en'         => 'Zhongnan University of Economics and Law',
                'name_th'         => 'มหาวิทยาลัยเศรษฐศาสตร์และกฎหมายจงหนาน',
                'location'        => 'Wuhan, Hubei',
                'location_city'   => 'Wuhan',
                'location_region' => 'Hubei',
                'website'         => 'https://www.zuel.edu.cn',
                'description'     => 'A prominent university specializing in economics, finance, and law.',
                'majors'          => json_encode(['Economics', 'Finance', 'Accounting', 'Law', 'International Business']),
                'program_types'   => json_encode(['bachelor', 'master', 'language']),
                'established_year'=> 1948,
                'ranking'         => 38,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'name_zh_cn'      => '武汉理工大学',
                'name_en'         => 'Wuhan University of Technology',
                'name_th'         => 'มหาวิทยาลัยเทคโนโลยีอู่ฮั่น',
                'location'        => 'Wuhan, Hubei',
                'location_city'   => 'Wuhan',
                'location_region' => 'Hubei',
                'website'         => 'https://www.whut.edu.cn',
                'description'     => 'A key national university in material science and transportation.',
                'majors'          => json_encode(['Material Science', 'Logistics', 'Civil Engineering', 'Architecture']),
                'program_types'   => json_encode(['bachelor', 'master', 'vocational']),
                'established_year'=> 1898,
                'ranking'         => 55,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'name_zh_cn'      => '武汉职业技术学院',
                'name_en'         => 'Wuhan Vocational College of Software and Engineering',
                'name_th'         => 'วิทยาลัยอาชีวศึกษาซอฟต์แวร์และวิศวกรรมอู่ฮั่น',
                'location'        => 'Wuhan, Hubei',
                'location_city'   => 'Wuhan',
                'location_region' => 'Hubei',
                'website'         => 'https://www.whvcse.edu.cn',
                'description'     => 'A well-known vocational college focusing on software and engineering programs.',
                'majors'          => json_encode(['Software Development', 'E-Commerce', 'Culinary Arts', 'Tourism Management']),
                'program_types'   => json_encode(['vocational']),
                'established_year'=> 1999,
                'ranking'         => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'name_zh_cn'      => '湖北大学',
                'name_en'         => 'Hubei University',
                'name_th'         => 'มหาวิทยาลัยหูเป่ย',
                'location'        => 'Wuhan, Hubei',
                'location_city'   => 'Wuhan',
                'location_region' => 'Hubei',
                'website'         => 'https://www.hubu.edu.cn',
                'description'     => 'A comprehensive provincial university with strong liberal arts and science programs.',
                'majors'          => json_encode(['Chinese Language', 'History', 'Chemistry', 'Mathematics', 'Education']),
                'program_types'   => json_encode(['bachelor', 'master', 'language']),
                'established_year'=> 1931,
                'ranking'         => 128,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
        ];

        foreach ($universities as $uni) {
            DB::table('universities')->updateOrInsert(
                ['name_en' => $uni['name_en']],
                $uni
            );
        }

        // ─────────────────────────────────────────────────────────────────
        // 2. SEMINARS
        // ─────────────────────────────────────────────────────────────────
        $seminars = [
            [
                'title_zh_cn'     => '2026年泰国留学生赴华求职指南',
                'title_en'        => '2026 Job Hunting Guide for Thai Students in China',
                'title_th'        => 'คู่มือหางานในจีนสำหรับนักศึกษาไทย ปี 2026',
                'desc_zh_cn'      => '本次研讨会将分享泰国留学生在华求职的实用技巧，包括简历撰写、面试准备和职场文化。',
                'desc_en'         => 'This seminar shares practical job-hunting tips for Thai students in China, covering resume writing, interview preparation, and workplace culture.',
                'desc_th'         => 'สัมมนานี้แบ่งปันเคล็ดลับการหางานในจีนสำหรับนักศึกษาไทย รวมถึงการเขียน CV การเตรียมตัวสัมภาษณ์ และวัฒนธรรมการทำงาน',
                'speaker_name'    => 'Dr. Somchai Prasertsri',
                'speaker_title'   => 'Career Advisor, Wuhan University',
                'speaker_bio'     => '10+ years of experience helping Southeast Asian students navigate the Chinese job market.',
                'target_audience' => 'students',
                'status'          => 'scheduled',
                'permission'      => 'registered',
                'max_viewers'     => 500,
                'starts_at'       => now()->addDays(7)->setTime(14, 0),
                'duration_min'    => 90,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'title_zh_cn'     => '湖北重点企业校园招聘说明会',
                'title_en'        => 'Hubei Key Enterprises Campus Recruitment Fair',
                'title_th'        => 'งานรับสมัครงานจากบริษัทชั้นนำในหูเป่ย',
                'desc_zh_cn'      => '10家湖北重点企业将在本次直播中介绍其校园招聘计划，包括职位详情、薪资待遇和发展前景。',
                'desc_en'         => '10 key Hubei enterprises will present their campus recruitment plans in this live session, including job details, compensation packages, and career paths.',
                'desc_th'         => 'บริษัทชั้นนำ 10 แห่งในหูเป่ยจะนำเสนอแผนรับสมัครงานในสัมมนานี้ รวมถึงรายละเอียดตำแหน่ง ค่าตอบแทน และเส้นทางอาชีพ',
                'speaker_name'    => 'Wang Jianming',
                'speaker_title'   => 'HR Director, HorizonHR International',
                'speaker_bio'     => 'HR professional with 15 years of cross-border recruitment experience.',
                'target_audience' => 'both',
                'status'          => 'scheduled',
                'permission'      => 'public',
                'max_viewers'     => 1000,
                'starts_at'       => now()->addDays(14)->setTime(10, 0),
                'duration_min'    => 120,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'title_zh_cn'     => '中国企业用人标准与跨文化沟通',
                'title_en'        => 'Chinese Workplace Standards & Cross-Cultural Communication',
                'title_th'        => 'มาตรฐานการทำงานในจีนและการสื่อสารข้ามวัฒนธรรม',
                'desc_zh_cn'      => '了解中国企业的工作文化、沟通方式及职业发展路径，为来华工作做好准备。',
                'desc_en'         => 'Understand Chinese corporate culture, communication styles, and career development paths to prepare for working in China.',
                'desc_th'         => 'ทำความเข้าใจวัฒนธรรมองค์กรจีน รูปแบบการสื่อสาร และเส้นทางการพัฒนาอาชีพ เพื่อเตรียมพร้อมสำหรับการทำงานในจีน',
                'speaker_name'    => 'Prof. Li Xiaoming',
                'speaker_title'   => 'Professor of Intercultural Communication, HUST',
                'speaker_bio'     => 'Published author on cross-cultural management and Southeast Asian studies.',
                'target_audience' => 'students',
                'status'          => 'scheduled',
                'permission'      => 'registered',
                'max_viewers'     => 300,
                'starts_at'       => now()->addDays(21)->setTime(15, 0),
                'duration_min'    => 60,
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
            [
                'title_zh_cn'     => '泰国高校与湖北企业合作对接会（回放）',
                'title_en'        => 'Thai Universities & Hubei Enterprises Partnership Meeting (Playback)',
                'title_th'        => 'การประชุมจับคู่มหาวิทยาลัยไทยและบริษัทหูเป่ย (รับชมย้อนหลัง)',
                'desc_zh_cn'      => '本次对接会促进了多所泰国高校与湖北企业之间的合作交流。',
                'desc_en'         => 'This partnership meeting facilitated collaboration between several Thai universities and Hubei enterprises.',
                'desc_th'         => 'การประชุมนี้ส่งเสริมความร่วมมือระหว่างมหาวิทยาลัยไทยหลายแห่งกับบริษัทในหูเป่ย',
                'speaker_name'    => 'Chen Wei',
                'speaker_title'   => 'CEO, HorizonHR International',
                'speaker_bio'     => 'Founder of HorizonHR with 20 years of international talent exchange experience.',
                'target_audience' => 'both',
                'status'          => 'ended',
                'permission'      => 'public',
                'max_viewers'     => 800,
                'starts_at'       => now()->subDays(10)->setTime(9, 0),
                'duration_min'    => 180,
                'ended_at'        => now()->subDays(10)->setTime(12, 0),
                'created_at'      => $now,
                'updated_at'      => $now,
            ],
        ];

        foreach ($seminars as $seminar) {
            DB::table('seminars')->updateOrInsert(
                ['title_en' => $seminar['title_en']],
                $seminar
            );
        }

        // ─────────────────────────────────────────────────────────────────
        // 3. ENTERPRISE USER + COMPANY + JOBS
        // ─────────────────────────────────────────────────────────────────
        $enterpriseUser = DB::table('users')->where('email', 'demo.enterprise@horizonhr.test')->first();
        if (! $enterpriseUser) {
            $enterpriseUserId = DB::table('users')->insertGetId([
                'role'           => 'enterprise',
                'email'          => 'demo.enterprise@horizonhr.test',
                'password'       => Hash::make('Demo@1234'),
                'status'         => 'active',
                'enterprise_status' => 'enterprise_verified',
                'prefer_lang'    => 'en',
                'email_verified' => true,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);
        } else {
            $enterpriseUserId = $enterpriseUser->id;
        }

        $enterprise = DB::table('enterprises')->where('user_id', $enterpriseUserId)->first();
        if (! $enterprise) {
            $enterpriseId = DB::table('enterprises')->insertGetId([
                'user_id'      => $enterpriseUserId,
                'company_name' => 'Hubei Longtech Manufacturing Co., Ltd.',
                'industry'     => 'Manufacturing / Engineering',
                'scale'        => 'large',
                'description'  => 'A leading manufacturing company in central China, specializing in precision engineering and smart manufacturing solutions. Actively recruiting Southeast Asian talent.',
                'website'      => 'https://longtech.example.com',
                'address'      => 'No. 88 Gaoxin Avenue, Wuhan, Hubei, China',
                'contact_name' => 'HR Department',
                'contact_phone'=> '+86-27-8800-1234',
                'verified'     => true,
                'prefer_lang'  => 'en',
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
        } else {
            $enterpriseId = $enterprise->id;
        }

        $jobs = [
            [
                'title'       => 'Software Engineer (Bilingual EN/ZH)',
                'description' => "We are looking for a talented Software Engineer to join our digital transformation team.\n\n**Responsibilities:**\n- Develop and maintain web applications using PHP/Laravel and Vue.js\n- Collaborate with cross-functional teams\n- Participate in code reviews\n\n**Why join us:**\nCompetitive salary, relocation assistance, and career growth opportunities.",
                'requirements'=> "- Bachelor's degree in Computer Science or related field\n- 1-3 years of experience with PHP or JavaScript\n- Basic Chinese language skills (HSK 3+)\n- Strong problem-solving skills",
                'location'    => 'Wuhan, Hubei',
                'salary_min'  => 8000,
                'salary_max'  => 15000,
                'job_type'    => 'full_time',
                'status'      => 'published',
                'published_at'=> now()->subDays(3),
                'expires_at'  => now()->addDays(60),
            ],
            [
                'title'       => 'International Sales Representative (Thailand Market)',
                'description' => "Expand our business in the Thai market. Serve as the bridge between our company and Thai clients.\n\n**Responsibilities:**\n- Develop and maintain Thai client relationships\n- Prepare sales proposals and presentations\n- Attend trade fairs and exhibitions\n- Report to the International Business Director",
                'requirements'=> "- Native Thai speaker or fluent in Thai\n- Proficient in English or Chinese\n- 1+ years of B2B sales experience\n- Willing to travel",
                'location'    => 'Wuhan, Hubei',
                'salary_min'  => 10000,
                'salary_max'  => 20000,
                'job_type'    => 'full_time',
                'status'      => 'published',
                'published_at'=> now()->subDays(5),
                'expires_at'  => now()->addDays(45),
            ],
            [
                'title'       => 'Quality Control Engineer',
                'description' => "Join our QC team to ensure our products meet the highest international standards.\n\n**Responsibilities:**\n- Inspect production processes and final products\n- Document quality issues and coordinate corrective actions\n- Communicate with overseas clients on quality standards",
                'requirements'=> "- Degree in Engineering or Manufacturing\n- Knowledge of ISO quality standards\n- English proficiency required\n- Detail-oriented with strong analytical skills",
                'location'    => 'Wuhan, Hubei',
                'salary_min'  => 7000,
                'salary_max'  => 12000,
                'job_type'    => 'full_time',
                'status'      => 'published',
                'published_at'=> now()->subDays(7),
                'expires_at'  => now()->addDays(30),
            ],
            [
                'title'       => 'Supply Chain Coordinator',
                'description' => "Manage and optimize our international supply chain operations.\n\n**Responsibilities:**\n- Coordinate with overseas suppliers and logistics partners\n- Track shipments and resolve delivery issues\n- Maintain supplier database and performance records",
                'requirements'=> "- Degree in Logistics, Supply Chain, or Business\n- Fluent in English; Chinese is a plus\n- Proficiency in Excel/ERP systems\n- 1+ years of supply chain experience preferred",
                'location'    => 'Wuhan, Hubei',
                'salary_min'  => 6000,
                'salary_max'  => 10000,
                'job_type'    => 'full_time',
                'status'      => 'published',
                'published_at'=> now()->subDays(2),
                'expires_at'  => now()->addDays(50),
            ],
            [
                'title'       => 'Marketing Assistant (Internship)',
                'description' => "A 6-month paid internship with potential for full-time conversion.\n\n**Responsibilities:**\n- Assist in creating social media content\n- Help organize events and trade shows\n- Conduct market research reports",
                'requirements'=> "- Currently enrolled in Marketing, Business, or Communications\n- Creative with good communication skills\n- Basic Chinese or English proficiency",
                'location'    => 'Wuhan, Hubei',
                'salary_min'  => 3000,
                'salary_max'  => 5000,
                'job_type'    => 'internship',
                'status'      => 'published',
                'published_at'=> now()->subDays(1),
                'expires_at'  => now()->addDays(30),
            ],
            [
                'title'       => 'Mandarin Language Instructor (Part-time)',
                'description' => "Teach Mandarin Chinese to our international staff and partners.\n\n**Responsibilities:**\n- Design and deliver Mandarin lessons for beginners and intermediate learners\n- Prepare lesson materials tailored to business scenarios\n- Flexible schedule: 2-3 sessions per week",
                'requirements'=> "- Native or near-native Mandarin speaker\n- Teaching certificate (HSK teacher license preferred)\n- Patient and adaptable teaching style",
                'location'    => 'Wuhan, Hubei (Online possible)',
                'salary_min'  => 200,
                'salary_max'  => 400,
                'salary_currency' => 'CNY',
                'job_type'    => 'part_time',
                'status'      => 'published',
                'published_at'=> now()->subDays(4),
                'expires_at'  => now()->addDays(60),
            ],
        ];

        foreach ($jobs as $job) {
            DB::table('jobs')->updateOrInsert(
                ['enterprise_id' => $enterpriseId, 'title' => $job['title']],
                array_merge($job, [
                    'enterprise_id'    => $enterpriseId,
                    'salary_currency'  => $job['salary_currency'] ?? 'CNY',
                    'view_count'       => rand(10, 200),
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ])
            );
        }

        // ─────────────────────────────────────────────────────────────────
        // 4. NEWS / POSTS
        // ─────────────────────────────────────────────────────────────────
        $posts = [
            [
                'title_zh_cn'    => '豪睿国际与武汉大学签署国际人才合作协议',
                'title_en'       => 'HorizonHR Signs International Talent Partnership Agreement with Wuhan University',
                'title_th'       => 'HorizonHR ลงนามข้อตกลงความร่วมมือด้านบุคลากรกับมหาวิทยาลัยอู่ฮั่น',
                'content_zh_cn'  => "<p>2026年5月1日，湖北豪睿国际人才服务有限公司与武汉大学正式签署国际人才合作协议，共同推动东南亚与中国之间的人才交流与合作。</p><p>根据协议，双方将在以下方面开展合作：</p><ul><li>联合举办招聘说明会和职业规划讲座</li><li>为东南亚留学生提供实习和就业机会</li><li>建立双向人才输送机制</li></ul><p>武汉大学校长表示，此次合作将进一步拓宽学生的国际视野，为他们提供更多的就业选择。</p>",
                'content_en'     => "<p>On May 1, 2026, HorizonHR International Talent Service Co., Ltd. and Wuhan University officially signed an international talent partnership agreement to jointly promote talent exchange between Southeast Asia and China.</p><p>Under the agreement, both parties will collaborate in the following areas:</p><ul><li>Co-organizing recruitment seminars and career planning workshops</li><li>Providing internship and employment opportunities for Southeast Asian students</li><li>Establishing a two-way talent pipeline</li></ul><p>The President of Wuhan University noted that this partnership will further broaden students' international perspectives and provide more career options.</p>",
                'content_th'     => "<p>เมื่อวันที่ 1 พฤษภาคม 2026 บริษัท HorizonHR International Talent Service จำกัด และมหาวิทยาลัยอู่ฮั่นได้ลงนามข้อตกลงความร่วมมือด้านบุคลากรระหว่างประเทศอย่างเป็นทางการ เพื่อร่วมกันส่งเสริมการแลกเปลี่ยนบุคลากรระหว่างเอเชียตะวันออกเฉียงใต้และจีน</p><p>ภายใต้ข้อตกลง ทั้งสองฝ่ายจะร่วมมือในด้านต่อไปนี้:</p><ul><li>จัดสัมมนาการรับสมัครงานและเวิร์กช็อปวางแผนอาชีพร่วมกัน</li><li>จัดหาโอกาสฝึกงานและการจ้างงานสำหรับนักศึกษาจากเอเชียตะวันออกเฉียงใต้</li><li>สร้างระบบส่งต่อบุคลากรแบบสองทิศทาง</li></ul>",
                'category'       => 'company_news',
                'status'         => 'published',
                'view_count'     => 342,
                'published_at'   => now()->subDays(4),
            ],
            [
                'title_zh_cn'    => '2026年度东南亚赴华留学白皮书发布',
                'title_en'       => '2026 White Paper on Southeast Asian Students Studying in China Released',
                'title_th'       => 'เผยแพร่รายงานฉบับขาวว่าด้วยนักศึกษาเอเชียตะวันออกเฉียงใต้ที่ศึกษาในจีน ปี 2026',
                'content_zh_cn'  => "<p>近日，豪睿国际发布了《2026年东南亚赴华留学白皮书》，对过去一年东南亚各国赴华留学的趋势进行了深入分析。</p><p>报告显示，2025年东南亚赴华留学生总数同比增长15%，其中泰国、越南、印度尼西亚位居前三。湖北省作为重要的教育中心，吸引了约12%的东南亚留学生。</p><p>白皮书同时指出，随着中国制造业和服务业的快速发展，懂中文的东南亚人才需求正在快速增长。</p>",
                'content_en'     => "<p>HorizonHR recently released the \"2026 White Paper on Southeast Asian Students Studying in China\", providing an in-depth analysis of trends in Southeast Asian students studying in China over the past year.</p><p>The report shows that the total number of Southeast Asian students in China increased by 15% year-on-year in 2025, with Thailand, Vietnam, and Indonesia ranking in the top three. Hubei Province, as an important educational hub, attracted approximately 12% of Southeast Asian students.</p><p>The white paper also noted that with the rapid development of China's manufacturing and service industries, demand for Chinese-speaking Southeast Asian talent is growing rapidly.</p>",
                'content_th'     => "<p>เมื่อเร็วๆ นี้ HorizonHR ได้เผยแพร่ \"รายงานฉบับขาวว่าด้วยนักศึกษาเอเชียตะวันออกเฉียงใต้ที่ศึกษาในจีน ปี 2026\" ซึ่งวิเคราะห์แนวโน้มเชิงลึกของนักศึกษาจากเอเชียตะวันออกเฉียงใต้ที่ศึกษาในจีนในช่วงปีที่ผ่านมา</p><p>รายงานแสดงให้เห็นว่าจำนวนนักศึกษาจากเอเชียตะวันออกเฉียงใต้ในจีนเพิ่มขึ้น 15% เมื่อเทียบกับปีก่อน ในปี 2025 โดยไทย เวียดนาม และอินโดนีเซียอยู่ใน 3 อันดับแรก</p>",
                'category'       => 'study_abroad',
                'status'         => 'published',
                'view_count'     => 518,
                'published_at'   => now()->subDays(8),
            ],
            [
                'title_zh_cn'    => '湖北省2026年国际人才引进政策解读',
                'title_en'       => 'Hubei Province 2026 International Talent Attraction Policy Explained',
                'title_th'       => 'อธิบายนโยบายดึงดูดบุคลากรระหว่างประเทศของมณฑลหูเป่ย ปี 2026',
                'content_zh_cn'  => "<p>湖北省近期出台了一系列新政策，旨在吸引更多国际人才来鄂工作和生活。主要政策亮点包括：</p><ul><li><strong>住房补贴：</strong>符合条件的外籍人才可申请每月最高3000元的住房补贴</li><li><strong>落户便利化：</strong>简化外籍高层次人才的居留许可申请流程</li><li><strong>子女入学：</strong>外籍人才子女可就读优质公立学校</li><li><strong>税收优惠：</strong>在特定高新技术产业工作的外籍人才享受税收减免</li></ul>",
                'content_en'     => "<p>Hubei Province recently introduced a series of new policies aimed at attracting more international talent to work and live in the province. Key policy highlights include:</p><ul><li><strong>Housing Subsidy:</strong> Qualified foreign talent can apply for a monthly housing subsidy of up to CNY 3,000</li><li><strong>Streamlined Registration:</strong> Simplified residence permit applications for high-level foreign talent</li><li><strong>Children's Education:</strong> Children of foreign talent can attend quality public schools</li><li><strong>Tax Benefits:</strong> Foreign talent working in specific high-tech industries enjoys tax reductions</li></ul>",
                'content_th'     => "<p>มณฑลหูเป่ยเพิ่งออกนโยบายใหม่หลายชุดเพื่อดึงดูดบุคลากรต่างชาติให้มาทำงานและใช้ชีวิตในมณฑล จุดเด่นของนโยบายสำคัญ ได้แก่:</p><ul><li><strong>เงินอุดหนุนที่อยู่อาศัย:</strong> บุคลากรต่างชาติที่มีคุณสมบัติสามารถขอรับเงินอุดหนุนค่าที่พักสูงสุด 3,000 หยวนต่อเดือน</li><li><strong>การลงทะเบียนที่คล่องตัว:</strong> กระบวนการขอใบอนุญาตพำนักที่ง่ายขึ้นสำหรับบุคลากรต่างชาติระดับสูง</li></ul>",
                'category'       => 'industry_news',
                'status'         => 'published',
                'view_count'     => 287,
                'published_at'   => now()->subDays(12),
            ],
            [
                'title_zh_cn'    => '春季校园招聘季正式启动',
                'title_en'       => 'Spring Campus Recruitment Season Officially Launched',
                'title_th'       => 'ฤดูกาลรับสมัครงานในมหาวิทยาลัยฤดูใบไม้ผลิเปิดตัวอย่างเป็นทางการแล้ว',
                'content_zh_cn'  => "<p>豪睿国际宣布2026年春季校园招聘季正式启动，共有30余家湖北重点企业参与。招聘岗位涵盖工程、商务、IT、市场营销等多个领域，面向全球东南亚高校毕业生开放。</p><p><strong>参与企业亮点：</strong></p><ul><li>长江高科技集团 — 软件工程师岗位</li><li>湖北隆泰制造有限公司 — 国际销售代表</li><li>武汉百信金融 — 金融分析师</li></ul><p>有意向的同学可在本平台直接投递简历，我们将在3个工作日内回复。</p>",
                'content_en'     => "<p>HorizonHR announces the official launch of the 2026 Spring Campus Recruitment Season, with 30+ key Hubei enterprises participating. Open positions span engineering, business, IT, and marketing, open to Southeast Asian university graduates worldwide.</p><p><strong>Participating Enterprise Highlights:</strong></p><ul><li>Yangtze Hi-Tech Group — Software Engineer positions</li><li>Hubei Longtech Manufacturing — International Sales Representatives</li><li>Wuhan Baixing Finance — Financial Analysts</li></ul><p>Interested candidates can submit resumes directly on this platform, and we will respond within 3 business days.</p>",
                'content_th'     => "<p>HorizonHR ประกาศเปิดตัวฤดูกาลรับสมัครงานในมหาวิทยาลัยฤดูใบไม้ผลิ ปี 2026 อย่างเป็นทางการ โดยมีบริษัทชั้นนำในหูเป่ยกว่า 30 แห่งเข้าร่วม ตำแหน่งที่เปิดรับครอบคลุมด้านวิศวกรรม ธุรกิจ IT และการตลาด เปิดรับบัณฑิตจากมหาวิทยาลัยในเอเชียตะวันออกเฉียงใต้ทั่วโลก</p>",
                'category'       => 'recruitment',
                'status'         => 'published',
                'view_count'     => 621,
                'published_at'   => now()->subDays(2),
            ],
            [
                'title_zh_cn'    => '豪睿国际荣获湖北省优秀人才服务机构称号',
                'title_en'       => 'HorizonHR Awarded "Hubei Province Outstanding Talent Service Organization"',
                'title_th'       => 'HorizonHR ได้รับรางวัล องค์กรบริการบุคลากรดีเด่นแห่งมณฑลหูเป่ย',
                'content_zh_cn'  => "<p>近日，湖北省人力资源和社会保障厅授予豪睿国际&ldquo;湖北省优秀人才服务机构&rdquo;称号，以表彰公司在促进国际人才交流方面的突出贡献。</p><p>自成立以来，豪睿国际已累计为逾500名东南亚留学生提供就业辅导服务，成功帮助其中300余人在湖北省企业就职，受到社会各界广泛好评。</p>",
                'content_en'     => "<p>Recently, the Hubei Provincial Department of Human Resources and Social Security awarded HorizonHR the title of \"Hubei Province Outstanding Talent Service Organization\" in recognition of the company's outstanding contributions to promoting international talent exchange.</p><p>Since its establishment, HorizonHR has provided employment guidance services to over 500 Southeast Asian students, successfully helping more than 300 of them find employment in Hubei enterprises, earning widespread praise from all sectors of society.</p>",
                'content_th'     => "<p>เมื่อเร็วๆ นี้ กรมทรัพยากรมนุษย์และประกันสังคมมณฑลหูเป่ยได้มอบรางวัล \"องค์กรบริการบุคลากรดีเด่นแห่งมณฑลหูเป่ย\" ให้แก่ HorizonHR เพื่อยกย่องผลงานดีเด่นของบริษัทในการส่งเสริมการแลกเปลี่ยนบุคลากรระหว่างประเทศ</p>",
                'category'       => 'company_news',
                'status'         => 'published',
                'view_count'     => 195,
                'published_at'   => now()->subDays(15),
            ],
        ];

        foreach ($posts as $post) {
            DB::table('posts')->updateOrInsert(
                ['title_en' => $post['title_en']],
                array_merge($post, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        // ─────────────────────────────────────────────────────────────────
        // 5. STUDENTS + TALENT CARDS
        // ─────────────────────────────────────────────────────────────────
        $students = [
            [
                'email'       => 'student.somying@horizonhr.test',
                'password'    => Hash::make('Demo@1234'),
                'profile' => [
                    'name'        => 'Somying Thanakit',
                    'nationality' => 'Thai',
                    'phone'       => '+66 89-123-4567',
                    'gender'      => 'female',
                    'birth_date'  => '2000-03-15',
                    'bio'         => 'Final-year Business Administration student at Chiang Mai University. Completed a 3-month Mandarin language program at Wuhan University. Passionate about international trade and cross-cultural communication.',
                    'verified'    => true,
                    'prefer_lang' => 'th',
                ],
                'talent' => [
                    'display_name'  => 'Somying T.',
                    'major'         => 'Business Administration',
                    'education'     => 'Bachelor\'s Degree',
                    'university'    => 'Chiang Mai University',
                    'languages'     => json_encode([
                        ['language' => 'Thai', 'level' => 'Native'],
                        ['language' => 'English', 'level' => 'Intermediate (B2)'],
                        ['language' => 'Mandarin', 'level' => 'Basic (HSK 3)'],
                    ]),
                    'skills'        => json_encode(['Microsoft Office', 'Financial Analysis', 'Market Research', 'Customer Service']),
                    'job_intention' => 'International Sales, Business Development, Trade Coordinator in Hubei, China',
                    'status'        => 'visible',
                ],
            ],
            [
                'email'       => 'student.anuwat@horizonhr.test',
                'password'    => Hash::make('Demo@1234'),
                'profile' => [
                    'name'        => 'Anuwat Charoenporn',
                    'nationality' => 'Thai',
                    'phone'       => '+66 81-234-5678',
                    'gender'      => 'male',
                    'birth_date'  => '1999-07-22',
                    'bio'         => 'Software Engineering graduate from King Mongkut\'s University of Technology. 2 years of experience in web development. Currently improving Mandarin skills to pursue opportunities in China\'s tech sector.',
                    'verified'    => true,
                    'prefer_lang' => 'en',
                ],
                'talent' => [
                    'display_name'  => 'Anuwat C.',
                    'major'         => 'Software Engineering',
                    'education'     => 'Bachelor\'s Degree',
                    'university'    => 'King Mongkut\'s University of Technology Thonburi',
                    'languages'     => json_encode([
                        ['language' => 'Thai', 'level' => 'Native'],
                        ['language' => 'English', 'level' => 'Advanced (C1)'],
                        ['language' => 'Mandarin', 'level' => 'Basic (HSK 2)'],
                    ]),
                    'skills'        => json_encode(['PHP', 'Vue.js', 'Python', 'MySQL', 'Docker', 'Git']),
                    'job_intention' => 'Backend Developer, Full-Stack Engineer, Tech Lead at Chinese tech companies',
                    'status'        => 'featured',
                ],
            ],
            [
                'email'       => 'student.nguyen@horizonhr.test',
                'password'    => Hash::make('Demo@1234'),
                'profile' => [
                    'name'        => 'Nguyen Thi Lan',
                    'nationality' => 'Vietnamese',
                    'phone'       => '+84 90-345-6789',
                    'gender'      => 'female',
                    'birth_date'  => '2001-11-08',
                    'bio'         => 'International Relations student at Hanoi University. Strong interest in China-ASEAN relations and trade policy. HSK 5 certified.',
                    'verified'    => true,
                    'prefer_lang' => 'en',
                ],
                'talent' => [
                    'display_name'  => 'Nguyen T.L.',
                    'major'         => 'International Relations',
                    'education'     => 'Bachelor\'s Degree',
                    'university'    => 'Hanoi University',
                    'languages'     => json_encode([
                        ['language' => 'Vietnamese', 'level' => 'Native'],
                        ['language' => 'Mandarin', 'level' => 'Advanced (HSK 5)'],
                        ['language' => 'English', 'level' => 'Upper-Intermediate (B2)'],
                    ]),
                    'skills'        => json_encode(['Policy Analysis', 'Translation (VI/ZH/EN)', 'Public Speaking', 'Research']),
                    'job_intention' => 'Government Relations, Trade Policy, Corporate Affairs, Translator/Interpreter',
                    'status'        => 'featured',
                ],
            ],
            [
                'email'       => 'student.putri@horizonhr.test',
                'password'    => Hash::make('Demo@1234'),
                'profile' => [
                    'name'        => 'Putri Rahayu',
                    'nationality' => 'Indonesian',
                    'phone'       => '+62 812-456-7890',
                    'gender'      => 'female',
                    'birth_date'  => '2000-05-30',
                    'bio'         => 'Accounting graduate from Universitas Indonesia. Completed a 1-year exchange program at Zhongnan University of Economics and Law. Experienced in financial reporting and tax compliance.',
                    'verified'    => true,
                    'prefer_lang' => 'en',
                ],
                'talent' => [
                    'display_name'  => 'Putri R.',
                    'major'         => 'Accounting',
                    'education'     => 'Bachelor\'s Degree',
                    'university'    => 'Universitas Indonesia',
                    'languages'     => json_encode([
                        ['language' => 'Indonesian', 'level' => 'Native'],
                        ['language' => 'English', 'level' => 'Intermediate (B2)'],
                        ['language' => 'Mandarin', 'level' => 'Intermediate (HSK 4)'],
                    ]),
                    'skills'        => json_encode(['Financial Reporting', 'Tax Compliance', 'SAP', 'Excel Advanced', 'Budgeting']),
                    'job_intention' => 'Financial Analyst, Accountant, Audit Associate at multinational firms in China',
                    'status'        => 'visible',
                ],
            ],
        ];

        foreach ($students as $s) {
            $user = DB::table('users')->where('email', $s['email'])->first();
            if (! $user) {
                $userId = DB::table('users')->insertGetId([
                    'role'           => 'student',
                    'email'          => $s['email'],
                    'password'       => $s['password'],
                    'status'         => 'active',
                    'prefer_lang'    => $s['profile']['prefer_lang'],
                    'email_verified' => true,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ]);
            } else {
                $userId = $user->id;
            }

            $student = DB::table('students')->where('user_id', $userId)->first();
            if (! $student) {
                $studentId = DB::table('students')->insertGetId(array_merge(
                    $s['profile'],
                    ['user_id' => $userId, 'created_at' => $now, 'updated_at' => $now]
                ));
            } else {
                $studentId = $student->id;
            }

            DB::table('talent_cards')->updateOrInsert(
                ['student_id' => $studentId],
                array_merge($s['talent'], ['student_id' => $studentId, 'created_at' => $now, 'updated_at' => $now])
            );
        }

        $this->command->info('✓ DummyDataSeeder complete:');
        $this->command->info('  - 6 Universities');
        $this->command->info('  - 4 Seminars (3 scheduled, 1 ended)');
        $this->command->info('  - 1 Enterprise + 6 Jobs');
        $this->command->info('  - 5 News Posts');
        $this->command->info('  - 4 Students + Talent Cards');
        $this->command->info('');
        $this->command->info('Demo accounts (password: Demo@1234):');
        $this->command->info('  Enterprise: demo.enterprise@horizonhr.test');
        $this->command->info('  Student 1:  student.somying@horizonhr.test (Thai)');
        $this->command->info('  Student 2:  student.anuwat@horizonhr.test (Thai)');
        $this->command->info('  Student 3:  student.nguyen@horizonhr.test (Vietnamese)');
        $this->command->info('  Student 4:  student.putri@horizonhr.test (Indonesian)');
    }
}
