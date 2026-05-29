<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Pilot Roster - {{ config('app.name') }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; font-size: 12px; color: #1e293b; padding: 2rem; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        .sub { color: #64748b; font-size: 13px; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #e11d48; color: #fff; padding: 8px 10px; text-align: left; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
        td { padding: 7px 10px; border-bottom: 1px solid #e2e8f0; }
        tr:nth-child(even) td { background: #f8fafc; }
        .text-right { text-align: right; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
        .badge-active { background: #dcfce7; color: #166534; }
        .badge-pending { background: #fef9c3; color: #854d0e; }
        .badge-suspended { background: #fee2e2; color: #991b1b; }
        @media print { body { padding: 0; } @page { margin: 1.5cm; } }
    </style>
</head>
<body>
    <h1>{{ config('app.name') }} — Pilot Roster</h1>
    <p class="sub">Generated {{ now()->format('F j, Y H:i') }} &middot; {{ $pilots->count() }} pilots</p>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Pilot ID</th>
                <th>Rank</th>
                <th class="text-right">Hours</th>
                <th class="text-right">Flights</th>
                <th>Status</th>
                <th>Location</th>
                <th>Since</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pilots as $p)
            <tr>
                <td><strong>{{ $p->name }}</strong><br><span style="font-size:11px;color:#64748b;">{{ $p->email }}</span></td>
                <td>{{ $p->pilot_id ?? '—' }}</td>
                <td>{{ $p->rank?->name ?? 'Candidate' }}</td>
                <td class="text-right">{{ number_format($p->total_hours, 1) }}</td>
                <td class="text-right">{{ $p->total_flights }}</td>
                <td><span class="badge badge-{{ $p->status }}">{{ ucfirst($p->status) }}</span></td>
                <td>{{ $p->last_location ?? '—' }}</td>
                <td>{{ $p->created_at?->format('Y-m-d') ?? '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <script>window.print();</script>
</body>
</html>
