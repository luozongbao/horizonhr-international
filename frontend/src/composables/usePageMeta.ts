import { useHead } from '@unhead/vue'
import { computed, toValue, type MaybeRefOrGetter } from 'vue'
import { useSettingsStore } from '@/stores/settings'

interface PageMetaOptions {
  title: MaybeRefOrGetter<string>
  description?: MaybeRefOrGetter<string | undefined>
  image?: MaybeRefOrGetter<string | undefined>
  type?: MaybeRefOrGetter<string>
}

export function usePageMeta(options: PageMetaOptions) {
  const settings = useSettingsStore()

  const siteName = computed(() => settings.config?.site_name ?? 'Horizon International')
  const title = computed(() => {
    const t = toValue(options.title)
    return t ? `${t} | ${siteName.value}` : siteName.value
  })
  const description = computed(() => toValue(options.description) ?? '')
  const image = computed(() => toValue(options.image) ?? '')
  const type = computed(() => toValue(options.type) ?? 'website')

  useHead({
    title,
    meta: [
      { name: 'description', content: description },
      { property: 'og:title', content: title },
      { property: 'og:description', content: description },
      { property: 'og:image', content: image },
      { property: 'og:type', content: type },
      { name: 'twitter:card', content: 'summary_large_image' },
      { name: 'twitter:title', content: title },
      { name: 'twitter:description', content: description },
    ],
  })
}
