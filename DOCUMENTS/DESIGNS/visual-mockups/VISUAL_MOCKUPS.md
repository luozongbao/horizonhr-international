# HBHR Visual Mockups - Design Specifications
## Hubei Horizon International Talent Service Website

**Project:** HBHR - 湖北豪睿国际人才服务有限公司
**Status:** Visual Mockups Completed
**Designer:** ยู (u)
**Date:** 2026-04-16
**Design System:** Based on DESIGN_SYSTEM.md + Wireframes

---

## 🎨 Design System Reference

### Colors
| Token | Hex | Usage |
|-------|-----|-------|
| Primary | `#003366` | Headers, nav, primary buttons, footer |
| Primary Hover | `#002244` | Button hover states |
| Secondary | `#E6F0FF` | Section backgrounds, highlights, badges |
| Accent | `#FF6B35` | CTAs, important actions, urgency |
| White | `#FFFFFF` | Backgrounds, cards, text on dark |
| Text | `#1A1A2E` | Body text |
| Text Secondary | `#6C757D` | Captions, secondary info |
| Border | `#DEE2E6` | Borders, dividers |

### Typography
- **Chinese:** Microsoft YaHei, PingFang SC
- **English:** Arial, Helvetica, sans-serif
- **Scale:** H1: 48px/700, H2: 36px/600, H3: 28px/600, H4: 22px/500

### Spacing (8px base)
- xs: 4px, sm: 8px, md: 16px, lg: 24px, xl: 32px, xxl: 48px

### Components
- **Cards:** bg white, border 1px #DEE2E6, radius 8px, shadow `0 2px 8px rgba(0,51,102,0.08)`
- **Buttons:** radius 6px, padding 12px 24px
- **Input:** height 44px, border 1px #DEE2E6, focus border 2px #003366

---

## Module 1: Home Page (首页)

### PC Layout (1920x1080)

#### Header
```
┌─────────────────────────────────────────────────────────────────────────────┐
│ [LOGO: Hubei Horizon HR]  Home | About | Study in China | Talent Pool        │
│                           Corporate | Seminar | News | Contact    [CN/EN]    │
│                                                                   [Login]   │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Height: 72px
- Background: white
- Border-bottom: 1px solid #DEE2E6
- Logo: left aligned, 180px wide
- Nav: centered, gap 32px between items, font 16px/500
- Actions: right aligned, language toggle + login button

#### Banner Section
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                              │
│   ████████████████████████████████████████████████████████████████████████  │
│   █                                                                          █  │
│   █       "Connect Southeast Asian Youth with Chinese Universities"          █  │
│   █                                                                          █  │
│   █       [Study in China]  [Services]  [Employment]  [Talent]             █  │
│   █                                                                          █  │
│   ████████████████████████████████████████████████████████████████████████  │
│                                                                              │
│   ○ ● ○ ○  (carousel indicators)                                           │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Height: 480px
- Background: Gradient from #003366 to #004080 (subtle)
- Text: white, centered
- H1: 48px/700, tagline: 20px/400
- 4 CTA buttons: bg #FF6B35, white text, radius 6px, padding 14px 28px
- Carousel indicators: 12px circles, active = #FF6B35, inactive = white/50%

#### Core Section Cards (4 cards)
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                              │
│  ┌───────────────┐  ┌───────────────┐  ┌───────────────┐  ┌─────────────┐  │
│  │  🎓           │  │  👥            │  │  🤝            │  │  🎥         │  │
│  │               │  │               │  │               │  │             │  │
│  │ Study in China │  │ Outstanding   │  │ Corporate     │  │ Seminar     │  │
│  │               │  │ Students       │  │ Cooperation   │  │ Center      │  │
│  │ Chinese Univ  │  │ Talent Pool   │  │ Join Us       │  │ Live Watch  │  │
│  │ Applications   │  │ 500+ Profiles │  │ 50+ Partners  │  │             │  │
│  │               │  │               │  │               │  │             │  │
│  │  [View More →]│  │  [View More →]│  │  [View More →]│  │  [More →]   │  │
│  └───────────────┘  └───────────────┘  └───────────────┘  └─────────────┘  │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Cards: 280px width, equal spacing
- Background: white
- Icon: 48px, color #003366
- Title: 22px/600 #003366
- Description: 16px/400 #6C757D
- CTA: 16px/500 #FF6B35 with arrow →
- Hover: translateY(-4px), shadow increase

#### Partner Scrolling Bar
```
┌─────────────────────────────────────────────────────────────────────────────┐
│  Partner Universities: [Logo][Logo][Logo][Logo][Logo] → (auto-scroll)        │
│  Partner Companies:    [Logo][Logo][Logo][Logo][Logo] → (auto-scroll)       │
│  Outstanding Students: [Photo][Photo][Photo][Photo] → (auto-scroll)         │
│  Upcoming Seminars:    [Card][Card][Card] → (auto-scroll)                  │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Background: #E6F0FF
- Height: 120px per row
- Auto-scroll: 30px/second, pause on hover
- Logos: 80px height, grayscale → color on hover

#### Core Advantages (4 items)
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌───────────────┐    │
│  │  🌍          │  │  🏛️          │  │  📊          │  │  🔄          │    │
│  │              │  │              │  │              │  │              │    │
│  │ Stable SE Asia│  │ Rich Univ    │  │ Targeted     │  │ Full Closed  │    │
│  │ Channels     │  │ Partnerships │  │ Chinese Ent. │  │ loop Service │    │
│  │ 10+ Years    │  │ 50+ Partners │  │ Recruitment  │  │              │    │
│  │ Experience   │  │ Across China │  │ One-Stop     │  │              │    │
│  └──────────────┘  └──────────────┘  └──────────────┘  └───────────────┘    │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Background: white
- Section padding: 64px vertical
- Icon: 56px, bg #E6F0FF, radius 12px, color #003366
- Title: 20px/600 #003366
- Subtitle: 14px/400 #6C757D
- Layout: 4 columns, 24px gap

