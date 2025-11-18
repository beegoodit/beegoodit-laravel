@php
    $id = $getId();
    $statePath = $getStatePath();
    $timezone = $getState() ?? '';
    $mapId = 'timezone-map-' . $id;
@endphp

<div 
    x-data="timezoneSelector(@js($timezone), '{{ $statePath }}', '{{ $mapId }}')"
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
            <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex items-center gap-2 text-sm text-blue-800 dark:text-blue-200">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ __('Click on a timezone region to select it') }}</span>
                </div>
            </div>
            
            <div class="relative">
                <div id="{{ $mapId }}" class="w-full rounded-lg" style="aspect-ratio: 16 / 9; min-height: 400px; background: #f0f0f0; border: 1px solid #ccc;">
                    <div class="flex items-center justify-center h-full text-gray-500">
                        <div class="text-center">
                            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto mb-2"></div>
                            <div>Loading map...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Disabled Input Field for Selected Timezone -->
        <div class="mt-4">
            <div class="relative">
                <input 
                    type="text"
                    x-model="selectedTimezone"
                    placeholder="{{ __('No timezone selected') }}"
                    disabled
                    readonly
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2"
                />
                <div 
                    x-show="selectedTimezone" 
                    x-cloak
                    class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"
                >
                    <div class="flex items-center gap-2 text-sm text-gray-700 dark:text-zinc-300">
                        <span x-text="currentTime" class="font-mono font-medium"></span>
                        <span class="text-gray-400 dark:text-zinc-500">|</span>
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
<!-- Leaflet CSS and JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" 
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" 
      crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
@endonce

<script>
function timezoneSelector(initialTimezone, statePath, mapId) {
    return {
        selectedTimezone: initialTimezone || '',
        map: null,
        timezoneLayer: null,
        selectedLayer: null,
        currentTime: '',
        utcOffset: '',
        timeInterval: null,
        mapId: mapId,
        
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
            const mapContainer = document.getElementById(this.mapId);
            if (mapContainer) {
                mapContainer.innerHTML = `
                    <div class="flex items-center justify-center h-full text-gray-500">
                        <div class="text-center">
                            <div class="text-gray-400 mb-2">🗺️</div>
                            <div class="font-medium">Interactive Map</div>
                            <div class="text-sm text-gray-400 mt-1">Click on timezone regions below to select</div>
                        </div>
                    </div>
                `;
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
            
            const mapContainer = document.getElementById(this.mapId);
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
            
            this.$nextTick(() => {
                setTimeout(() => {
                    this.createMapInstance(mapContainer);
                }, 200);
            });
        },
        
        createMapInstance(mapContainer) {
            try {
                this.map = L.map(this.mapId, {
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
                }
            }, 200);
        },
        
        async addTimezoneLayer() {
            try {
                const response = await fetch('/data/timezones-tiny.geojson');
                if (!response.ok) {
                    throw new Error(`Failed to load timezone data: ${response.status}`);
                }
                const timezoneData = await response.json();
                
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
                        }
                        
                        layer.bindTooltip(tzid, {
                            permanent: false,
                            direction: 'top',
                            sticky: true,
                            className: 'timezone-tooltip'
                        });
                        
                        layer.on('click', (e) => {
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
            } catch (error) {
                console.error('Error loading timezone layer:', error);
            }
        },
        
        selectTimezone(timezone) {
            this.selectedTimezone = timezone;
            // Update Filament form state using Livewire
            @this.set(statePath, timezone);
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

