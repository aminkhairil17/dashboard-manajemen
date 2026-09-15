@props(['rows', 'labelHeader' => 'Indikator', 'valueHeader' => 'Nilai', 'showTarget' => true])
<table class="dtable">
    <thead>
        <tr>
            <th>{{ $labelHeader }}</th>
            <th>{{ $valueHeader }}</th>
            @if($showTarget)<th>Target</th>@endif
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
            <tr>
                <td class="strong">{{ $row['label'] ?? $row['unit'] }}</td>
                <td class="num tabular">{{ $row['val'] }}</td>
                @if($showTarget)<td>{{ $row['target'] ?? '—' }}</td>@endif
                <td><x-dashboard.chip :status="$row['status']" /></td>
            </tr>
        @endforeach
    </tbody>
</table>
