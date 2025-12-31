# Filament Legal

Centralized legal compliance for Filament applications. This package provides versioned policy management (Privacy Policy, Imprint, Cookie Policy) with a robust audit trail and a unified "Legal Gate" enforcement middleware.

## Features

- **Filament Management**: A dedicated `LegalResource` to manage and version policies directly from your Admin panel.
- **Unified Legal Gate**: Middleware that intercepts users immediately after login/registration if they haven't accepted the latest policy.
- **Robust Audit Trail**: Detailed tracking of policy acceptances, including User ID, Policy Version, IP Address, and User Agent.
- **Multi-language Support**: Native integration with Filament's translation features for managing policies in multiple languages.
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
