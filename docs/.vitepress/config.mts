import taskLists from "markdown-it-task-lists";
import defineVersionedConfig from "vitepress-versioning-plugin";
import {withMermaid} from "vitepress-plugin-mermaid";

const BASE_PATH = '/'

// https://vitepress.dev/reference/site-config
export default withMermaid(defineVersionedConfig({
  title: "Beacon Metrics",
  description: "Simple Metrics for Laravel",
  base: BASE_PATH,
  versioning: {
    latestVersion: 'dev',
  },
  head: [
    [
      'meta',
      { name: 'author', content: 'Davey Shafik' }
    ],
    [
      'meta',
      { name:"twitter:image", content: BASE_PATH + "assets/images/social.png" }
    ],
    [
      'meta',
      { name:"og:image", content: BASE_PATH +  "assets/images/social.png" }
    ],
    [
      'link',
      { rel: 'preconnect', href: 'https://fonts.googleapis.com' }
    ],
    [
      'link',
      { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' }
    ],
    [
      'link',
      { href: 'https://fonts.googleapis.com/css2?family=Source+Code+Pro:ital,wght@0,200..900;1,200..900&family=Source+Sans+3:ital,wght@0,200..900;1,200..900&display=swap', rel: 'stylesheet' }
    ],
  ],
  themeConfig: {
    logo: '/assets/images/icon.png',
    search: {
      provider: 'local',
      options: {
        locales: {
          "root": {
             translations: {
               button: {
                 buttonText: "Search latest version…"
               }
             }
          }
        },
        async _render(src, env, md) {
          const html = md.render(src, env)
          if (env.frontmatter?.search === false) return ''
          if (env.relativePath.match(/\d+\.(\d+|x)/) !== null) return ''
          return html
        }
      },
    },
    // https://vitepress.dev/reference/default-theme-config
    nav: [
      { text: 'Home', link: './' },
      { text: 'Documentation', link: './install' },
      {
        component: 'VersionSwitcher',
      }
    ],

    sidebar: {
      "/": [
        {
          "text": "Get Started",
          "items": [
            {"text": "Installation", "link": "/install"},
            {"text": "Basic Usage", "link": "/basic-usage"},
          ]
        },
        {
          "text": "Base Query",
          collapsed: true,
            "items": [
                {"text": "Query Builders", "link": "/query-builders"},
                {"text": "Model Trait", "link": "/trait"},
            ]
        },
        {
          "text": "Aggregates",
          collapsed: true,
          "items": [
            {"text": "Count", "link": "/count"},
            {"text": "Sum", "link": "/sum"},
            {"text": "Average", "link": "/average"},
            {"text": "Min", "link": "/min"},
            {"text": "Max", "link": "/max"}
          ]
        },
        {
          "text": "Intervals",
          collapsed: true,
          "items": [
            {"text": "By Second", "link": "/by-second"},
            {"text": "By Minute", "link": "/by-minute"},
            {"text": "By Hour", "link": "/by-hour"},
            {"text": "By Day", "link": "/by-day"},
            {"text": "By Day Name", "link": "/by-day-name"},
            {"text": "By Week", "link": "/by-week"},
            {"text": "By Month", "link": "/by-month"},
            {"text": "By Year", "link": "/by-year"},
          ]
        },
        {
          "text": "Date Ranges",
          collapsed: true,
          "items": [
              {"text": "Between", "link": "/between"},
              {"text": "From", "link": "/from"},
              {"text": "Period", "link": "/period"},
          ]
        },
        {
          "text": "Metrics",
          collapsed: true,
          "items": [
              {"text": "Value Metrics", "link": "/value-metrics"},
              {"text": "Trend Metrics", "link": "/trend-metrics"},
          ]
        }
      ]
    },

    footer: {
      message: "Made with 🦁💖🏳️‍🌈 by <a href=\"https://www.daveyshafik.com\">Davey Shafik</a>.",
      copyright: "Released under the MIT License. Copyright © 2025 Davey Shafik.",
    },

    socialLinks: [
      { icon: 'github', link: 'https://github.com/beacon-hq/metrics' }
    ],

    versionSwitcher: false,
  },
  markdown: {
    theme: {
      dark: 'monokai',
      light: 'github-light'
    },

    config: md => {
        md.use(taskLists)
    }
  },
}, __dirname))
