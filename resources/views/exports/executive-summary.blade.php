<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Executive Summary - ZESCO</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #1a1a1a; margin: 0; padding: 20px; }
        .header { background: #006838; color: white; padding: 20px; margin: -20px -20px 20px; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p { margin: 5px 0 0; opacity: 0.85; font-size: 11px; }
        .meta { font-size: 10px; color: #666; margin-bottom: 15px; }
        .kpi-grid { display: table; width: 100%; margin-bottom: 20px; }
        .kpi-card { display: table-cell; width: 25%; padding: 10px; text-align: center; border: 1px solid #e5e7eb; }
        .kpi-card .value { font-size: 20px; font-weight: bold; color: #006838; }
        .kpi-card .label { font-size: 10px; color: #6b7280; text-transform: uppercase; margin-top: 3px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10px; }
        th { background: #f3f4f6; padding: 8px 6px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; color: #374151; border-bottom: 2px solid #d1d5db; }
        td { padding: 6px; border-bottom: 1px solid #e5e7eb; }
        tr:nth-child(even) { background: #f9fafb; }
        h2 { color: #006838; font-size: 14px; margin: 20px 0 10px; border-bottom: 1px solid #006838; padding-bottom: 5px; }
        .severity-critical { color: #dc2626; font-weight: bold; }
        .severity-high { color: #ea580c; font-weight: bold; }
        .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #d1d5db; font-size: 9px; color: #9ca3af; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ZESCO Executive Insights Dashboard</h1>
        <p>Executive Summary Report</p>
    </div>

    <div class="meta">
        Generated: {{ $generatedAt }} | By: {{ $generatedBy }} | Source: {{ $summary['data_source'] ?? 'N/A' }}
    </div>

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="value">ZMW {{ number_format($summary['total_revenue'] ?? 0, 0) }}</div>
            <div class="label">Total Revenue</div>
        </div>
        <div class="kpi-card">
            <div class="value">{{ $summary['total_projects'] ?? 0 }}</div>
            <div class="label">Active Projects</div>
        </div>
        <div class="kpi-card">
            <div class="value">{{ number_format($summary['avg_completion'] ?? 0, 1) }}%</div>
            <div class="label">Avg. Completion</div>
        </div>
        <div class="kpi-card">
            <div class="value">{{ $summary['high_risks'] ?? 0 }}</div>
            <div class="label">High Risks</div>
        </div>
    </div>

    <h2>Directorate Performance</h2>
    <table>
        <thead>
            <tr>
                <th>Directorate</th>
                <th>Code</th>
                <th style="text-align:right">Revenue (ZMW)</th>
                <th style="text-align:right">Budget (ZMW)</th>
                <th style="text-align:center">Completion</th>
                <th style="text-align:center">Risks</th>
                <th style="text-align:center">Score</th>
            </tr>
        </thead>
        <tbody>
            @forelse($summary['directorates'] ?? [] as $dir)
            <tr>
                <td>{{ $dir['name'] ?? '' }}</td>
                <td>{{ $dir['code'] ?? '' }}</td>
                <td style="text-align:right">{{ number_format($dir['revenue'] ?? 0, 0) }}</td>
                <td style="text-align:right">{{ number_format($dir['budget'] ?? 0, 0) }}</td>
                <td style="text-align:center">{{ number_format($dir['completion_percentage'] ?? 0, 1) }}%</td>
                <td style="text-align:center">{{ $dir['open_risks'] ?? 0 }}</td>
                <td style="text-align:center">{{ number_format($dir['score'] ?? 0, 1) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; color:#9ca3af;">No directorate data available.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        &copy; {{ date('Y') }} ZESCO Limited — Confidential Executive Report
    </div>
</body>
</html>
