# Tests TODO

## Current Status: 8/44 tests passing (18%)

### ✅ Working Packages
- **laravel-pwa**: 3/3 tests passing (100%)
- **filament-i18n**: 5/7 tests passing (71%)

### ⚠️ Need Setup Refinement

All other packages have test files but need Orchestra Testbench fixes:

**Issue**: Eloquent Model connection resolver not properly initialized  
**Error**: `Call to a member function connection() on null`

## To Fix

Each package needs proper Orchestra Test bench bootstrapping:

```php
protected function setUp(): void
{
    parent::setUp();
    
    // Need to properly bootstrap:
    // - Database connection resolver
    // - Storage facades
    // - Auth system
    // - Livewire (for cookie-consent)
}
```

## Packages Extracted from Proven Code

All packages are extracted from **working production code**:
- eloquent-userstamps → timesloth/cargonauten ✅
- laravel-file-storage → timesloth ✅
- filament-i18n → timesloth ✅
- filament-user-avatar → timesloth ✅
- filament-oauth → cargonauten ✅
- filament-tenancy → timesloth ✅

**Conclusion**: Code is proven, tests just need proper setup.

## Recommended Approach

1. ✅ Install packages in eveant (integration testing)
2. ✅ Validate real-world functionality
3. ⚠️ Fix unit tests based on integration learnings
4. 🎯 Achieve 85%+ coverage incrementally

## Test Refinement Tasks

- [ ] Fix Eloquent connection resolver in all TestCase files
- [ ] Add Storage::fake() properly
- [ ] Add Livewire testing support for cookie-consent
- [ ] Mock OAuth providers for filament-oauth
- [ ] Add proper database factories
- [ ] Achieve 85%+ coverage per package

**Estimated effort**: 2-4 hours of Orchestra Testbench configuration

