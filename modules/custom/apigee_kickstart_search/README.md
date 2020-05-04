# Introduction
The `apigee_kickstart_search` module Adds a search page for searching content and APIs.

## Installation

1. Visit **Extend --> Install new module** from the admin toolbar.
2. Enable the `Apigee Kickstart Search` module.
3. Visit `/admin/reports/status` and click on **Run cron** to re-index content.

## Customization

- To add new entity types to the search index or to configure fields *boost*, see the configuration form at `/admin/config/search/search-api/index/default/edit`.
- To customize the search results, see the view at `admin/structure/views/view/apigee_kickstart_search`.

### Note

When enabled, the `apigee_kickstart_search` module redirects all search queries from the search form in the header to the `/search`.
