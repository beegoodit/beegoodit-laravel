<div 
    x-data="timezoneSelector(@js($getState() ?? ''), '{{ $getStatePath() }}')"
    class="w-full"
    wire:ignore.self
>
    @once
    <style>
    .timezone-tooltip {
        background-color: rgba(0, 0, 0, 0.8) !important;
        color: white !important;
        border: none !important;
        border-radius: 4px !important;
        padding: 4px 8px !important;
        font-size: 12px !important;
        font-weight: 500 !important;
    }
    
    .timezone-tooltip::before {
        border-top-color: rgba(0, 0, 0, 0.8) !important;
    }
    
    /* Force timezone map container and all Leaflet elements to stay below Filament navbar */
    /* Filament navbar is typically z-30 or z-40, so we cap everything at z-20 */
    .timezone-map-container,
    [id^="timezone-map"] {
        position: relative !important;
        z-index: 1 !important;
        isolation: isolate !important;
    }
    
    /* Override all Leaflet z-index values - cap them at 20 to stay below navbar */
    .timezone-map-container .leaflet-container,
    .timezone-map-container .leaflet-pane,
    .timezone-map-container .leaflet-map-pane,
    .timezone-map-container .leaflet-tile-pane,
    .timezone-map-container .leaflet-overlay-pane,
    .timezone-map-container .leaflet-shadow-pane,
    .timezone-map-container .leaflet-marker-pane,
    .timezone-map-container .leaflet-tooltip-pane,
    .timezone-map-container .leaflet-popup-pane,
    .timezone-map-container .leaflet-control-container,
    .timezone-map-container .leaflet-control,
    .timezone-map-container .leaflet-popup,
    .timezone-map-container .leaflet-popup-content-wrapper,
    .timezone-map-container .leaflet-tooltip,
    .timezone-map-container .leaflet-zoom-box,
    .timezone-map-container .leaflet-image-layer,
    .timezone-map-container .leaflet-layer,
    .timezone-map-container .leaflet-tile-container,
    .timezone-map-container .leaflet-tile,
    .timezone-map-container .leaflet-objects-pane,
    .timezone-map-container .leaflet-shadow-pane img,
    .timezone-map-container .leaflet-marker-icon,
    .timezone-map-container .leaflet-marker-shadow,
    .timezone-map-container [class*="leaflet"],
    [id^="timezone-map"] .leaflet-container,
    [id^="timezone-map"] .leaflet-pane,
    [id^="timezone-map"] .leaflet-map-pane,
    [id^="timezone-map"] .leaflet-tile-pane,
    [id^="timezone-map"] .leaflet-overlay-pane,
    [id^="timezone-map"] .leaflet-shadow-pane,
    [id^="timezone-map"] .leaflet-marker-pane,
    [id^="timezone-map"] .leaflet-tooltip-pane,
    [id^="timezone-map"] .leaflet-popup-pane,
    [id^="timezone-map"] .leaflet-control-container,
    [id^="timezone-map"] .leaflet-control,
    [id^="timezone-map"] .leaflet-popup,
    [id^="timezone-map"] .leaflet-popup-content-wrapper,
    [id^="timezone-map"] .leaflet-tooltip,
    [id^="timezone-map"] .leaflet-zoom-box,
    [id^="timezone-map"] .leaflet-image-layer,
    [id^="timezone-map"] .leaflet-layer,
    [id^="timezone-map"] .leaflet-tile-container,
    [id^="timezone-map"] .leaflet-tile,
    [id^="timezone-map"] .leaflet-objects-pane,
    [id^="timezone-map"] .leaflet-shadow-pane img,
    [id^="timezone-map"] .leaflet-marker-icon,
    [id^="timezone-map"] .leaflet-marker-shadow,
    [id^="timezone-map"] [class*="leaflet"] {
        z-index: 20 !important;
    }
    </style>
    @endonce
    
    <div class="space-y-2">
        @if ($getLabel())
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ $getLabel() }}
            </label>
        @endif

        <!-- Interactive Leaflet Map -->
        <div class="hidden md:block" wire:ignore>
            <div class="mb-4 rounded-lg bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 p-4">
                <div class="flex items-center gap-2 text-sm text-primary-700 dark:text-primary-400">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" style="width: 1rem; height: 1rem;">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ __('Click on a timezone region to select it') }}</span>
                </div>
            </div>
            
            <div class="relative">
                <div id="timezone-map" class="timezone-map-container w-full rounded-t-lg" style="aspect-ratio: 16 / 9; min-height: 400px; background: #f0f0f0; border: 1px solid #ccc; border-bottom: none;">
                    <div class="flex items-center justify-center h-full text-gray-500">
                        <div class="text-center">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto mb-2"></div>
                            <div>Loading map...</div>
                        </div>
                    </div>
                </div>
                
                <!-- Dark Footer Bar -->
                <div class="bg-gray-700 dark:bg-gray-800 text-white rounded-b-lg px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center">
                        <span x-show="selectedTimezone" x-text="selectedTimezone" class="text-base font-medium"></span>
                        <span x-show="!selectedTimezone" class="text-base font-medium">{{ __('No timezone selected') }}</span>
                    </div>
                    <div 
                        x-show="selectedTimezone" 
                        x-cloak
                        class="flex items-center gap-2 text-sm"
                    >
                        <span x-text="currentTime" class="font-mono font-medium"></span>
                        <span>|</span>
                        <span x-text="utcOffset" class="font-medium"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile fallback: Simple select -->
        <div class="md:hidden">
            <select 
                x-model="selectedTimezone"
                @change="selectTimezone($event.target.value)"
                class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2"
            >
                <option value="">{{ __('Select your timezone...') }}</option>
                <option value="UTC">UTC (Coordinated Universal Time)</option>
                <option value="Europe/Berlin">Europe/Berlin (Germany)</option>
                <option value="Europe/London">Europe/London (UK)</option>
                <option value="Europe/Paris">Europe/Paris (France)</option>
                <option value="Europe/Vienna">Europe/Vienna (Austria)</option>
                <option value="Europe/Zurich">Europe/Zurich (Switzerland)</option>
                <option value="America/New_York">America/New York (EST)</option>
                <option value="America/Los_Angeles">America/Los Angeles (PST)</option>
                <option value="Asia/Tokyo">Asia/Tokyo (Japan)</option>
                <option value="Australia/Sydney">Australia/Sydney</option>
            </select>
        </div>
    </div>
