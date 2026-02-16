# Plan updates: Complete app build guide

Use this when implementing BUILDING-AN-APP.md. It records the agreed updates to the plan.

## Confirmed choices

- **Guide file**: `BUILDING-AN-APP.md` in the repo root (linked from README).
- **Prerequisites**: **PHP 8.4+**, **Laravel 12+**, **Filament 4+** (and Composer, Node for Filament/Vite).

## Optimizing for AI

Structure BUILDING-AN-APP.md so that an AI assistant can parse it, follow steps in order, and avoid hallucinating:

1. **Version block at top**  
   Start the guide with an explicit prerequisites block (e.g. table or list): PHP 8.4+, Laravel 12+, Filament 4+. Single place for the AI to check stack version before suggesting commands or code.

2. **Strict heading hierarchy**  
   One H1 (title), H2 for major phases (0–6), H3 for each package or pattern. Same depth everywhere so an AI can jump to a section or package reliably.

3. **Per-package template**  
   For every package use the same subsections: **Requires** (list beegoodit deps), **Install** (exact `composer require`), **Publish** (exact `php artisan vendor:publish --tag=...`), **Configure** (file path + snippet), **Model/Panel** (trait + registration). Same order every time so the AI knows where to find the next step.

4. **Explicit commands only**  
   Every CLI command in a fenced code block with language (`bash` or `php`). No “run the appropriate publish command” without the exact tag; no “configure as needed” without the exact config key or file snippet.

5. **Dependency callouts**  
   At the start of each package section state “Requires: X, Y (see section N).” so an AI does not suggest installing out of order.

6. **Checklist as parseable list**  
   End with a numbered or checkbox list that mirrors the section order (e.g. `- [ ] 0. Prerequisites`). An AI can use it to infer what is done vs what comes next.

7. **Single source for “more”**  
   For each package link once to `packages/<name>/README.md` for full options. Avoid duplicating long docs so the guide stays the single linear path and the AI is directed to the README for edge cases.

Apply these rules when writing BUILDING-AN-APP.md so both humans and AIs can follow the guide step-by-step without guessing.
