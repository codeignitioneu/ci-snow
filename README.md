# Seasonal Snow by Code Ignition

A lightweight WordPress plugin that adds a festive falling effect to your site: snowflakes, colored Christmas baubles, tiny gifts, soft snow dots and your own images from the Media Library.

Everything is configured from **Settings → Seasonal Snow** — no code needed.

## Features

- Built-in shapes drawn on a canvas (no image downloads): snowflakes, Christmas baubles, tiny gifts, soft dots
- Your own images from the Media Library (up to 20, transparent PNG/SVG recommended)
- Colors for snowflakes, outline (so flakes are visible on white backgrounds), baubles and gifts
- Separate amounts for desktop and phones, or turn it off on phones
- Show on the homepage only, the whole site, or selected pages
- Optional start and end date
- Never blocks clicks (`pointer-events: none`), pauses when the tab is hidden, and is skipped for visitors who prefer reduced motion
- Live preview on the settings page
- Updates from GitHub Releases directly on the Plugins page

## Installation

1. Download `ci-snow.zip` from the latest [release](https://github.com/codeignitioneu/ci-snow/releases).
2. WordPress → Plugins → Add New → Upload Plugin → choose the zip → Install → Activate.
3. Go to Settings → Seasonal Snow.

## Releasing an update

1. Bump `Version` in `ci-snow.php` and `CI_SNOW_VERSION`.
2. Push to `main`.
3. Create a GitHub release tagged `vX.Y.Z` and attach `ci-snow.zip` (the plugin folder zipped as `ci-snow/`).

## License

GPL-2.0-or-later © Code Ignition SRL
