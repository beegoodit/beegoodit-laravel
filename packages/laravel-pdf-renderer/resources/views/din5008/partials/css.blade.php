@php
    use BeegoodIT\Pdf\Support\Din5008Metrics;
@endphp
:root {
@foreach (Din5008Metrics::cssVariables() as $name => $value)
    {{ $name }}: {{ $value }};
@endforeach
}

din-5008 {
    display: block;
    margin: 0;
    overflow: hidden;
    position: relative;
    box-sizing: border-box;
    page-break-after: always;
    width: var(--din-paper-width);
    min-height: var(--din-paper-height);
    padding-right: var(--din-content-right);
    padding-top: var(--din-briefkopf-a);
    font-family: DejaVu Sans, sans-serif;
    font-size: 11pt;
    color: #111827;
}

din-5008[form='B'] {
    padding-top: var(--din-briefkopf-b);
}

din-5008::before,
din-5008::after {
    content: '';
    position: absolute;
    left: 0;
    display: block;
    border: 1pt solid rgba(0, 0, 0, 0.3);
    border-left: none;
    border-right: none;
    width: 10mm;
}

din-5008::after {
    top: 148.5mm;
    width: 15mm;
    border-bottom: none;
}

din-5008::before {
    height: calc(105mm - 1pt);
    top: 87mm;
}

din-5008[form='B']::before {
    top: 105mm;
}

din-5008[no-markers]::before,
din-5008[no-markers]::after {
    display: none;
}

din-5008 .briefkopf,
din-5008 .rucksendeangabe,
din-5008 .vermerkzone,
din-5008 .anschriftzone,
din-5008 .informationsblock,
din-5008 .textfeld {
    display: block;
}

din-5008 .briefkopf {
    position: absolute;
    top: 0;
    left: 0;
    width: var(--din-paper-width);
    height: var(--din-briefkopf-a);
}

din-5008[form='B'] .briefkopf {
    height: var(--din-briefkopf-b);
}

din-5008 .rucksendeangabe,
din-5008 .vermerkzone,
din-5008 .anschriftzone {
    position: absolute;
    left: var(--din-content-left);
    width: var(--din-anschrift-width);
}

din-5008 .rucksendeangabe {
    font-size: 8pt;
    top: var(--din-briefkopf-a);
    height: 5mm;
}

din-5008[form='B'] .rucksendeangabe {
    display: none;
}

din-5008 .vermerkzone {
    top: 32mm;
    height: 12.7mm;
}

din-5008[form='B'] .vermerkzone {
    top: var(--din-briefkopf-b);
    height: 17.7mm;
}

din-5008 .anschriftzone {
    top: 44.7mm;
    height: 27.3mm;
}

din-5008[form='B'] .anschriftzone {
    top: 62.7mm;
    height: 27.3mm;
}

din-5008 .informationsblock {
    margin: 5mm 0 8.46mm var(--din-infoblock-left);
    max-width: var(--din-infoblock-max-width);
    min-height: 40mm;
}

din-5008[form='B'] .informationsblock {
    height: 40mm;
}

din-5008 .textfeld {
    margin: 0 0 4.23mm var(--din-content-left);
}

/*
 * Folgeseite: fixed A4 geometry (din-5008-css style).
 * briefkopf uses position:fixed so it repeats on every printed page.
 * Textfeld flows in the content band; bottom reserved via Chromium marginBottom.
 */
din-5008-folgeseite {
    display: block;
    position: relative;
    box-sizing: border-box;
    width: var(--din-paper-width);
    min-height: var(--din-paper-height);
    margin: 0;
    padding: var(--din-folgeseite-header) var(--din-content-right) 0 var(--din-content-left);
    font-family: DejaVu Sans, sans-serif;
    font-size: 11pt;
    color: #111827;
}

din-5008-folgeseite .briefkopf {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1;
    box-sizing: border-box;
    width: var(--din-paper-width);
    height: var(--din-folgeseite-header);
    padding: 3mm var(--din-content-right) 3mm var(--din-content-left);
    text-align: right;
    background: #fff;
}

din-5008-folgeseite .briefkopf img {
    display: inline-block;
    max-width: var(--din-logo-max-width);
    max-height: var(--din-logo-max-height);
    width: auto;
    height: auto;
    object-fit: contain;
    border: 0;
    vertical-align: middle;
}

din-5008-folgeseite .textfeld,
din-5008-folgeseite .body {
    display: block;
    position: relative;
    box-sizing: border-box;
    width: 100%;
    margin: 0;
}

din-5008-folgeseite .seitenangabe {
    display: none; /* filled by Chromium footerTemplate */
}

/* Debug outlines — enable via BEEGOODIT_PDF_RENDER_DEBUG_LAYOUT / debugLayout arg */
din-5008[debug-layout] .briefkopf {
    outline: 2px solid #ef4444;
    outline-offset: -2px;
}
din-5008[debug-layout] .rucksendeangabe {
    outline: 2px solid #f97316;
    outline-offset: -2px;
}
din-5008[debug-layout] .vermerkzone {
    outline: 2px solid #eab308;
    outline-offset: -2px;
}
din-5008[debug-layout] .anschriftzone {
    outline: 2px solid #22c55e;
    outline-offset: -2px;
}
din-5008[debug-layout] .informationsblock {
    outline: 2px solid #a855f7;
    outline-offset: -2px;
}
din-5008[debug-layout] .textfeld {
    outline: 2px solid #3b82f6;
    outline-offset: -2px;
}

din-5008-folgeseite[debug-layout] .briefkopf {
    outline: 2px solid #ef4444;
    outline-offset: -2px;
}
din-5008-folgeseite[debug-layout] .textfeld,
din-5008-folgeseite[debug-layout] .body {
    outline: 2px solid #22c55e;
    outline-offset: -2px;
    min-height: 40mm;
}
