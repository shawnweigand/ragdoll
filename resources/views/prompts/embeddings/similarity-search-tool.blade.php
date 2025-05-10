<links>
    @foreach ($results as $result)
        <link>
            <title>{{ $result['title'] }}</title>
            <content>{{ $result['content'] }}</content>
            <source>{{ $result['source'] }}</source>
            <metas>
                @foreach ($result['meta'] as $meta)
                    <meta>{{ $meta }}</meta>
                @endforeach
            </metas>
            <tags>
                @foreach ($result['tags'] as $tag)
                    <tag>{{ $tag }}</tag>
                @endforeach
            </tags>
        </link>
    @endforeach
</links>

ALWAYS CITE SOURCES AT THE END OF YOUR RESPONSE

<example-sources>
    Sources:
    - [title](source)
    - [title](source)
</example-sources>