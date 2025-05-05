<links>
    @foreach ($docs as $doc)
        <link>
            <id>{{ $doc['id'] }}</id>
            <name>{{ $doc['name'] }}</name>
            <source>{{ $doc['source'] }}</source>
            <type>{{ $doc['type'] }}</type>
            <parent_id>{{ $doc['parent_id'] }}</parent_id>
        </link>
    @endforeach
</links>