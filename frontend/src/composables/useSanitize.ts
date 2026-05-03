/**
 * useSanitize — safely sanitize HTML strings before rendering with v-html.
 * Uses DOMPurify to prevent XSS attacks.
 */
import DOMPurify from 'dompurify'

export function useSanitize() {
  function sanitize(html: string | null | undefined): string {
    if (!html) return ''
    return DOMPurify.sanitize(html, {
      ALLOWED_TAGS: [
        'p', 'br', 'strong', 'em', 'b', 'i', 'u', 's',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li', 'blockquote', 'pre', 'code',
        'a', 'img', 'span', 'div', 'table', 'thead', 'tbody', 'tr', 'th', 'td',
        'hr',
      ],
      ALLOWED_ATTR: ['href', 'src', 'alt', 'title', 'target', 'rel', 'class', 'style'],
    })
  }

  return { sanitize }
}
