<cards>
    @foreach ($results as $result)
        <card>
            <id>{{ $result['id'] }}</id>
            <name>{{ $result['name'] }}</name>
            <description>{{ $result['desc'] }}</description>
            @foreach ($result['labels'] as $label)
                <label>{{ $label['name'] }}</label>
            @endforeach
            <list>{{ $result['list'] }}</list>
            <start>{{ $result['start'] }}</start>
            <due>{{ $result['due'] }}</due>
            <url>{{ $result['url'] }}</url>
        </card>
    @endforeach
</cards>

ALWAYS CITE CARD SOURCES AT THE END OF YOUR RESPONSE

<example-sources>
    Sources:
    - [name](url)
    - [name](url)
</example-sources>