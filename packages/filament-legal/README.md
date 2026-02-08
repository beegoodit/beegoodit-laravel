# Filament Legal

Centralized legal compliance for Filament applications. This package provides versioned policy management (Privacy Policy, Imprint, Cookie Policy) with a robust audit trail and a unified "Legal Gate" enforcement middleware.

## Features

- **Polymorphic Ownership**: Legal documents (Policies and Identities) can be owned by the platform (default) or by specific entities like Teams.
- **Legal Identities**: Manage official contact and registration details (GmbH, e.V., etc.) for the platform or specific owners.
- **Dynamic Documents**: Fetch owner-specific imprint or privacy policies with automatic fallbacks to platform defaults.
- **Unified Legal Gate**: Middleware that enforces policy acceptance across different owners.
- **Filament Integration**: Pre-built resources for managing legal data in any Filament panel.
- **Plug-and-Play**: Designed to be dropped into any project in the `beegoodit` ecosystem to ensure consistent legal compliance.

## Installation

```bash
composer require beegoodit/filament-legal
```

## Workflow: The Unified Gate

1.  **Publish**: An administrator publishes a new version of the Privacy Policy (e.g., v2.0) via the `LegalResource`.
2.  **Intercept**: The `EnsureLegalAcceptance` middleware detects that users have not yet signed this specific version.
3.  **Acceptance**: Users are redirected to a dedicated acceptance screen.
4.  **Audit**: Upon clicking "Accept", their digital signature (IP, User Agent) is stored in the `policy_acceptances` table.
5.  **Access**: The gate opens, and the user is redirected back to their intended destination.

---

Created as part of the BeeGoodIT Shared Package Ecosystem.
