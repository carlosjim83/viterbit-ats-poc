# ADR 008: Symfony UX Live Components + Stimulus for Interactive Filters

## Status
Accepted

## Context
The Applications list page (`/applications`) provides filters (status, position, search) implemented as a traditional HTML form with full page reload. This results in:
- Slower user experience due to full page re-rendering.
- Loss of scroll position and focus state.
- Unnecessary server load re-rendering the entire layout.

We need a lightweight, maintainable way to make the filter interaction reactive without building a separate SPA or API.

## Decision
Adopt **Symfony UX Live Components** (`symfony/ux-live-component`) backed by **Stimulus** (`symfony/stimulus-bundle`) to render the Applications list as a self-contained Twig component that re-renders automatically when filter inputs change.

### Rationale
- **Server-side rendering**: Logic stays in PHP/Twig; no API endpoints needed.
- **Minimal JavaScript**: Stimulus provides just enough interactivity without taking over the frontend.
- **Ecosystem alignment**: Both packages are official Symfony UX initiatives, well-supported and documented.
- **Progressive enhancement**: Works without JavaScript (degrades to standard form submission).

## Alternatives Considered

| Alternative | Pros | Cons |
|-------------|------|------|
| HTMX on plain form | Very small JS footprint | Manual attribute wiring, no reusable component model |
| React/Vue SPA | Rich interactivity | Heavy JS bundle, separate build pipeline, overkill for a PoC |
| Turbo Frames + Stimulus | Good UX | Still requires manual wiring of state and partial updates |

## Consequences
- **Positive**: Faster UX, reduced server rendering load, reusable component pattern.
- **Negative**: Additional Composer (`symfony/ux-live-component`) and npm-like (AssetMapper) dependencies.
- **Neutral**: Requires AssetMapper for JS asset management; this is already configured in the project.

## Implementation Notes
- Create a `ApplicationSearch` live component under `src/Twig/Components/`.
- Expose filter values as writable `#[LiveProp]` properties.
- Use `data-model="propName"` in the component Twig template to bind inputs.
- The existing `ListApplicationsHandler` and `ListApplications` query remain unchanged; the component calls them internally.

## References
- [Symfony UX Live Component docs](https://symfony.com/bundles/ux-live-component/current/index.html)
- [Symfony Stimulus Bundle docs](https://symfony.com/bundles/stimulus-bundle/current/index.html)
- Context7 queries: `/symfony/ux-live-component`, `/symfony/stimulus-bundle`
