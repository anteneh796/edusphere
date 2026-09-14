@props([
    'columns' => [],
    'rows' => [],
    'emptyMessage' => 'No records found.',
    'emptyAction' => null,
    'actionsLabel' => '',
])

<div class="table-responsive">
    <table {{ $attributes->merge(['class' => 'table']) }}>
        <thead>
            <tr>
                @foreach ($columns as $column)
                    <th @if (is_array($column) && ($column['align'] ?? null)) class="{{ $column['align'] }}" @endif>
                        {{ is_array($column) ? $column['label'] : $column }}
                    </th>
                @endforeach

                @if ($actionsLabel || isset($actions))
                    <th class="text-right">{{ $actionsLabel }}</th>
                @endif
            </tr>
        </thead>

        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($columns as $index => $column)
                        @php
                            $cell = is_object($row) ? $row : $row[is_array($column) ? ($column['key'] ?? $index) : $index] ?? null;
                        @endphp
                        <td @class(['num' => is_array($column) && ($column['align'] ?? null) === 'num'])>
                            {!! $cell !!}
                        </td>
                    @endforeach

                    @if (isset($actions))
                        <td class="actions-cell">
                            {{ $actions(['row' => $row]) }}
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) + (isset($actions) ? 1 : 0) }}">
                        <div class="empty-state" style="padding: var(--space-6);">
                            <div class="empty-state-icon">
                                <x-icon name="file-text" class="icon-lg" />
                            </div>
                            <p>{{ $emptyMessage }}</p>
                            @if ($emptyAction)
                                {{ $emptyAction }}
                            @endif
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>