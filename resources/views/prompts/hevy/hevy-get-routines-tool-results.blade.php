<routines>
    @foreach ($results as $routine)
        <routine>
            {{-- <id>{{ $routine['id'] }}</id> --}}
            <title>{{ $routine['title'] }}</title>
            {{-- <folder_id>{{ $routine['folder_id'] }}<folder_id> --}}
            {{-- <updated_at>{{ $routine['updated_at'] }}</updated_at>
            <created_at>{{ $routine['created_at'] }}</created_at> --}}

            <exercises>
                @foreach ($routine['exercises'] as $exercise)
                    {{-- <index>{{ $exercise['index'] }}</index> --}}
                    <title>{{ $exercise['title'] }}</title>
                    {{-- <notes>{{ $exercise['notes'] }}</notes> --}}
                    {{-- <exercise_template_id>{{ $exercise['exercise_template_id'] }}</exercise_template_id> --}}
                    {{-- <superset_id>{{ $exercise['superset_id'] }}</superset_id> --}}
                    <sets>
                        @foreach ($exercise['sets'] as $set)
                            <index>{{ $set['index'] }}</index>
                            <type>{{ $set['type'] }}</type>
                            <weight_kg>{{ $set['weight_kg'] }} kg</weight_kg>
                            <reps>{{ $set['reps'] }}</reps>
                            {{-- <distance_meters>{{ $set['distance_meters'] }} meters</distance_meters>
                            <duration_seconds>{{ $set['duration_seconds'] }} seconds</duration_seconds>
                            <custom_metric>{{ $set['custom_metric'] }}</custom_metric> --}}
                        @endforeach
                    </sets>
                @endforeach
            </exercises>
        </routine>
    @endforeach
</routines>