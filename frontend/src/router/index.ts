import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  scrollBehavior: () => ({ top: 0 }),
  routes: [
    // ─── Public ────────────────────────────────────────────────────────────
    {
      path: '/',
      component: () => import('@/views/public/HomeView.vue'),
      meta: { layout: 'public' },
    },
    {
      path: '/about',
      component: () => import('@/views/public/AboutView.vue'),
      meta: { layout: 'public' },
    },
    {
      path: '/study',
      component: () => import('@/views/public/StudyView.vue'),
      meta: { layout: 'public' },
    },
    {
      path: '/talent',
      component: () => import('@/views/public/TalentPoolView.vue'),
      meta: { layout: 'public' },
    },
    {
      path: '/corporate',
      component: () => import('@/views/public/CorporateView.vue'),
      meta: { layout: 'public' },
    },
    {
      path: '/seminars',
      component: () => import('@/views/public/SeminarsView.vue'),
      meta: { layout: 'public' },
    },
    {
      path: '/seminars/:id',
      component: () => import('@/views/public/SeminarDetailView.vue'),
      meta: { layout: 'public' },
    },
    {
      path: '/news',
      component: () => import('@/views/public/NewsView.vue'),
      meta: { layout: 'public' },
    },
    {
      path: '/news/:slug',
      component: () => import('@/views/public/NewsDetailView.vue'),
      meta: { layout: 'public' },
    },
    {
      path: '/contact',
      component: () => import('@/views/public/ContactView.vue'),
      meta: { layout: 'public' },
    },
    {
      path: '/pages/:slug',
      component: () => import('@/views/public/CmsPageView.vue'),
      meta: { layout: 'public' },
    },

    // ─── Auth ──────────────────────────────────────────────────────────────
    {
      path: '/login',
      component: () => import('@/views/auth/LoginView.vue'),
      meta: { layout: 'auth', guestOnly: true },
    },
    {
      path: '/register/student',
      component: () => import('@/views/auth/RegisterStudentView.vue'),
      meta: { layout: 'auth', guestOnly: true },
    },
    {
      path: '/register/enterprise',
      component: () => import('@/views/auth/RegisterEnterpriseView.vue'),
      meta: { layout: 'auth', guestOnly: true },
    },
    {
      path: '/password/forgot',
      component: () => import('@/views/auth/ForgotPasswordView.vue'),
      meta: { layout: 'auth' },
    },
    {
      path: '/email/confirmed',
      component: () => import('@/views/auth/EmailConfirmedView.vue'),
      meta: { layout: 'auth' },
    },

    // ─── Student Portal ────────────────────────────────────────────────────
    {
      path: '/student',
      redirect: '/student/dashboard',
      meta: { requiresAuth: true, role: 'student' },
      children: [
        {
          path: 'dashboard',
          component: () => import('@/views/student/DashboardView.vue'),
          meta: { layout: 'student', requiresAuth: true, role: 'student' },
        },
        {
          path: 'profile',
          component: () => import('@/views/student/ProfileView.vue'),
          meta: { layout: 'student', requiresAuth: true, role: 'student' },
        },
        {
          path: 'resume',
          component: () => import('@/views/student/ResumeView.vue'),
          meta: { layout: 'student', requiresAuth: true, role: 'student' },
        },
        {
          path: 'applications',
          component: () => import('@/views/student/ApplicationsView.vue'),
          meta: { layout: 'student', requiresAuth: true, role: 'student' },
        },
        {
          path: 'interviews',
          component: () => import('@/views/student/InterviewsView.vue'),
          meta: { layout: 'student', requiresAuth: true, role: 'student' },
        },
        {
          path: 'seminars',
          component: () => import('@/views/student/SeminarsView.vue'),
          meta: { layout: 'student', requiresAuth: true, role: 'student' },
        },
      ],
    },

    // ─── Enterprise Portal ─────────────────────────────────────────────────
    {
      path: '/enterprise',
      redirect: '/enterprise/dashboard',
      meta: { requiresAuth: true, role: 'enterprise' },
      children: [
        {
          path: 'dashboard',
          component: () => import('@/views/enterprise/DashboardView.vue'),
          meta: { layout: 'enterprise', requiresAuth: true, role: 'enterprise' },
        },
        {
          path: 'profile',
          component: () => import('@/views/enterprise/ProfileView.vue'),
          meta: { layout: 'enterprise', requiresAuth: true, role: 'enterprise' },
        },
        {
          path: 'jobs',
          component: () => import('@/views/enterprise/JobsView.vue'),
          meta: { layout: 'enterprise', requiresAuth: true, role: 'enterprise' },
        },
        {
          path: 'talent',
          component: () => import('@/views/enterprise/TalentView.vue'),
          meta: { layout: 'enterprise', requiresAuth: true, role: 'enterprise' },
        },
        {
          path: 'interviews',
          component: () => import('@/views/enterprise/InterviewsView.vue'),
          meta: { layout: 'enterprise', requiresAuth: true, role: 'enterprise' },
        },
      ],
    },

    // ─── Admin Panel ───────────────────────────────────────────────────────
    {
      path: '/admin',
      redirect: '/admin/dashboard',
      meta: { requiresAuth: true, role: 'admin' },
      children: [
        {
          path: 'dashboard',
          component: () => import('@/views/admin/DashboardView.vue'),
          meta: { layout: 'admin', requiresAuth: true, role: 'admin' },
        },
        {
          path: 'users',
          component: () => import('@/views/admin/UsersView.vue'),
          meta: { layout: 'admin', requiresAuth: true, role: 'admin' },
        },
        {
          path: 'resumes',
          component: () => import('@/views/admin/ResumesView.vue'),
          meta: { layout: 'admin', requiresAuth: true, role: 'admin' },
        },
        {
          path: 'interviews',
          component: () => import('@/views/admin/InterviewsView.vue'),
          meta: { layout: 'admin', requiresAuth: true, role: 'admin' },
        },
        {
          path: 'seminars',
          component: () => import('@/views/admin/SeminarsView.vue'),
          meta: { layout: 'admin', requiresAuth: true, role: 'admin' },
        },
        {
          path: 'news',
          component: () => import('@/views/admin/AnnouncementsView.vue'),
          meta: { layout: 'admin', requiresAuth: true, role: 'admin' },
        },
        {
          path: 'pages',
          component: () => import('@/views/admin/CmsPagesView.vue'),
          meta: { layout: 'admin', requiresAuth: true, role: 'admin' },
        },
        {
          path: 'posts',
          component: () => import('@/views/admin/CmsPostsView.vue'),
          meta: { layout: 'admin', requiresAuth: true, role: 'admin' },
        },
        {
          path: 'contacts',
          component: () => import('@/views/admin/ContactsView.vue'),
          meta: { layout: 'admin', requiresAuth: true, role: 'admin' },
        },
        {
          path: 'settings',
          component: () => import('@/views/admin/SettingsView.vue'),
          meta: { layout: 'admin', requiresAuth: true, role: 'admin' },
        },
        {
          path: 'languages',
          component: () => import('@/views/admin/LanguagesView.vue'),
          meta: { layout: 'admin', requiresAuth: true, role: 'admin' },
        },
      ],
    },

    // ─── Fallback ──────────────────────────────────────────────────────────
    {
      path: '/:pathMatch(.*)*',
      component: () => import('@/views/NotFoundView.vue'),
      meta: { layout: 'public' },
    },
  ],
})

// Navigation guard
router.beforeEach(async (to) => {
  const auth = useAuthStore()

  // Initialise auth state once on first navigation
  if (!auth.initialised) {
    await auth.init()
  }

  if (to.meta.requiresAuth && !auth.isLoggedIn) {
    return { path: '/login', query: { redirect: to.fullPath } }
  }

  if (to.meta.guestOnly && auth.isLoggedIn) {
    return auth.redirectForRole()
  }

  if (to.meta.role && auth.user?.role !== to.meta.role) {
    return auth.redirectForRole()
  }
})

export default router