</div>

@once
@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>
<script>
function timezoneSelector(initialTimezone, statePath) {
    return {
        selectedTimezone: initialTimezone || '',
        map: null,
        timezoneLayer: null,
        selectedLayer: null,
        currentTime: '',
        utcOffset: '',
        timeInterval: null,
        statePath: statePath,
        
        init() {
            console.log('TimezoneSelector initialized with timezone:', this.selectedTimezone);
            
            this.setupLeafletErrorHandling();
            
            this.$nextTick(() => {
                this.waitForLeaflet();
            });
            
            this.updateTimeDisplay();
            this.timeInterval = setInterval(() => {
                this.updateTimeDisplay();
            }, 1000);
        },
        
        destroy() {
            if (this.timeInterval) {
                clearInterval(this.timeInterval);
            }
            if (this.zIndexObserver) {
                this.zIndexObserver.disconnect();
                this.zIndexObserver = null;
            }
            if (this.map) {
                this.map.remove();
                this.map = null;
            }
        },
        
        updateTimeDisplay() {
            this.currentTime = this.getCurrentTime();
            this.utcOffset = this.getUtcOffset();
        },
        
        setupLeafletErrorHandling() {
            window.addEventListener('error', (event) => {
                if (event.message && event.message.includes('offsetWidth')) {
                    console.log('Caught Leaflet DOM error, preventing crash:', event.message);
                    event.preventDefault();
                    event.stopPropagation();
                    return false;
                }
            });
        },
        
        waitForLeaflet(retryCount = 0) {
            if (typeof L !== 'undefined') {
                console.log('Leaflet found, initializing map...');
                this.initLeafletMap();
            } else if (retryCount < 50) {
                setTimeout(() => {
                    this.waitForLeaflet(retryCount + 1);
                }, 100);
            } else {
                console.error('Leaflet failed to load after 5 seconds');
                this.showMapError();
            }
        },
        
        showMapError() {
            const mapContainer = document.getElementById('timezone-map');
            if (mapContainer) {
                mapContainer.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500"><div class="text-center"><div class="text-gray-400 mb-2">🗺️</div><div class="font-medium">Interactive Map</div><div class="text-sm text-gray-400 mt-1">Click on timezone regions below to select</div></div></div>';
            }
        },
        
        initLeafletMap() {
            if (this.map) {
                try {
                    this.map.remove();
                } catch (e) {
                    console.log('Error removing old map:', e);
                }
                this.map = null;
            }
            
            const mapContainer = document.getElementById('timezone-map');
            if (!mapContainer) {
                console.error('Map container not found');
                this.showMapError();
                return;
            }
            
            mapContainer.innerHTML = '';
            mapContainer.style.height = '400px';
            mapContainer.style.width = '100%';
            mapContainer.style.display = 'block';
            mapContainer.style.position = 'relative';
            // Ensure the class is maintained for CSS targeting
            mapContainer.classList.add('timezone-map-container');
            
            // Create unique ID to avoid conflicts
            const mapId = 'timezone-map-' + Date.now();
            mapContainer.id = mapId;
            
            this.$nextTick(() => {
                setTimeout(() => {
                    this.createMapInstance(mapContainer, mapId);
                }, 200);
            });
        },
        
        createMapInstance(mapContainer, mapId) {
            try {
                this.map = L.map(mapId, {
                    preferCanvas: true,
                    zoomControl: false,
                    attributionControl: true,
                    dragging: false,
                    touchZoom: false,
                    doubleClickZoom: false,
                    scrollWheelZoom: false,
                    boxZoom: false,
                    keyboard: false,
                    zoomSnap: 0.5,
                    zoomDelta: 1,
                    minZoom: 1.5,
                    maxZoom: 1.5
                }).setView([20, 0], 1.5);
                
                this.continueMapInitialization();
            } catch (error) {
                console.error('Error creating Leaflet map:', error);
                this.showMapError();
            }
        },
        
        async continueMapInitialization() {
            if (!this.map) return;
            
            try {
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 18,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(this.map);
            } catch (error) {
                console.error('Error adding tile layer:', error);
                this.showMapError();
                return;
            }
            
            try {
                await this.addTimezoneLayer();
            } catch (error) {
                console.error('Error adding timezone layer:', error);
            }
            
            setTimeout(() => {
                if (this.map) {
                    this.map.invalidateSize();
                    // Force z-index override after map is fully initialized
                    this.overrideLeafletZIndex();
                    
                    // Set up observer to catch dynamically created elements
                    this.setupZIndexObserver();
                }
            }, 200);
        },
        
        overrideLeafletZIndex() {
            // Override any inline z-index styles that Leaflet sets
            const mapContainer = document.getElementById(this.map.getContainer().id)?.closest('.timezone-map-container') || 
                                 document.querySelector('.timezone-map-container');
            if (mapContainer) {
                const allElements = mapContainer.querySelectorAll('*');
                allElements.forEach((el) => {
                    const zIndex = parseInt(el.style.zIndex || window.getComputedStyle(el).zIndex || '0');
                    if (zIndex > 20) {
                        el.style.setProperty('z-index', '20', 'important');
                    }
                });
                
                // Also check for Leaflet panes that might be direct children
                const leafletPanes = document.querySelectorAll('.leaflet-pane, .leaflet-container, .leaflet-control-container');
                leafletPanes.forEach((pane) => {
                    if (mapContainer.contains(pane)) {
                        const zIndex = parseInt(pane.style.zIndex || window.getComputedStyle(pane).zIndex || '0');
                        if (zIndex > 20) {
                            pane.style.setProperty('z-index', '20', 'important');
                        }
                    }
                });
            }
        },
        
        setupZIndexObserver() {
            const mapContainer = document.querySelector('.timezone-map-container');
            if (!mapContainer || !window.MutationObserver) return;
            
            const observer = new MutationObserver(() => {
                this.overrideLeafletZIndex();
            });
            
            observer.observe(mapContainer, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['style', 'class']
            });
            
            // Store observer for cleanup
            this.zIndexObserver = observer;
        },
        
        async addTimezoneLayer() {
            try {
                console.log('Loading timezone GeoJSON data...');
                const response = await fetch('/data/timezones-tiny.geojson');
                if (!response.ok) {
                    throw new Error(`Failed to load timezone data: ${response.status}`);
                }
                const timezoneData = await response.json();
                console.log(`Loaded ${timezoneData.features.length} timezone regions`);
                
                this.timezoneLayer = L.geoJSON(timezoneData, {
                    style: (feature) => {
                        const isSelected = this.selectedTimezone === feature.properties.tzid;
                        return {
                            color: isSelected ? '#10b981' : '#3b82f6',
                            weight: isSelected ? 2 : 1,
                            opacity: 0.6,
                            fillColor: isSelected ? '#34d399' : '#60a5fa',
                            fillOpacity: isSelected ? 0.4 : 0.15
                        };
                    },
                    onEachFeature: (feature, layer) => {
                        const tzid = feature.properties.tzid;
                        
                        if (this.selectedTimezone === tzid) {
                            this.selectedLayer = layer;
                            console.log('Initial timezone highlighted:', tzid);
                        }
                        
                        layer.bindTooltip(tzid, {
                            permanent: false,
                            direction: 'top',
                            sticky: true,
                            className: 'timezone-tooltip'
                        });
                        
                        layer.on('click', (e) => {
                            console.log('Layer clicked:', tzid);
                            L.DomEvent.stopPropagation(e);
                            
                            this.map.eachLayer((l) => {
                                if (l.getTooltip && l.getTooltip()) {
                                    l.closeTooltip();
                                }
                            });
                            
                            if (this.selectedLayer && this.selectedLayer !== layer) {
                                this.selectedLayer.setStyle({
                                    fillOpacity: 0.15,
                                    weight: 1,
                                    color: '#3b82f6',
                                    fillColor: '#60a5fa'
                                });
                            }
                            
                            layer.setStyle({
                                fillOpacity: 0.4,
                                weight: 2,
                                color: '#10b981',
                                fillColor: '#34d399'
                            });
                            
                            this.selectedLayer = layer;
                            this.selectTimezone(tzid);
                        });
                        
                        layer.on('mouseover', () => {
                            if (this.selectedLayer !== layer) {
                                layer.setStyle({
                                    fillOpacity: 0.3,
                                    weight: 2,
                                    color: '#2563eb'
                                });
                            }
                        });
                        
                        layer.on('mouseout', () => {
                            if (this.selectedLayer !== layer) {
                                layer.setStyle({
                                    fillOpacity: 0.15,
                                    weight: 1,
                                    color: '#3b82f6'
                                });
                            }
                        });
                    }
                }).addTo(this.map);
                console.log('Timezone layer added successfully');
            } catch (error) {
                console.error('Error loading timezone layer:', error);
            }
        },
        
        selectTimezone(timezone) {
            console.log('Selecting timezone:', timezone);
            this.selectedTimezone = timezone;
            @this.set(this.statePath, timezone);
        },
        
        getCurrentTime() {
            if (!this.selectedTimezone) return '';
            
            try {
                const now = new Date();
                return now.toLocaleTimeString('en-US', {
                    timeZone: this.selectedTimezone,
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: false
                });
            } catch (e) {
                return '';
            }
        },
        
        getUtcOffset() {
            if (!this.selectedTimezone) return '';
            
            try {
                const now = new Date();
                const formatter = new Intl.DateTimeFormat('en-US', {
                    timeZone: this.selectedTimezone,
                    timeZoneName: 'shortOffset'
                });
                
                const parts = formatter.formatToParts(now);
                const timeZonePart = parts.find(part => part.type === 'timeZoneName');
                
                if (timeZonePart) {
                    return timeZonePart.value.replace('GMT', 'UTC');
                }
                
                const utcDate = new Date(now.toLocaleString('en-US', { timeZone: 'UTC' }));
                const tzDate = new Date(now.toLocaleString('en-US', { timeZone: this.selectedTimezone }));
                const offset = (tzDate - utcDate) / (1000 * 60 * 60);
                const sign = offset >= 0 ? '+' : '-';
                const hours = Math.floor(Math.abs(offset));
                const minutes = Math.round((Math.abs(offset) - hours) * 60);
                
                return `UTC${sign}${hours}${minutes > 0 ? ':' + String(minutes).padStart(2, '0') : ''}`;
            } catch (e) {
                return '';
            }
        }
    };
}
</script>
@endpush
@endonce
