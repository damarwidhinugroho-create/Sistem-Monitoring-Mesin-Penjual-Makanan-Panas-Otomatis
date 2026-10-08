<div class="data-table-wrap">
    <table {{ $attributes->class('data-table') }}>
        @if ($headers)
            <thead>
                <tr>
                    @foreach ($headers as $header)
                        <th scope="col">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody>{{ $slot }}</tbody>
    </table>
</div>
