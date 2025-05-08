import csv
from io import StringIO
from collections import defaultdict

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
        
        for (date, workout_name), rows in grouped.items():
            exercises = defaultdict(list)

            for row in rows:
                ex = row['Exercise Name']
                set_order = row['Set Order']
                weight = row['Weight']
                reps = row['Reps']
                line = f"   - Set {set_order}: {weight} lb x {reps} reps"
                exercises[ex].append(line)
                
            text = f"Workout: {workout_name}\nDate: {date}\nDuration: {rows[0]['Duration']}\n\nExercises:\n"
            for ex_name, sets in exercises.items():
                text += f"{ex_name}\n" + "\n".join(sets) + "\n\n"

            chunks.append({
                "page_content": text.strip(),
                "metadata": {
                    "source": doc.metadata['source'],
                    "title": doc.metadata.get('title', ''),
                }
            })
    
    return chunks