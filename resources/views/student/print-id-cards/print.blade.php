<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'ID Card' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        @page { size: A4; margin: 8mm; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #eef0f3;
            padding: 24px;
            color: #1f2937;
        }
        .no-print { display: flex; gap: 10px; margin-bottom: 20px; justify-content: center; }
        .btn { padding: 9px 22px; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; border: none; }
        .btn-print { background: #1f2937; color: #fff; }
        .btn-back { background: #fff; color: #333; border: 1px solid #d1d5db; }

        .sheet { display: flex; flex-direction: column; align-items: center; gap: 14px; }
        .id-pair { display: flex; gap: 6mm; page-break-inside: avoid; break-inside: avoid; }

        /* CR80 landscape card */
        .id-card {
            --c: #B47A42;
            --c-dark: color-mix(in srgb, var(--c) 65%, #000);
            position: relative;
            width: 85.6mm;
            height: 54mm;
            border-radius: 3mm;
            overflow: hidden;
            background-color: #fff;
            background-size: cover;
            background-position: center;
            border: 0.25mm solid #d6d9de;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .12);
            display: flex;
            flex-direction: column;
        }
        .id-card .bar-bottom { height: 2.2mm; background: var(--c-dark); flex-shrink: 0; }

        /* ---- front ---- */
        .head { background: var(--c-dark); color: #fff; padding: 2.6mm 4mm 2.2mm; flex-shrink: 0; }
        .head .school { font-size: 3.7mm; font-weight: 800; letter-spacing: .2mm; text-transform: uppercase; line-height: 1.15; }
        .head .contact { font-size: 2.1mm; opacity: .85; margin-top: .6mm; }
        .front-body { flex: 1; display: flex; align-items: center; gap: 4mm; padding: 0 4mm; }
        .photo {
            width: 23mm; height: 28mm; border-radius: 2.4mm; flex-shrink: 0; overflow: hidden;
            background: #eceff3; border: .35mm solid #dfe3e8;
            display: flex; align-items: center; justify-content: center; font-size: 9mm; color: #9ca3af;
        }
        .photo img { width: 100%; height: 100%; object-fit: cover; }
        .info { flex: 1; min-width: 0; }
        .info .name { font-size: 4.4mm; font-weight: 800; color: var(--c-dark); text-transform: uppercase; line-height: 1.1; margin-bottom: 1.6mm; word-break: break-word; }
        .info .row { display: flex; font-size: 2.4mm; line-height: 1.55; }
        .info .row .k { width: 14mm; color: #6b7280; flex-shrink: 0; }
        .info .row .v { font-weight: 700; color: #111827; word-break: break-word; }

        /* ---- back ---- */
        .back-body { flex: 1; padding: 4mm 5mm 1mm; display: flex; flex-direction: column; justify-content: space-between; }
        .back-body .item { margin-bottom: 1.5mm; }
        .back-body .item .k { font-size: 2.1mm; color: #6b7280; }
        .back-body .item .v { font-size: 2.6mm; font-weight: 700; color: #111827; word-break: break-word; }
        .sign { align-self: flex-end; text-align: center; width: 30mm; margin-bottom: 1mm; }
        .sign img { max-height: 8mm; max-width: 28mm; display: block; margin: 0 auto 0.6mm; }
        .sign .line { border-top: .35mm solid var(--c-dark); padding-top: .8mm; font-size: 1.9mm; font-weight: 800; letter-spacing: .3mm; color: var(--c-dark); }
        .back-title { font-size: 2.2mm; font-weight: 800; letter-spacing: .4mm; color: var(--c-dark); text-transform: uppercase; margin-bottom: 1.6mm; }

        /* uploaded background: lighten the picture with a white wash (no boxes behind the text) */
        .id-card.has-bg::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, .55);
            z-index: 0;
        }
        .id-card > * { position: relative; z-index: 1; }
        .id-card.has-bg .head { background: color-mix(in srgb, var(--c-dark) 94%, transparent); }

        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
            .sheet { gap: 6mm; align-items: flex-start; }
            .id-card { box-shadow: none; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn btn-print" onclick="window.print()">Print</button>
    <button class="btn btn-back" onclick="window.close()">Close</button>
</div>

<div class="sheet">
@foreach($cards as $card)
    @php
        $bg = $card['background'] ?? null;
        $style = '--c: '.$card['color'].';'.($bg ? " background-image: url('".e($bg)."');" : '');
        $cls = 'id-card'.($bg ? ' has-bg' : '');
    @endphp
    <div class="id-pair">
        {{-- FRONT --}}
        <div class="{{ $cls }}" style="{!! $style !!}">
            <div class="head">
                <div class="school">{{ $card['school_name'] }}</div>
                @if(!empty($card['school_contact']))
                    <div class="contact">{{ $card['school_contact'] }}</div>
                @endif
            </div>
            <div class="front-body">
                <div class="photo">
                    @if(!empty($card['photo']))
                        <img src="{{ $card['photo'] }}" alt="Photo" onerror="this.remove();">
                    @else
                        &#128100;
                    @endif
                </div>
                <div class="info">
                    <div class="name">{{ $card['name'] }}</div>
                    @foreach($card['front'] as $label => $value)
                        <div class="row"><span class="k">{{ $label }}:</span><span class="v">{{ $value }}</span></div>
                    @endforeach
                </div>
            </div>
            <div class="bar-bottom"></div>
        </div>

        {{-- BACK --}}
        <div class="{{ $cls }}" style="{!! $style !!}">
            <div class="back-body">
                <div class="items">
                    <div class="back-title">{{ $card['label'] }}</div>
                    @foreach($card['back'] as $label => $value)
                        <div class="item"><div class="k">{{ $label }}</div><div class="v">{{ $value }}</div></div>
                    @endforeach
                </div>
                <div class="sign">
                    @if(!empty($card['signature']))
                        <img src="{{ $card['signature'] }}" alt="Signature" onerror="this.remove();">
                    @endif
                    <div class="line">AUTHORIZED SIGNATURE</div>
                </div>
            </div>
            <div class="bar-bottom"></div>
        </div>
    </div>
@endforeach
</div>

<script>
    // Print straight away (no extra "print" screen). Add ?preview=1 to just look at the cards.
    (function () {
        if (new URLSearchParams(location.search).has('preview')) return;
        function go() { setTimeout(function () { window.focus(); window.print(); }, 150); }
        const imgs = Array.from(document.images);
        Promise.all(imgs.map(function (i) {
            return i.complete ? Promise.resolve() : new Promise(function (r) { i.onload = i.onerror = r; });
        })).then(go);
    })();
</script>
</body>
</html>
