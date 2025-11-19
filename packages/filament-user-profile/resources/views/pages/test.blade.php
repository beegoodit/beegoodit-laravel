<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Basic Tailwind Classes Test -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-2">Basic Tailwind Classes</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Testing basic utility classes</p>
            
            <div class="space-y-4">
                <div class="p-4 bg-blue-100 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
                    <p class="text-blue-800 dark:text-blue-200">Blue background with border</p>
                </div>
                
                <div class="p-4 bg-green-100 dark:bg-green-900/20 rounded border border-green-200 dark:border-green-800">
                    <p class="text-green-800 dark:text-green-200">Green background with border</p>
                </div>
                
                <div class="p-4 bg-red-100 dark:bg-red-900/20 rounded border border-red-200 dark:border-red-800">
                    <p class="text-red-800 dark:text-red-200">Red background with border</p>
                </div>
            </div>
        </div>

        <!-- Filament Color Classes Test -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-2">Filament Color Classes</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Testing Filament's default color classes</p>
            
            <div class="space-y-4">
                <div class="rounded-lg bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 p-4">
                    <div class="flex items-center gap-2 text-sm text-primary-700 dark:text-primary-400">
                        <span>Primary color: bg-primary-50, text-primary-700</span>
                    </div>
                </div>

                <div class="rounded-lg bg-success-50 dark:bg-success-900/20 border border-success-200 dark:border-success-800 p-4">
                    <div class="flex items-center gap-2 text-sm text-success-700 dark:text-success-400">
                        <span>Success color: bg-success-50, text-success-700</span>
                    </div>
                </div>

                <div class="rounded-lg bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800 p-4">
                    <div class="flex items-center gap-2 text-sm text-warning-700 dark:text-warning-400">
                        <span>Warning color: bg-warning-50, text-warning-700</span>
                    </div>
                </div>

                <div class="rounded-lg bg-danger-50 dark:bg-danger-900/20 border border-danger-200 dark:border-danger-800 p-4">
                    <div class="flex items-center gap-2 text-sm text-danger-700 dark:text-danger-400">
                        <span>Danger color: bg-danger-50, text-danger-700</span>
                    </div>
                </div>

                <div class="rounded-lg bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-800 p-4">
                    <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-400">
                        <span>Gray color: bg-gray-50, text-gray-700</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Layout Classes Test -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-2">Layout Classes</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Testing flexbox, grid, and spacing</p>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 bg-purple-100 dark:bg-purple-900/20 rounded">Grid Item 1</div>
                <div class="p-4 bg-purple-100 dark:bg-purple-900/20 rounded">Grid Item 2</div>
                <div class="p-4 bg-purple-100 dark:bg-purple-900/20 rounded">Grid Item 3</div>
            </div>
            
            <div class="flex flex-wrap gap-2 mt-4">
                <span class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900/20 rounded-full text-indigo-800 dark:text-indigo-200 text-sm">Flex Item 1</span>
                <span class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900/20 rounded-full text-indigo-800 dark:text-indigo-200 text-sm">Flex Item 2</span>
                <span class="px-3 py-1 bg-indigo-100 dark:bg-indigo-900/20 rounded-full text-indigo-800 dark:text-indigo-200 text-sm">Flex Item 3</span>
            </div>
        </div>

        <!-- Typography Classes Test -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-2">Typography Classes</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Testing text utilities</p>
            
            <div class="space-y-2">
                <p class="text-xs text-gray-500">Extra small text (text-xs)</p>
                <p class="text-sm text-gray-600">Small text (text-sm)</p>
                <p class="text-base text-gray-700">Base text (text-base)</p>
                <p class="text-lg text-gray-800">Large text (text-lg)</p>
                <p class="text-xl font-bold text-gray-900">Extra large bold text (text-xl font-bold)</p>
            </div>
        </div>

        <!-- Status -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-2">Test Status</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                This page is located in the package at <code class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">packages/filament-user-profile/resources/views/pages/test.blade.php</code>
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                If you can see styled elements above, Tailwind classes are working in the package. Compare this with the main application test page at <code class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">/portal/{tenant}/test</code>.
            </p>
        </div>
    </div>
</x-filament-panels::page>
