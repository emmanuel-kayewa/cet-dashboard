<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $directorate->name }} - Directorate Report</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #1a1a1a; margin: 0; padding: 20px; }
        .header { background: #006838; color: white; padding: 20px; margin: -20px -20px 20px; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p { margin: 5px 0 0; opacity: 0.85; font-size: 11px; }
        .meta { font-size: 10px; color: #666; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10px; }
        th { background: #f3f4f6; padding: 8px 6px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #374151; border-bottom: 2px solid #d1d5db; }
        td { padding: 6px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background: #f9fafb; }
        h2 { color: #006838; font-size: 14px; margin: 20px 0 10px; border-bottom: 1px solid #006838; padding-bottom: 5px; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #d1d5db; font-size: 9px; color: #9ca3af; text-align: center; }
        .severity-critical { color: #dc2626; font-weight: bold; }
        .severity-high { color: #ea580c; font-weight: bold; }
        .severity-medium { color: #d97706; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $directorate->name }}</h1>
        <p>{{ $directorate->code }} — Directorate Performance Report</p>
    </div>

    <div class="meta">
        Generated: {{ $generatedAt }} | By: {{ $generatedBy }} | Head: {{ $directorate->head_name ?? 'N/A' }}
    </div>

    <h2>Key Performance Indicators</h2>
    <table>
        <thead>
            <tr>
                <th>KPI</th>
                <th style="text-align:right">Value</th>
                <th style="text-align:right">Target</th>
                <th style="text-align:center">Achievement</th>
                <th style="text-align:center">Change</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detail['kpis'] ?? [] as $kpi)
            <tr>
                <td>{{ $kpi['kpi_name'] ?? '' }}</td>
                <td style="text-align:right">{{ number_format($kpi['value'] ?? 0, 2) }}</td>
                <td style="text-align:right">{{ number_format($kpi['target'] ?? 0, 2) }}</td>
                <td style="text-align:center">
                    @if(isset($kpi['target']) && $kpi['target'] > 0)
                        {{ number_format(($kpi['value'] ?? 0) / $kpi['target'] * 100, 1) }}%
                    @else
                        N/A
                    @endif
                </td>
                <td style="text-align:center">{{ ($kpi['change_percentage'] ?? 0) }}%</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center; color:#9ca3af;">No KPI data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Financial Summary</h2>
    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th style="text-align:right">Amount (ZMW)</th>
                <th style="text-align:right">Budget (ZMW)</th>
                <th style="text-align:right">Variance (ZMW)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detail['financials'] ?? [] as $fin)
            <tr>
                <td>{{ ucfirst($fin['category'] ?? '') }}</td>
                <td style="text-align:right">{{ number_format($fin['amount'] ?? 0, 0) }}</td>
                <td style="text-align:right">{{ number_format($fin['budget'] ?? 0, 0) }}</td>
                <td style="text-align:right">{{ number_format(($fin['amount'] ?? 0) - ($fin['budget'] ?? 0), 0) }}</td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center; color:#9ca3af;">No financial data.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Projects</h2>
    <table>
        <thead>
            <tr>
                <th>Project</th>
                <th>Status</th>
                <th style="text-align:center">Completion</th>
                <th style="text-align:right">Budget (ZMW)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detail['projects'] ?? [] as $proj)
            <tr>
                <td>{{ $proj['name'] ?? $proj['title'] ?? '' }}</td>
                <td>{{ ucfirst($proj['status'] ?? '') }}</td>
                <td style="text-align:center">{{ number_format($proj['completion_percentage'] ?? 0, 1) }}%</td>
                <td style="text-align:right">{{ number_format($proj['budget'] ?? 0, 0) }}</td>
            </tr>
            @empty
            <tr><td colspan="4" style="text-align:center; color:#9ca3af;">No projects.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Risk Register</h2>
    <table>
        <thead>
            <tr>
                <th>Risk</th>
                <th>Category</th>
                <th style="text-align:center">Score</th>
                <th>Status</th>
                <th>Owner</th>
            </tr>
        </thead>
        <tbody>
            @forelse($detail['risks'] ?? [] as $risk)
            <tr>
                <td>{{ $risk['title'] ?? '' }}</td>
                <td>{{ ucfirst($risk['category'] ?? '') }}</td>
                <td style="text-align:center" class="{{ ($risk['likelihood'] ?? 0) * ($risk['impact'] ?? 0) >= 20 ? 'severity-critical' : (($risk['likelihood'] ?? 0) * ($risk['impact'] ?? 0) >= 12 ? 'severity-high' : 'severity-medium') }}">
                    {{ ($risk['likelihood'] ?? 0) * ($risk['impact'] ?? 0) }}
                </td>
                <td>{{ ucfirst($risk['status'] ?? '') }}</td>
                <td>{{ $risk['owner'] ?? '' }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center; color:#9ca3af;">No risks registered.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        &copy; {{ date('Y') }} ZESCO Limited — Confidential Directorate Report
    </div>
</body>
</html>
