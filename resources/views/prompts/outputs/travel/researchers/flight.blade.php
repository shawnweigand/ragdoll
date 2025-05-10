<findings>
    @foreach($response as $finding)
        <finding>
            <title>{{ $finding['title'] }}</title>
            <summary>{{ $finding['summary'] }}</summary>
            <details>{{ $finding['details'] }}</details>
            <link>{{ $finding['link'] }}</link>
            <price>{{ $finding['price'] }}</price>
            <duration>{{ $finding['duration'] }}</duration>
            <provider>{{ $finding['provider'] }}</provider>
            <departure>
                <date>{{ $finding['departure']['date'] }}</date>
                <time>{{ $finding['departure']['time'] }}</time>
                <location>{{ $finding['departure']['location'] }}</location>
            </departure>
            <arrival>
                <date>{{ $finding['arrival']['date'] }}</date>
                <time>{{ $finding['arrival']['time'] }}</time>
                <location>{{ $finding['arrival']['location'] }}</location>
            </arrival>
        </finding>
    @endforeach
</findings>