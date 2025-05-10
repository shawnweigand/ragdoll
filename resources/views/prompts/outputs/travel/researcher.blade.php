<findings>
    @foreach($response as $finding)
        <finding>
            <title>{{ $finding['title'] }}</title>
            <summary>{{ $finding['summary'] }}</summary>
            <details>{{ $finding['details'] }}</details>
            @if (!empty($finding['link']))
                <link>{{ $finding['link'] }}</link>
            @endif
            @if (!empty($finding['price']))
                <link>{{ $finding['price'] }}</link>
            @endif
        </finding>
    @endforeach
</findings>