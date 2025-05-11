import csv
from io import StringIO
from collections import defaultdict
from datetime import datetime

def split_strong_csv_docs(docs):
    """
    Split Strong documents into smaller chunks based on the workout date.

    Args:
        docs (List[Document]): A list of documents to split.

    Returns:
        List[Chunk]: A list of split documents.
    """

    chunks = []

    for doc in docs:
        f = StringIO(doc.page_content)
        reader = csv.DictReader(f)

        grouped = defaultdict(list)
        for row in reader:
            key = (row['Date'], row['Workout Name'])
            grouped[key].append(row)
        
        for (date_raw, workout_name), rows in grouped.items():
            dt = datetime.strptime(date_raw, "%Y-%m-%d %H:%M:%S")
            readable_date = dt.strftime("%B %d, %Y at %I:%M %p")
            duration = rows[0]['Duration']

            exercise_texts = []

            exercises = defaultdict(list)
            for row in rows:
                ex = row['Exercise Name']
                weight = float(row['Weight'])
                reps = int(row['Reps'])
                exercises[ex].append(f"{weight:g} lbs x {reps} reps")

            for ex_name, sets in exercises.items():
                joined_sets = "; ".join(sets)
                exercise_texts.append(f"{ex_name} for {len(sets)} sets at {joined_sets}")

            text = f"On {readable_date}, a workout titled '{workout_name}' lasting {duration} was completed. The session included: " + "; ".join(exercise_texts) + "."

            chunks.append({
                "page_content": text,
                "metadata": {
                    "source": doc.metadata['source'],
                    "title": doc.metadata.get('title', ''),
                    "when": readable_date
                }
            })

    return chunks