#### Footer
```
┌─────────────────────────────────────────────────────────────────────────────┐
│  [Address] [Phone] [Email] [WeChat] [WhatsApp] [Line]                       │
│  [Privacy Policy] [Terms of Service] [Site Map]                            │
│  Copyright © 2026 Hubei Horizon International. All Rights Reserved.        │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Background: #003366
- Text: white
- Links: white/70%, hover white
- Social icons: 24px, white

---

### Mobile Layout (375x812)

#### Header
- Height: 56px
- Logo left, hamburger right, language toggle + user icon
- Hamburger opens slide-in drawer from right

#### Banner
- Height: 280px
- Text scales down: H1 32px, tagline 16px
- 4 CTA buttons stack 2x2

#### Core Cards
- 2x2 grid
- Cards: full width with 16px margin

#### Partner Bar
- Vertical scroll within card
- 80px height

#### Advantages
- Single column stack

---

## Module 2: About Us (关于我们)

### Visual Specifications

#### Page Hero
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                              │
│                        About Us / 关于我们                                   │
│                        我们连接东南亚青年与中国高校                           │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Background: #003366
- Height: 200px
- Text: white, centered
- H1: 48px/700, subtitle: 20px/400

#### Company Introduction Section
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                              │
│  ┌─────────────────────┐    Hubei Horizon International                     │
│  │                     │    Talent Service Co., Ltd.                        │
│  │   [Company Photo]   │    湖北豪睿国际人才服务有限公司                     │
│  │   400x300px         │    ─────────────────────────────                    │
│  │                     │    Founded: 2018 | Team: 50+                       │
│  └─────────────────────┘                                                    │
│                                                                              │
│  Our core mission is to connect Southeast Asian youth with Chinese          │
│  universities and deliver quality local talent to Chinese enterprises.     │
│                                                                              │
│  [Professional] [International] [Reliable] [Closed-loop]                   │
│  (badges: bg #E6F0FF, text #003366, radius 20px, padding 6px 16px)          │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Section padding: 64px
- Image: radius 8px, shadow
- Title: 28px/600 #003366
- Description: 18px/400, line-height 1.8
- Core values: horizontal flex, gap 16px

#### Timeline Section
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        Our Journey / 发展历程                                │
│                        ────────────────────                                 │
│                                                                              │
│   ●───────────●───────────●───────────●───────────●───────────●             │
│   2018       2019        2020        2021        2022        2023           │
│                                                                              │
│   ┌────────┐  ┌────────┐  ┌────────┐  ┌────────┐  ┌────────┐  ┌────────┐   │
│   │Founded │  │SE Asia │  │50+     │  │Launch  │  │Online  │  │10,000+ │   │
│   │Company │  │Office  │  │University│ │Corporate│ │Interview│ │Students│   │
│   │        │  │Opened  │  │Partners │  │Portal  │  │System  │  │Served  │   │
│   └────────┘  └────────┘  └────────┘  └────────┘  └────────┘  └────────┘   │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Timeline line: 4px, #003366
- Nodes: 16px circles, #003366 fill, white center
- Cards: 160px width, bg white, border 1px #DEE2E6, shadow
- Year: 18px/600 #003366
- Title: 14px/500

#### Service Flow
```
┌─────────────────────────────────────────────────────────────────────────────┐
│              Full-Process Service / 全流程服务                                │
│              ────────────────────────────                                   │
│                                                                              │
│  ┌──────────┐     ┌──────────┐     ┌──────────┐     ┌──────────┐          │
│  │   📋     │ ──► │   🏠     │ ──► │   📚     │ ──► │   💼     │          │
│  │          │     │          │     │          │     │          │          │
│  │Recruitment│   │ Living   │     │ Academic │     │Employment│          │
│  │ Service   │    │ Services │     │ Tutoring │     │ Placement │          │
│  │          │     │          │     │          │     │          │          │
│  │Resume     │    │Airport   │     │Language  │     │Job       │          │
│  │Collection │    │Pickup    │     │Training  │     │Matching  │          │
│  └──────────┘     └──────────┘     └──────────┘     └──────────┘          │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Arrows: 32px, #FF6B35
- Cards: 200px width, centered
- Icons: 48px, bg #E6F0FF, radius 50%, color #003366
- Title: 18px/600, subtitle: 14px/400 #6C757D

#### Team Section
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        Core Team / 核心团队                                   │
│                        ──────────────                                        │
│                                                                              │
│   ┌───────────┐  ┌───────────┐  ┌───────────┐  ┌───────────┐               │
│   │           │  │           │  │           │  │           │               │
│   │   [Photo]│  │   [Photo]│  │   [Photo]│  │   [Photo]│               │
│   │   120x120│  │   120x120│  │   120x120│  │   120x120│               │
│   │           │  │           │  │           │  │           │               │
│   │   Name   │  │   Name   │  │   Name   │  │   Name   │               │
│   │   CEO    │  │   COO    │  │   CTO    │  │  Director│               │
│   └───────────┘  └───────────┘  └───────────┘  └───────────┘               │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Photo: 120x120px, radius 50% (circle)
- Name: 18px/600 #003366
- Title: 14px/400 #6C757D
- Hover: scale 1.05

