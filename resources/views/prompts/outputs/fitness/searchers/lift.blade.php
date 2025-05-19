<workouts>
    @foreach ($response as $workout)
        <workout>
            <title>{{ $workout['workout_name'] }}</title>
            <date>{{ $workout['timestamp']['date'] }}</date>
            <time>{{ $workout['timestamp']['time'] }}</time>
            <duration>{{ $workout['duration'] }}</duration>>
            <source>{{ $workout['source'] }}</source>

            <exercises>
                @foreach ($workout['exercises'] as $exercise)
                        <name>{{ $exercise['name'] }}</name>
                        <sets>
                            @foreach ($exercise['sets'] as $set)
                                <weight>{{ $set['weight'] }} lbs </weight>
                                <reps>{{ $set['reps'] }}</reps>
                            @endforeach
                        </sets>
                    </li>
                @endforeach
            </exercises>
        </workout>
    @endforeach
</workouts>