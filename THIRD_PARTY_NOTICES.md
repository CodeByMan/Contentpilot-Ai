# Third-Party Notices

The root [`LICENSE`](LICENSE) applies to project-owned ContentPilot AI source code, documentation, CSS, JavaScript, SVG branding, and placeholder artwork included in this repository.

## Public interface assets

No commercial theme bundle is included in this public distribution.

The following former distribution paths are intentionally absent:

- `public/frontend/`
- `public/backend/`

Therefore, the repository does not distribute the Softnio ContentPilot theme, Softnio NioBoard theme, Nioicon font, ThemeForest demo images or illustrations, bundled TinyMCE/Choices assets, or their compiled JavaScript bundles.

Isotope is not included. No Isotope source, minified build, packaged marker, font, or dependent bundle is shipped by the application.

The active interface assets are project-owned:

- `resources/css/contentpilot.css`
- `resources/js/contentpilot.js`
- `public/assets/css/contentpilot.css` — generated copy
- `public/assets/js/contentpilot.js` — generated copy
- `public/assets/images/contentpilot-logo.svg`
- `public/assets/images/contentpilot-icon.svg`
- `public/assets/images/avatar-placeholder.svg`

They use system fonts and text-based interface symbols; no third-party font or icon package is embedded.

## Composer dependencies

Composer packages are not committed under `vendor/`. They retain their upstream licences as recorded in `composer.lock` and installed package metadata. Major direct or runtime-relevant packages include:

| Package | Locked version | Declared licence |
|---|---:|---|
| `laravel/framework` | 12.53.0 | MIT |
| `barryvdh/laravel-dompdf` | 3.1.2 | MIT |
| `dompdf/dompdf` | 3.1.5 | LGPL-2.1 |
| `intervention/image` | 3.11.7 | MIT |
| `openai-php/laravel` | 0.19.0 | MIT |
| `laravel/tinker` | 2.11.1 | MIT |
| `pestphp/pest` | 3.8.5 | MIT |
| `laravel/pint` | 1.27.1 | MIT |

This summary does not replace the complete notices supplied by Composer packages. Review `composer.lock`, installed package metadata, and upstream licence files when preparing a binary or hosted distribution.

## Laravel framework files

Standard Laravel bootstrap/public entry files remain subject to Laravel's MIT licence. Project modifications and project-specific application code remain covered by the repository MIT licence.

## Automated guard

Run:

```bash
npm run check:licenses
```

The check fails if `public/frontend/` or `public/backend/` returns, if a layout references removed bundles, or if public/resource text contains recognised Softnio, NioBoard, Nioicon, or Isotope distribution markers.