#### Certificates
```
┌─────────────────────────────────────────────────────────────────────────────┐
│              Certificates & Licenses / 资质证书                              │
│              ──────────────────────────────                                  │
│                                                                              │
│   ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐               │
│   │ 📜   │  │ 📜   │  │ 📜   │  │ 📜   │  │ 📜   │  │ 📜   │               │
│   │      │  │      │  │      │  │      │  │      │  │      │               │
│   │Cert 1│  │Cert 2│  │Cert 3│  │Cert 4│  │Cert 5│  │Cert 6│               │
│   └──────┘  └──────┘  └──────┘  └──────┘  └──────┘  └──────┘               │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Certificate cards: 120x160px
- Background: #F8F9FA
- Border: 1px #DEE2E6
- Image preview icon on hover
- Lightbox on click

---

## Module 3: Study in China (留学中国)

### Visual Specifications

#### Page Hero
- Same style as About Us
- Title: "Study in China / 留学中国"
- Subtitle: "Your Path to Chinese Universities"

#### Programs Grid (2x2)
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    Programs We Offer / 招生项目                              │
│                    ────────────────────────                                  │
│                                                                              │
│   ┌─────────────────┐    ┌─────────────────┐                                │
│   │     🎓          │    │     🎓          │                                │
│   │                 │    │                 │                                │
│   │   Vocational    │    │   Bachelor's    │                                │
│   │   Training      │    │   Degree        │                                │
│   │                 │    │                 │                                │
│   │   • 1-3 years   │    │   • 4 years     │                                │
│   │   • Practical   │    │   • Academic    │                                │
│   │   • Job ready   │    │   • Research    │                                │
│   │                 │    │                 │                                │
│   │   [Apply Now →] │    │   [Apply Now →] │                                │
│   │   (bg: #FF6B35) │    │   (bg: #FF6B35) │                                │
│   └─────────────────┘    └─────────────────┘                                │
│                                                                              │
│   ┌─────────────────┐    ┌─────────────────┐                                │
│   │     🎓          │    │     🗣️          │                                │
│   │                 │    │                 │                                │
│   │   Master's      │    │   Chinese       │                                │
│   │   Degree        │    │   Language      │                                │
│   │                 │    │   Training      │                                │
│   │   • 2-3 years   │    │   • HSK prep    │                                │
│   │   • Research    │    │   • Immersion   │                                │
│   │   • Graduate    │    │   • Certificates│                                │
│   │                 │    │                 │                                │
│   │   [Apply Now →] │    │   [Apply Now →] │                                │
│   │   (bg: #FF6B35) │    │   (bg: #FF6B35) │                                │
│   └─────────────────┘    └─────────────────┘                                │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Card size: 400x320px
- Icon: 64px, #003366
- Title: 24px/600 #003366
- Bullet points: 14px/400 #6C757D
- CTA button: 160x48px, bg #FF6B35, white text, radius 6px

#### Application Process (5 steps)
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        How to Apply / 申请指南                               │
│                        ─────────────────                                     │
│                                                                              │
│   ┌───────┐    ┌───────┐    ┌───────┐    ┌───────┐    ┌───────┐            │
│   │   1   │───►│   2   │───►│   3   │───►│   4   │───►│   5   │            │
│   │       │    │       │    │       │    │       │    │       │            │
│   │Submit │    │ Doc   │    │ Visa  │    │Airport│    │Start  │            │
│   │Online │    │Review │    │Apply  │    │Pickup  │    │Study  │            │
│   │Form   │    │       │    │       │    │       │    │       │            │
│   └───────┘    └───────┘    └───────┘    └───────┘    └───────┘            │
│                                                                              │
│   Required Materials:                                                        │
│   □ Passport copy  □ Academic transcripts  □ Language certificate          │
│   □ Health check   □ Financial proof        □ Photos                       │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Step circles: 80px diameter, bg #003366, white text (number)
- Active step: bg #FF6B35
- Completed: bg #28A745 with checkmark
- Arrows: 40px, #DEE2E6
- Step label: 14px/500 below circle
- Materials checklist: 16px, green checkboxes

#### Partner Universities Grid
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                  Partner Universities / 合作院校                              │
│                  ──────────────────────────                                  │
│                                                                              │
│  [Filter: All Regions ▼]  [Filter: All Majors ▼]  [🔍 Search universities] │
│                                                                              │
│   ┌───────────┐  ┌───────────┐  ┌───────────┐  ┌───────────┐               │
│   │  [Logo]   │  │  [Logo]   │  │  [Logo]   │  │  [Logo]   │               │
│   │           │  │           │  │           │  │           │               │
│   │  Peking   │  │  Tsinghua │  │   Wuhan  │  │ Fudan     │               │
│   │  Univ     │  │           │  │  University│  │           │               │
│   │  Beijing  │  │  Beijing  │  │  Wuhan    │  │ Shanghai  │               │
│   │           │  │           │  │           │  │           │               │
│   │  [View →] │  │  [View →] │  │  [View →] │  │  [View →] │               │
│   └───────────┘  └───────────┘  └───────────┘  └───────────┘               │
│                                                                              │
│   ┌───────────┐  ┌───────────┐  ┌───────────┐  ┌───────────┐               │
│   │  [Logo]   │  │  [Logo]   │  │  [Logo]   │  │  [Logo]   │               │
│   │  Shanghai │  │  Zhejiang │  │  Xi'an    │  │  Nankai   │               │
│   │  Jiao Tong│  │  Univ     │  │  Jiaotong │  │  Univ     │               │
│   │  Shanghai │  │  Hangzhou │  │  Xi'an    │  │  Tianjin  │               │
│   │           │  │           │  │           │  │           │               │
│   │  [View →] │  │  [View →] │  │  [View →] │  │  [View →] │               │
│   └───────────┘  └───────────┘  └───────────┘  └───────────┘               │
│                                                                              │
│   [Load More ↓]                                                              │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- University cards: 240x280px
- Logo: 120x80px, centered
- Name: 18px/600 #003366
- Location: 14px/400 #6C757D
- CTA: 14px/500 #FF6B35
- Grid: 4 columns desktop, 2 columns tablet, 1 column mobile

#### FAQ Accordion
```
┌─────────────────────────────────────────────────────────────────────────────┐
│            Frequently Asked Questions / 常见问题                             │
│            ───────────────────────────────────                              │
│                                                                              │
│   ┌───────────────────────────────────────────────────────────────────┐    │
│   │ ▶ How difficult is the application process?               [−]     │    │
│   ├───────────────────────────────────────────────────────────────────┤    │
│   │   Answer text goes here with detailed information...             │    │
│   └───────────────────────────────────────────────────────────────────┘    │
│   ┌───────────────────────────────────────────────────────────────────┐    │
│   │ ▶ What are the language requirements?                    [+]     │    │
│   └───────────────────────────────────────────────────────────────────┘    │
│   ┌───────────────────────────────────────────────────────────────────┐    │
│   │ ▶ What are the tuition fees?                             [+]     │    │
│   └───────────────────────────────────────────────────────────────────┘    │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Accordion item: bg white, border 1px #DEE2E6, radius 8px
- Question: 16px/500 #003366, padding 16px
- Icon: chevron, rotates 180° when expanded
- Answer: 14px/400 #6C757D, padding 0 16px 16px

