<workouts>
    @foreach ($results as $workout)
        <workout>
            <id>{{ $workout['id'] }}</id>
            <title>{{ $workout['title'] }}</title>
            <start_time>{{ $workout['start_time'] }}</start_time>
            <end_time>{{ $workout['end_time'] }}</end_time>
            <updated_at>{{ $workout['updated_at'] }}</updated_at>
            <created_at>{{ $workout['created_at'] }}</created_at>

            <exercises>
                @foreach ($workout['exercises'] as $exercise)
                    <index>{{ $exercise['index'] }}</index>
                    <title>{{ $exercise['title'] }}</title>
                    <notes>{{ $exercise['notes'] }}</notes>
                    <exercise_template_id>{{ $exercise['exercise_template_id'] }}</exercise_template_id>
                    <superset_id>{{ $exercise['superset_id'] }}</superset_id>
                    <sets>
                        @foreach ($exercise['sets'] as $set)
                            <index>{{ $set['index'] }}</index>
                            <type>{{ $set['type'] }}</type>
                            <weight_kg>{{ $set['weight_kg'] }} kg</weight_kg>
                            <reps>{{ $set['reps'] }}</reps>
                            <distance_meters>{{ $set['distance_meters'] }} meters</distance_meters>
                            <duration_seconds>{{ $set['duration_seconds'] }} seconds</duration_seconds>
                            <rpe>{{ $set['rpe'] }}</rpe>
                            <custom_metric>{{ $set['custom_metric'] }}</custom_metric>
                        @endforeach
                    </sets>
                @endforeach
            </exercises>
        </workout>
    @endforeach
</workouts>