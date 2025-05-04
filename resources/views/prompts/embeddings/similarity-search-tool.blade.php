<links>
    @foreach ($results as $result)
        <link>
            <title>{{ $result['title'] }}</title>
            <content>{{ $result['content'] }}</content>
            <source>{{ $result['source'] }}</source>
        </link>
    @endforeach
</links>

ALWAYS CITE SOURCES AT THE END OF YOUR RESPONSE

<example-sources>
    Sources:
    - [title](source)
    - [title](source)
</example-sources>