#### CTA Banner
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                              │
│              Ready to Start Your Journey? / 准备好开始你的留学之旅了吗？       │
│                                                                              │
│                         [Apply Now →]                                        │
│                         (bg: #FF6B35, 200x56px)                              │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Background: #003366
- Text: white, H2 36px/600
- CTA: bg #FF6B35, white text, 200x56px, radius 6px

---

## Module 4: Talent Pool (人才库)

### Visual Specifications

#### Page Hero
- Title: "Talent Pool / 人才库"
- Subtitle: "Connecting Outstanding SE Asian Students with Chinese Enterprises"

#### Filter Bar
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                              │
│  [Nationality ▼]  [Major ▼]  [Language ▼]  [Education ▼]  [Availability ▼]  │
│                                                                              │
│  Found: 523 talents    Sort: [Newest ▼]    [▦ Grid] [≡ List]                │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Filters: dropdown style, 160px width, height 40px
- Background: white
- Border: 1px #DEE2E6, focus border #003366
- Count: 18px/600 #003366
- View toggle: 32px icons, active = #003366

#### Talent Cards Grid (3 columns)
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                              │
│  ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐           │
│  │  ┌──────────┐    │  │  ┌──────────┐    │  │  ┌──────────┐    │          │
│  │  │          │    │  │  │          │    │  │  │          │    │          │
│  │  │  [Photo] │    │  │  │  [Photo] │    │  │  │  [Photo] │    │          │
│  │  │   80x80  │    │  │  │   80x80  │    │  │  │   80x80  │    │          │
│  │  │          │    │  │  │          │    │  │  │          │    │          │
│  │  └──────────┘    │  │  └──────────┘    │  │  └──────────┘    │          │
│  │                  │  │                  │  │                  │          │
│  │  Somchai Smith   │  │  Rina Wijaya     │  │  Budi Santoso    │          │
│  │  🇹🇭 Thailand    │  │  🇮🇩 Indonesia   │  │  🇻🇳 Vietnam    │          │
│  │                  │  │                  │  │                  │          │
│  │  🎓 Wuhan Univ    │  │  🎓 Peking Univ  │  │  🎓 Tsinghua    │          │
│  │  Business Admin  │  │  Computer Science│  │  Finance         │          │
│  │                  │  │                  │  │                  │          │
│  │  🗣️ CN: HSK4     │  │  🗣️ CN: HSK5     │  │  🗣️ CN: HSK3     │          │
│  │     EN: IELTS 6.0│  │     EN: IELTS 7.0│  │     EN: IELTS 5.0│          │
│  │                  │  │                  │  │                  │          │
│  │  💼 Marketing Mgr │  │  💼 Software Dev  │  │  💼 Finance Anal│          │
│  │                  │  │                  │  │                  │          │
│  │  [👁 Preview]     │  │  [👁 Preview]     │  │  [👁 Preview]     │          │
│  │  [📄 Resume]      │  │  [📄 Resume]      │  │  [📄 Resume]      │          │
│  └──────────────────┘  └──────────────────┘  └──────────────────┘           │
│                                                                              │
│  [Load More ↓]                                                              │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Card: 320px width, bg white, border 1px #DEE2E6, radius 8px, shadow
- Photo: 80x80px, radius 50%, border 2px #E6F0FF
- Name: 18px/600 #003366
- Flag + nationality: 14px, inline
- University + major: 14px/400 #6C757D
- Languages: 12px, badges bg #E6F0FF
- Job intention: 14px/500 #FF6B35
- Actions: text buttons, 14px/500

#### Preview Modal
```
┌─────────────────────────────────────────────────────────────────────────────┐
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                                              [×]                     │   │
│  │                                                                     │   │
│  │  ┌────────────┐                                                    │   │
│  │  │            │    Name: Somchai Smith                              │   │
│  │  │   [Photo]  │    Nationality: 🇹🇭 Thailand                       │   │
│  │  │    120x120 │    Location: Wuhan, China                          │   │
│  │  │            │                                                    │   │
│  │  └────────────┘                                                    │   │
│  │                                                                     │   │
│  │  🎓 Education                                                       │   │
│  │  ├── Bachelor of Business Administration                           │   │
│  │  ├── Wuhan University, China                                        │   │
│  │  └── 2019-2023                                                      │   │
│  │                                                                     │   │
│  │  🗣️ Languages                                                       │   │
│  │  ├── Chinese: HSK Level 4 (Score: 210)                             │   │
│  │  ├── English: IELTS 6.0                                            │   │
│  │  └── Thai: Native                                                  │   │
│  │                                                                     │   │
│  │  💼 Work Experience                                                 │   │
│  │  ├── Marketing Intern @ ABC Company (2022)                         │   │
│  │  └── Sales Associate @ XYZ Corp (2023)                              │   │
│  │                                                                     │   │
│  │  🎯 Job Intention                                                   │   │
│  │  ├── Position: Marketing Manager                                    │   │
│  │  ├── Industry: E-commerce, Consumer Goods                           │   │
│  │  └── Location: Shanghai, Beijing                                   │   │
│  │                                                                     │   │
│  │  📄 Resume: [Download PDF] (link color #0066CC)                    │   │
│  │                                                                     │   │
│  │                           [📩 Contact]  [📅 Invite Interview]       │   │
│  │                           (buttons: 160x44px)                        │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Modal: 600px width, bg white, radius 12px, shadow-lg
- Overlay: black 50% opacity
- Close button: top right, 32px circle
- Section headers: 16px/600 #003366
- Content: 14px/400
- Buttons: primary #003366, secondary #FF6B35

---

## Module 5: Corporate Cooperation (企业合作)

### Visual Specifications

#### Page Hero
- Title: "Corporate Cooperation / 企业合作"
- Subtitle: "Partner with Us for Quality Talent Solutions"

#### Services Section (3 cards + custom)
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    Our Services for Enterprises / 企业服务                   │
│                    ─────────────────────────────────                         │
│                                                                              │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐             │
│  │     🎯          │  │     📚          │  │     👷          │             │
│  │                 │  │                 │  │                 │             │
│  │   Localized     │  │   Targeted      │  │   Labor         │             │
│  │   Recruitment   │  │   Training      │  │   Dispatch      │             │
│  │                 │  │                 │  │                 │             │
│  │   Connect with  │  │   Customized    │  │   Flexible      │             │
│  │   quality SE    │  │   programs to   │  │   staffing      │             │
│  │   Asian talent  │  │   meet your     │  │   solutions     │             │
│  │   pool          │  │   specific needs│  │                 │             │
│  │                 │  │                 │  │                 │             │
│  │  [Learn More →]│  │  [Learn More →]│  │  [Learn More →]│             │
│  │  (text: #FF6B35│  │  (text: #FF6B35)│  │  (text: #FF6B35)│             │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘             │
│                                                                              │
│  ┌─────────────────────────────────────────────────────────────────────┐  │
│  │                   Customized Talent Programs / 定制人才计划           │  │
│  │                                                                     │  │
│  │  We design recruitment solutions tailored to your industry          │  │
│  │  and company culture. From campus recruitment to executive search,   │  │
│  │  we've got you covered.                                            │  │
│  │                                                                     │  │
│  │                          [Contact Us →] (button #FF6B35)            │  │
│  └─────────────────────────────────────────────────────────────────────┘  │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Service cards: 320px width, bg white, border 1px #DEE2E6, radius 8px
- Icon: 64px, bg #E6F0FF, radius 12px, color #003366
- Title: 22px/600 #003366
- Description: 16px/400 #6C757D
- Custom section: bg #E6F0FF, padding 48px, centered text

#### Partner Logos
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        Our Partners / 合作伙伴                               │
│                        ─────────────────                                     │
│                                                                              │
│   ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐  ┌──────┐   │
│   │ Logo │  │ Logo │  │ Logo │  │ Logo │  │ Logo │  │ Logo │  │ Logo │   │
│   │  1   │  │  2   │  │  3   │  │  4   │  │  5   │  │  6   │  │  7   │   │
│   └──────┘  └──────┘  └──────┘  └──────┘  └──────┘  └──────┘  └──────┘   │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Logos: 120x60px each, grayscale by default, color on hover
- Background: white
- Hover: scale 1.05, shadow

#### Success Cases (2 columns)
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        Success Cases / 合作案例                              │
│                        ────────────────                                      │
│                                                                              │
│  ┌─────────────────────────────┐  ┌─────────────────────────────┐         │
│  │      [Company Photo]         │  │      [Company Photo]         │         │
│  │          400x200             │  │          400x200             │         │
│  │                              │  │                              │         │
│  │        Alibaba Group        │  │      Huawei Technologies     │         │
│  │        ─────────────────   │  │      ───────────────────     │         │
│  │                              │  │                              │         │
│  │  Positions: 50+             │  │  Positions: 30+              │         │
│  │  Students Placed: 120       │  │  Students Placed: 85         │         │
│  │  Success Rate: 95%          │  │  Success Rate: 92%            │         │
│  │                              │  │                              │         │
│  │  "Horizon helped us find    │  │  "Professional service with  │         │
│  │   exceptional talent from   │  │   thorough understanding of  │         │
│  │   SE Asia"                  │  │   our needs"                 │         │
│  └─────────────────────────────┘  └─────────────────────────────┘         │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Case cards: bg white, border 1px #DEE2E6, radius 8px
- Image: 400x200px, radius 8px top
- Company name: 24px/600 #003366
- Stats: 16px/500, green color
- Quote: 14px/400 italic, #6C757D

#### Enterprise Portal CTA
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                              │
│                     Enterprise Portal / 企业入口                              │
│                                                                              │
│  ┌──────────────────────────────────────────────────────────────────────┐  │
│  │                                                                      │  │
│  │    [Register as Enterprise]      [Login to Portal]                  │  │
│  │    (outlined button)              (filled button #003366)            │  │
│  │                                                                      │  │
│  │    Post jobs, view resumes, conduct interviews, all in one place     │  │
│  │                                                                      │  │
│  └──────────────────────────────────────────────────────────────────────┘  │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Background: #003366
- Buttons: 200x48px, radius 6px
- Register: bg white, text #003366
- Login: bg #FF6B35, text white

---

## Module 6: Seminar Center (研讨会中心)

### Visual Specifications

#### Page Hero
- Title: "Seminar Center / 研讨会中心"
- Subtitle: "Live Broadcasts, Webinars, and On-Demand Content"

#### Tab Navigation
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                              │
│   [📺 Live Seminars]    [📼 Playback]    [📅 Upcoming]                      │
│   (underline active)                                                   │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Tab bar: bg white, border-bottom 1px #DEE2E6
- Active tab: text #003366, underline 3px #003366
- Inactive: text #6C757D
- Tab padding: 16px 32px, font 16px/500

#### Live Seminar Section
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                              │
│   🔴 LIVE NOW                                                              │
│                                                                              │
│   ┌────────────────────────────────────────────────────────────────────┐   │
│   │                                                                     │   │
│   │  ┌──────────────────────────────────────────────────────────────┐  │   │
│   │  │                                                              │  │   │
│   │  │                                                              │  │   │
│   │  │                    [Video Player]                             │  │   │
│   │  │                       800x450                                 │  │   │
│   │  │                                                              │  │   │
│   │  │                                                              │  │   │
│   │  └──────────────────────────────────────────────────────────────┘  │   │
│   │                                                                     │   │
│   │  Topic: "How to Succeed in Chinese Universities"                  │   │
│   │  Speaker: Dr. Zhang Wei | Audience: 1,234 | 🔴 Live              │   │
│   │                                                                     │   │
│   │  ┌─────────────────┐    ┌─────────────────────────────────────┐  │   │
│   │  │ LIVE CHAT       │    │ Q&A                                 │  │   │
│   │  │ ─────────────── │    │ ─────────────────────────────────── │  │   │
│   │  │ [User1]: ...    │    │ Question: ...                      │  │   │
│   │  │ [User2]: ...    │    │ [Ask Question] [Submit]             │  │   │
│   │  │ [Type message]  │    │                                     │  │   │
│   │  │ [Send]          │    │                                     │  │   │
│   │  └─────────────────┘    └─────────────────────────────────────┘  │   │
│   │                                                                     │   │
│   │  [📺 Watch Now]  [🔗 Share]  [📝 Register for Replay]           │   │
│   │                                                                     │   │
│   └────────────────────────────────────────────────────────────────────┘   │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- LIVE badge: bg #DC3545, white text, 12px, radius 4px, pulse animation
- Video player: 16:9 ratio, bg black, radius 8px
- Chat panel: 280px width, bg #F8F9FA, border 1px #DEE2E6
- Q&A panel: 400px width, bg white, border 1px #DEE2E6
- Action buttons: 140x44px, bg #003366 (Watch), outlined (Share, Register)

#### Upcoming Seminars Cards (3 columns)
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    Upcoming Seminars / 即将开始的研讨会                      │
│                    ─────────────────────────────                           │
│                                                                              │
│   ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐          │
│   │  [Thumbnail]     │  │  [Thumbnail]     │  │  [Thumbnail]     │          │
│   │    280x160       │  │    280x160       │  │    280x160       │          │
│   │                 │  │                 │  │                 │          │
│   │  Apr 20, 2026   │  │  Apr 25, 2026   │  │  May 2, 2026    │          │
│   │  10:00 AM (BJT) │  │  2:00 PM (BJT)  │  │  9:00 AM (BJT)  │          │
│   │                 │  │                 │  │                 │          │
│   │  Chinese Univ   │  │  Job Market     │  │  Visa           │          │
│   │  Application    │  │  Trends         │  │  Application    │          │
│   │  Guide          │  │                 │  │                 │          │
│   │                 │  │                 │  │                 │          │
│   │  Speaker: Prof.Li│  │ Speaker: Mr.Wang│  │ Speaker: Ms.Chen│          │
│   │  Target: Students│  │ Target: Corporate│  │ Target: Both   │          │
│   │                 │  │                 │  │                 │          │
│   │  [🔔 Register]   │  │  [🔔 Register]   │  │  [🔔 Register]   │          │
│   └─────────────────┘  └─────────────────┘  └─────────────────┘          │
│                                                                              │
│   You'll receive a reminder 15 minutes before start                        │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Card: bg white, border 1px #DEE2E6, radius 8px
- Thumbnail: 280x160px, radius 8px top
- Date/time: 14px/600 #003366
- Title: 18px/600 #003366
- Speaker: 14px/400 #6C757D
- Target badge: bg #E6F0FF, text #003366, 12px, radius 12px
- Register button: 140x40px, bg #FF6B35, white text, radius 6px

#### Playback Section
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        Past Seminars / 往期回放                              │
│                        ─────────────────                                    │
│                                                                              │
│   [Filter: All Categories ▼]  [Search 🔍]                                  │
│                                                                              │
│   ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐          │
│   │  [Thumbnail]     │  │  [Thumbnail]     │  │  [Thumbnail]     │          │
│   │    ▶ Play        │  │    ▶ Play        │  │    ▶ Play        │          │
│   │    280x160       │  │    280x160       │  │    280x160       │          │
│   │                 │  │                 │  │                 │          │
│   │  Title: ...     │  │  Title: ...     │  │  Title: ...     │          │
│   │                 │  │                 │  │                 │          │
│   │  Views: 5,234   │  │  Views: 3,456   │  │  Views: 2,123   │          │
│   │  Duration: 1h30m │  │  Duration: 2h   │  │  Duration: 1h45m│          │
│   │                 │  │                 │  │                 │          │
│   │  [▶] [0.5x][1x] │  │                 │  │                 │          │
│   │     [2x]        │  │                 │  │                 │          │
│   └─────────────────┘  └─────────────────┘  └─────────────────┘          │
│                                                                              │
│   [Load More ↓]                                                            │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Play icon overlay on thumbnail: 48px circle, bg white/80%
- View count: 14px, eye icon
- Duration: 14px, clock icon
- Speed controls: 12px buttons, outline style

---

## Module 7: News (新闻)

### Visual Specifications

#### Page Hero
- Title: "News & Insights / 新闻资讯"
- Subtitle: "Stay Updated with Our Latest News"

#### Category Tabs
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                              │
│   [All]  [Company News]  [Industry News]  [Study Abroad]  [Recruitment]     │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Tab style: same as Seminar Center tabs
- Active: underline 3px #003366
- Scrollable on mobile

#### Featured Article
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                              │
│   ┌──────────────────────────────────┐  ┌──────────────────────────────────┐ │
│   │                                   │  │  [Company News]                  │ │
│   │                                   │  │                                   │ │
│   │                                   │  │  Article Title Goes Here         │ │
│   │        [Featured Image]           │  │  ────────────────────────────    │ │
│   │            600x400                │  │                                   │ │
│   │                                   │  │  Summary text goes here and      │ │
│   │                                   │  │  continues for a couple lines    │ │
│   │                                   │  │  with the full story preview...  │ │
│   │                                   │  │                                   │ │
│   │                                   │  │  Apr 15, 2026 | 5 min read        │ │
│   │                                   │  │                                   │ │
│   │                                   │  │              [Read More →]       │ │
│   └──────────────────────────────────┘  └──────────────────────────────────┘ │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Featured section: bg #E6F0FF, padding 48px
- Image: 600x400px, radius 8px
- Text side: 400px width
- Category tag: bg #003366, white text, 12px, radius 12px
- Title: 28px/600 #003366
- Summary: 16px/400 #6C757D
- Meta: 14px/400 #6C757D

#### News Grid (3 columns)
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                              │
│   ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐          │
│   │  [Image]          │  │  [Image]          │  │  [Image]          │          │
│   │    320x200        │  │    320x200        │  │    320x200        │          │
│   │                   │  │                   │  │                   │          │
│   │  [Company News]   │  │  [Industry News]  │  │  [Study Abroad]   │          │
│   │  (tag)            │  │  (tag)            │  │  (tag)            │          │
│   │                   │  │                   │  │                   │          │
│   │  Title of the     │  │  Title of the     │  │  Title of the     │          │
│   │  article goes     │  │  article goes     │  │  article goes     │          │
│   │  here and can     │  │  here...         │  │  here...         │          │
│   │  span two lines   │  │                   │  │                   │          │
│   │                   │  │                   │  │                   │          │
│   │  Apr 12, 2026     │  │  Apr 10, 2026     │  │  Apr 8, 2026      │          │
│   └──────────────────┘  └──────────────────┘  └──────────────────┘          │
│                                                                              │
│   ┌──────────────────┐  ┌──────────────────┐  ┌──────────────────┐          │
│   │  [Image]          │  │  [Image]          │  │  [Image]          │          │
│   │    ...            │  │    ...            │  │    ...            │          │
│   └──────────────────┘  └──────────────────┘  └──────────────────┘          │
│                                                                              │
│   [Load More ↓]                                                            │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Card: bg white, border 1px #DEE2E6, radius 8px, shadow
- Image: 320x200px, radius 8px top
- Category tag: same as above
- Title: 18px/600 #003366
- Date: 14px/400 #6C757D
- Hover: translateY(-4px), shadow increase

#### Newsletter Subscription
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                              │
│              Subscribe to Our Newsletter / 订阅我们的新闻通讯                 │
│              ────────────────────────────────────────                       │
│                                                                              │
│              ┌────────────────────────────────────────┐  ┌──────────────┐  │
│              │  Enter your email address              │  │ [Subscribe →]│  │
│              │  (input 300x48px)                      │  │ (bg #FF6B35)│  │
│              └────────────────────────────────────────┘  └──────────────┘  │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Section bg: #003366
- Text: white, 20px/500
- Input: 300x48px, white bg, radius 6px
- Button: 140x48px, bg #FF6B35, white text

---

## Module 8: Contact Us (联系我们)

### Visual Specifications

#### Page Hero
- Title: "Contact Us / 联系我们"
- Subtitle: "We're Here to Help"

#### Contact Info + Form Layout (2 columns)
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                                                                              │
│  ┌─────────────────────────────┐  ┌──────────────────────────────────────┐ │
│  │                             │  │  Send Us a Message                    │ │
│  │  CONTACT INFORMATION        │  │  ────────────────────────────────    │ │
│  │  ───────────────────        │  │                                      │ │
│  │                             │  │  Name: [_____________________]       │ │
│  │  📍 Address:                │  │                                      │ │
│  │  [Office Address]           │  │  Email: [_____________________]     │ │
│  │  [City, Country]            │  │                                      │ │
│  │                             │  │  Phone: [_____________________]     │ │
│  │  📞 Phone: [Number]         │  │                                      │ │
│  │                             │  │  Subject: [_____________________]   │ │
│  │  ✉️ Email: [Email]          │  │                                      │ │
│  │                             │  │  Message: [______________________]  │ │
│  │  💬 WeChat: [ID]            │  │              [______________________]  │ │
│  │                             │  │              [______________________]  │ │
│  │  Social Media:              │  │                                      │ │
│  │  [FB] [LI] [WA] [Line]      │  │              [Send Message →]         │ │
│  │  (32px icons)              │  │                                      │ │
│  │                             │  └──────────────────────────────────────┘ │
│  │  ─────────────────────────   │                                           │
│  │                             │                                           │
│  │  [Map Embed]                │                                           │
│  │  ┌───────────────────────┐  │                                           │
│  │  │                       │  │                                           │
│  │  │    Google Maps        │  │                                           │
│  │  │                       │  │                                           │
│  │  └───────────────────────┘  │                                           │
│  │                             │                                           │
│  └─────────────────────────────┘                                           │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Left column: 400px width
- Right column: 600px width
- Contact info icons: 24px, #003366
- Social icons: 32px, bg #E6F0FF, radius 50%, hover #003366
- Map: 400x300px, radius 8px
- Form inputs: 100% width, height 48px, border 1px #DEE2E6
- Textarea: 120px height
- Send button: 180x48px, bg #003366, white text, radius 6px

#### Regional Offices Grid
```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        Our Offices / 我们的办事处                             │
│                        ───────────────                                      │
│                                                                              │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐             │
│  │  🏢 China HQ     │  │  🏢 Thailand    │  │  🏢 Indonesia   │             │
│  │                 │  │                 │  │                 │             │
│  │  Wuhan, Hubei   │  │  Bangkok        │  │  Jakarta        │             │
│  │  ─────────────  │  │  ─────────────  │  │  ─────────────  │             │
│  │  [Address]      │  │  [Address]      │  │  [Address]      │             │
│  │  [Phone]        │  │  [Phone]        │  │  [Phone]        │             │
│  │  [Email]        │  │  [Email]        │  │  [Email]        │             │
│  │                 │  │                 │  │                 │             │
│  │  [Get Directions]│  │ [Get Directions]│  │ [Get Directions]│             │
│  └─────────────────┘  └─────────────────┘  └─────────────────┘             │
│                                                                              │
│  ┌─────────────────┐  ┌─────────────────┐                                   │
│  │  🏢 Vietnam     │  │  🏢 Malaysia    │                                   │
│  │                 │  │                 │                                   │
│  │  Ho Chi Minh    │  │  Kuala Lumpur   │                                   │
│  │  ─────────────  │  │  ─────────────  │                                   │
│  │  [Address]      │  │  [Address]      │                                   │
│  │  [Phone]        │  │  [Phone]        │                                   │
│  │  [Email]        │  │  [Email]        │                                   │
│  │                 │  │                 │                                   │
│  │  [Get Directions]│  │ [Get Directions]│                                   │
│  └─────────────────┘  └─────────────────┘                                   │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```
- Office cards: 280px width, bg white, border 1px #DEE2E6, radius 8px
- Office icon: 48px, bg #E6F0FF, radius 50%
- City: 20px/600 #003366
- Address/phone/email: 14px/400 #6C757D
- CTA: text link #FF6B35

---

## Common Components Summary

### Buttons
| Type | Background | Text | Border | Use |
|------|-----------|------|--------|-----|
| Primary | #003366 | white | none | Main actions |
| CTA | #FF6B35 | white | none | Call-to-action |
| Secondary | white | #003366 | #003366 1px | Secondary actions |
| Ghost | transparent | #003366 | none | Tertiary actions |

### Cards
- Background: white
- Border: 1px solid #DEE2E6
- Border-radius: 8px
- Shadow: `0 2px 8px rgba(0,51,102,0.08)`
- Padding: 24px

### Navigation
- Desktop: 72px height, sticky
- Mobile: 56px height, hamburger menu
- Active link: underline or bold

### Forms
- Input height: 48px (desktop), 44px (mobile)
- Border radius: 6px
- Focus: 2px border #003366
- Error: border #DC3545, error text below

### Responsive Breakpoints
| Name | Width | Layout |
|------|-------|--------|
| Mobile | < 576px | Single column |
| Tablet | 576-768px | 2 columns |
| Desktop | 768-1024px | 3-4 columns |
| Large Desktop | > 1024px | Full layout |

---

**Design Files Location:** `/home/zongbao/Projects/hr-website/DESIGN/`

**Export Specifications:**
- PNG screenshots for each module (PC + Mobile)
- PDF documentation of design system
- Figma link (if available)

**Designer:** ยู (u)
**PM Review:** โปร (Pro)
**Status:** Ready for Implementation ✓

---

_Visual Mockups completed based on wireframes and design system_
_All 8 modules designed with PC + Mobile responsive layouts_