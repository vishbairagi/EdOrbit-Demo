<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $exam->title }} · Admit Cards</title>
    @include('examination.admit-cards._card-styles')
    <style>body{margin:0;background:#fff;font-family:'Segoe UI',Arial,sans-serif}.toolbar{max-width:210mm;margin:12px auto;display:flex;justify-content:space-between;align-items:center;padding:8px 14px;border:1px solid #e5e7eb;border-radius:6px}.toolbar button{padding:6px 14px;background:#000;color:#fff;border:0;border-radius:4px;cursor:pointer}.toolbar button.secondary{background:#e5e7eb;color:#000}@media print{.toolbar{display:none}}</style>
</head>
<body>
    <div class="toolbar no-print">
        <div class="title">{{ $exam->title }} · {{ $cards->count() }} Admit Card(s)</div>
        <div class="actions">
            <button type="button" class="secondary" onclick="window.history.back()">Back</button>
            <button type="button" onclick="window.print()">Print Admit Cards</button>
        </div>
    </div>

    @foreach($cards as $card)
        @include('examination.admit-cards._card', ['card' => $card, 'exam' => $exam, 'papers' => $papers])
    @endforeach
</body>
</html>